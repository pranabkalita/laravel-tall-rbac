import requests
import time
import mysql.connector
from typing import Optional, Dict

class PDBService:
    def __init__(self, db_config):
        self.client = requests.Session()
        self.db_config = db_config
        self.url = "https://search.rcsb.org/rcsbsearch/v2/query"

        # Step 1: Establish database connection
        self.db = mysql.connector.connect(**self.db_config)
        self.cursor = self.db.cursor()

        # Payload template
        self.payload = {
            "query": {
                "type": "terminal",
                "service": "full_text",
                "parameters": {
                    "value": ''
                }
            },
            "return_type": "entry",
            "request_options": {
                "paginate": {
                    "start": 0,
                    "rows": 1000
                },
                "sort": [
                    {
                        "sort_by": "score",
                        "direction": "desc"
                    }
                ]
            }
        }

    def fetch_proteins(self):
        """Fetch proteins from the database."""
        self.cursor.execute("SELECT id, name FROM proteins WHERE name > 'HLA-C' ORDER BY name ASC")
        return self.cursor.fetchall()

    def make_request(self, payload: Dict) -> requests.Response:
        headers = {'Content-Type': 'application/json'}
        response = self.client.post(self.url, json=payload, headers=headers)
        return response

    def get_pdb_count(self) -> int:
        payload = self.payload.copy()
        payload['query']['parameters']['value'] = self.protein

        try:
            # Send POST request using requests
            response = self.make_request(payload)

            if response.status_code == 200:
                data = response.json()
                return data.get('total_count', 0)
            else:
                return 0
        except requests.exceptions.RequestException as e:
            # Handle exceptions
            print(f"----> Error: {e}")
            return 0

    def fetch_pdb_entry_details(self, pdb_id: str) -> Optional[Dict]:
        url = f"https://data.rcsb.org/rest/v1/core/entry/{pdb_id}"

        try:
            # Send GET request to fetch details for the PDB entry
            response = self.client.get(url)

            if response.status_code == 200:
                return response.json()
        except requests.exceptions.RequestException as e:
            print(f"----> Error fetching details for PDB ID {pdb_id}: {e}")

        return None

    def fetch_pdb_ids(self, protein) -> Optional[Dict]:
        self.protein = protein
        count = self.get_pdb_count()
        pdbids = set()
        print(f"----> Total PDB entries found: {count}")

        if count == 0:
            print("No results found.")
            return {}

        payload = self.payload.copy()
        payload['query']['parameters']['value'] = self.protein
        payload['request_options']['paginate']['rows'] = count

        try:
            # Send POST request to fetch data
            response = self.make_request(payload)

            if response.status_code == 200:
                data = response.json()

                # Extract PDB entries and add additional details to each entry
                if 'result_set' in data:
                    for entry in data['result_set']:
                        pdb_id = entry['identifier']
                        pdbids.add(pdb_id)

                return pdbids
            else:
                print("Error: Unable to retrieve data.")
                return {}
        except requests.exceptions.RequestException as e:
            # Handle exceptions
            print(f"----> Error: {e}")
            return {}

    def get_pdb_details(self) -> Dict:
        count = self.get_pdb_count()
        pdbids = []
        print(f"----> Total PDB entries found: {count}")

        if count == 0:
            print("No results found.")
            return {}

        payload = self.payload.copy()
        payload['query']['parameters']['value'] = self.protein
        payload['request_options']['paginate']['rows'] = count

        try:
            # Send POST request to fetch data
            response = self.make_request(payload)

            if response.status_code == 200:
                data = response.json()

                # Extract PDB entries and add additional details to each entry
                if 'result_set' in data:
                    for entry in data['result_set']:
                        pdb_id = entry['identifier']
                        print(f"----> Fetching details for PDB ID: {pdb_id}")
                        details = self.fetch_pdb_entry_details(pdb_id)
                        if details:
                            # Add detailed fields to the original entry
                            entry['details'] = {
                                'title': details.get('struct', {}).get('title', 'N/A'),
                                'release_date': details.get('rcsb_accession_info', {}).get('initial_release_date', 'N/A'),
                                'method': details.get('exptl', [{}])[0].get('method', 'N/A')
                            }
                else:
                    data['result_set'] = []

                print(pdbids)

                return data
            else:
                print("Error: Unable to retrieve data.")
                return {}
        except requests.exceptions.RequestException as e:
            # Handle exceptions
            print(f"----> Error: {e}")
            return {}

    def save_pdb_ids(self, protein_id: int, pdb_ids: list) -> bool:
        """Save PDB IDs for a given protein to the database."""
        try:
            # Prepare the insert query
            insert_query = """
            INSERT INTO pdbs (protein_id, pdb_id, created_at, updated_at)
            VALUES (%s, %s, %s, %s)
            """
            # Get the current timestamp for both created_at and updated_at
            current_time = time.strftime('%Y-%m-%d %H:%M:%S')

            # Loop through PDB IDs and insert them into the database
            for pdb_id in pdb_ids:
                # Execute the query for each PDB ID
                self.cursor.execute(insert_query, (protein_id, pdb_id, current_time, current_time))

            # Commit the transaction
            self.db.commit()

            print(f"----> Successfully saved {len(pdb_ids)} PDB IDs for protein ID {protein_id}.")
            return True
        except mysql.connector.Error as e:
            # Rollback in case of error
            self.db.rollback()
            print(f"----> Error saving PDB IDs: {e}")
            return False
