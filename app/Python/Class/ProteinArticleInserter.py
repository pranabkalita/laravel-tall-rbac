import mysql.connector
import os
from datetime import datetime

class ProteinArticleInserter:
    def __init__(self, db_config, pmids_folder='pmids'):
        # Database configuration (dictionary)
        self.db_config = db_config
        self.pmids_folder = pmids_folder

        # Step 1: Establish database connection
        self.db = mysql.connector.connect(**self.db_config)
        self.cursor = self.db.cursor()

    def fetch_proteins(self):
        """Fetch proteins from the database."""
        self.cursor.execute("SELECT id, name FROM proteins ORDER BY name ASC")
        return self.cursor.fetchall()

    def process_files_and_insert(self, proteins):
        """Process the files and insert PMIDs in batches."""
        batch_size = 100
        articles_to_insert = []

        for protein_id, protein_name in proteins:
            # Build the filename (e.g., "BRCA1.txt")
            filename = os.path.join(self.pmids_folder, f"{protein_name}.txt")

            # Check if the file exists
            if os.path.isfile(filename):
                print("Importing articles for protein : " + protein_name)
                # Read the contents of the file
                with open(filename, 'r') as file:
                    pmids = file.readlines()

                # Clean the PMIDs and prepare for insertion into the database
                pmids = [pmid.strip() for pmid in pmids]  # Strip any surrounding whitespace/newlines

                # Prepare the batch for insertion
                current_timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                for pmid in pmids:
                    articles_to_insert.append((protein_id, pmid, current_timestamp, current_timestamp))

                    # If we've reached the batch size, insert the batch and reset the list
                    if len(articles_to_insert) >= batch_size:
                        self.insert_articles_batch(articles_to_insert)
                        articles_to_insert = []

                # After the loop, insert any remaining articles that didn't reach the batch size
                if articles_to_insert:
                    self.insert_articles_batch(articles_to_insert)

                print("Complete Importing articles for protein : " + protein_name)
                print("-------------------------------------------")

            else:
                print(f"File {filename} not found. Skipping...")

    def insert_articles_batch(self, articles):
        """Insert a batch of articles into the database."""
        insert_query = """
        INSERT INTO articles (protein_id, pmid, created_at, updated_at)
        VALUES (%s, %s, %s, %s)
        """
        self.cursor.executemany(insert_query, articles)
        self.db.commit()
        print(f"Inserted {len(articles)} articles into the database.")

    def close(self):
        """Close the cursor and the database connection."""
        self.cursor.close()
        self.db.close()
        print("Database connection closed.")

