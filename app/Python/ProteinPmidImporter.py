from Class.ProteinArticleInserter import ProteinArticleInserter
from dotenv import load_dotenv
import os

load_dotenv()

# Example usage
if __name__ == "__main__":
    # Database configuration
    db_config = {
        'host': os.getenv('DB_HOST'),
        'user': os.getenv('DB_USERNAME'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_DATABASE'),
        'port': os.getenv('DB_PORT', 3306)
    }

    # Create an instance of the ProteinArticleInserter class
    inserter = ProteinArticleInserter(db_config, 'pmids/txt')

    # Fetch the proteins from the database
    proteins = inserter.fetch_proteins()

    # Process the files and insert PMIDs
    inserter.process_files_and_insert(proteins)

    # Close the connection after processing
    inserter.close()
