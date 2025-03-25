from Class.ProteinArticleInserter import ProteinArticleInserter

# Example usage
if __name__ == "__main__":
    # Database configuration
    db_config = {
        'host': '127.0.0.1',
        'user': 'root',
        'password': '',
        'database': 'biostation_tall'
    }

    # Create an instance of the ProteinArticleInserter class
    inserter = ProteinArticleInserter(db_config, 'pmids/txt')

    # Fetch the proteins from the database
    proteins = inserter.fetch_proteins()

    # Process the files and insert PMIDs
    inserter.process_files_and_insert(proteins)

    # Close the connection after processing
    inserter.close()
