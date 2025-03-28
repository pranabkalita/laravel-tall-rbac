from sqlalchemy import create_engine, text
from bprotein_chron import BProteinChron
from xml_service import XmlService
from protein_processor import ProteinProcessor
from dotenv import load_dotenv
import os

load_dotenv()

# Database configuration
db_config = {
    'host': os.getenv('DB_HOST'),
    'user': os.getenv('DB_USERNAME'),
    'password': os.getenv('DB_PASSWORD'),
    'database': os.getenv('DB_DATABASE'),
    'port': os.getenv('DB_PORT', 3306)
}

# SQLAlchemy connection string
connection_string = f"mysql+mysqlconnector://{db_config['user']}:{db_config['password']}@{db_config['host']}/{db_config['database']}"
engine = create_engine(connection_string)

# Query to get mutation data for each protein
protein_query = """
SELECT proteins.*,
(
    SELECT COUNT(*)
    FROM articles
    WHERE articles.protein_id = proteins.id
    AND articles.title IS NULL
) AS articles_count
FROM proteins
WHERE EXISTS (
    SELECT 1
    FROM articles
    WHERE articles.protein_id = proteins.id
    AND articles.title IS NULL
)
ORDER BY proteins.name
LIMIT 1;
"""

# Loop to process proteins until no proteins are left
while True:
    with engine.connect() as connection:
        result = connection.execute(text(protein_query))
        protein = result.mappings().fetchone()

        if not protein:
            print("No proteins left to process.")
            break

        protein_id = protein['id']

        # Query to get the articles for the current protein
        articles_query = f"""
        SELECT * FROM articles
        WHERE articles.protein_id = {protein_id}
        AND articles.title IS NULL
        ORDER BY published_on
        LIMIT 100;
        """

        # Execute the query to get the articles
        with engine.connect() as connection:
            articles_result = connection.execute(text(articles_query))
            articles = articles_result.mappings().fetchall()

            pmids = []

            # Print the articles data
            for article in articles:
                pmids.append(article['pmid'])

            pmid_str = ','.join(pmids)

            # Fetch abstracts using BProteinChron
            protein_chron = BProteinChron(email="your_email@example.com")
            records = protein_chron.fetch_batch_pmid_with_abstract(pmids)

            # Prepare the data using XmlService
            xml_service = XmlService()
            articles = xml_service.prepare_for_processing(records)

            # Process the articles for the current protein
            processor = ProteinProcessor(protein_id=protein_id, protein=protein)
            processor.process(articles)

    print(f"Processed protein {protein['name']}")
