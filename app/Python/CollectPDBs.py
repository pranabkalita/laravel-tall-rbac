import time
from Class.PDBService import PDBService
from dotenv import load_dotenv
import os

load_dotenv()

if __name__ == "__main__":
    # Database configuration
    db_config = {
        'host': os.getenv('DB_HOST'),
        'user': os.getenv('DB_USERNAME'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_DATABASE'),
        'port': os.getenv('DB_PORT', 3306)
    }

    pdb_service = PDBService(db_config)
    proteins = pdb_service.fetch_proteins()

    processed_count = 0

    for protein_id, protein_name in proteins:
        print('*****************************************************')
        print(f"Fetching PDB IDs for protein: {protein_name}")

        pdb_ids = pdb_service.fetch_pdb_ids(protein_name)

        if len(pdb_ids) > 0:
            pdb_service.save_pdb_ids(protein_id, pdb_ids)

        processed_count += 1  # Increment the counter

        # If 5 proteins have been processed, sleep for 1 minute
        if processed_count == 5:
            print("Processed 5 proteins. Sleeping for 1 minute...")
            time.sleep(60)  # Sleep for 60 seconds (1 minute)
            processed_count = 0  # Reset the counter after sleeping
