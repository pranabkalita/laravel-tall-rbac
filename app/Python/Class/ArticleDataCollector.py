from sqlalchemy import create_engine, text
from Class.BProtein import BProteinChron
from Class.XMLService import XmlService
from Class.ProteinProcessor import ProteinProcessor

class ArticleDataCollector:
    def __init__(self, db_config, email):
        """
        Initializes the CollectArticlesData class with the database configuration and email for BProteinChron.
        :param db_config: Dictionary containing the database configuration (host, user, password, database)
        :param email: Email to be used in the BProteinChron class
        """
        self.db_config = db_config
        self.email = email

        # SQLAlchemy connection string
        connection_string = f"mysql+mysqlconnector://{db_config['user']}:{db_config['password']}@{db_config['host']}/{db_config['database']}"
        self.engine = create_engine(connection_string)

    def fetch_protein_data(self):
        """
        Fetches a protein from the database that needs processing. It selects proteins where
        there are articles to be processed (articles.success = 0).
        :return: protein dictionary or None if no proteins are left to process
        """
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

        with self.engine.connect() as connection:
            result = connection.execute(text(protein_query))
            return result.mappings().fetchone()

    def fetch_articles(self, protein_id):
        """
        Fetches articles for a given protein that need processing (articles.success = 0).
        :param protein_id: The protein ID for which articles are to be fetched
        :return: List of articles
        """
        articles_query = f"""
        SELECT * FROM articles
        WHERE articles.protein_id = {protein_id}
        AND articles.title IS NULL
        ORDER BY published_on
        LIMIT 100;
        """

        with self.engine.connect() as connection:
            articles_result = connection.execute(text(articles_query))
            return articles_result.mappings().fetchall()

    def process_articles_for_protein(self, protein):
        """
        Process the articles for a given protein.
        :param protein: Protein dictionary containing protein data
        """
        protein_id = protein['id']

        # Fetch articles for this protein
        articles = self.fetch_articles(protein_id)

        if not articles:
            print(f"No articles to process for protein {protein['name']}.")
            return

        pmids = [article['pmid'] for article in articles]
        pmid_str = ','.join(pmids)

        # Fetch abstracts using BProteinChron
        protein_chron = BProteinChron(email=self.email)
        records = protein_chron.fetch_batch_pmid_with_abstract(pmids)

        # Prepare the data using XmlService
        xml_service = XmlService()
        processed_articles = xml_service.prepare_for_processing(records)

        # Process the articles for the current protein
        processor = ProteinProcessor(protein_id=protein_id, protein=protein)
        processor.process(processed_articles)

        print(f"Processed protein {protein['name']}")

    def run(self):
        """
        Main method to run the process. It will fetch proteins and process their associated articles
        until no proteins are left to process.
        """
        while True:
            protein = self.fetch_protein_data()

            if not protein:
                print("No proteins left to process.")
                break

            self.process_articles_for_protein(protein)

