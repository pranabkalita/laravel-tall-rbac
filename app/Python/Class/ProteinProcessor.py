import logging
from datetime import datetime
from sqlalchemy.orm import Session
from sqlalchemy.exc import SQLAlchemyError
import re
from sqlalchemy import create_engine, text
from datetime import datetime
from dotenv import load_dotenv
import os

load_dotenv()


class ProteinProcessor:
    def __init__(self, protein_id: int, protein: object):
        self.protein_id = protein_id
        self.protein = protein
        self.session = Session()

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
        self.engine = create_engine(connection_string)

    def extract_abstract(self, element):
        """Extract the abstract from the given element."""
        abstract = ""
        if 'MedlineCitation' in element:
            article = element['MedlineCitation'].get('Article', {})
            abstract_text = article.get('Abstract', {}).get('AbstractText', "")

            # If the abstract is a list, join the list items into a single string
            if isinstance(abstract_text, list):
                abstract = ' '.join(abstract_text)
            else:
                abstract = abstract_text

        return abstract

    def extract_dates(self, element: dict) -> dict:
        """Extract and format the published and last revision dates."""
        published_date = ''
        last_revision_date = ''

        # Check if PubMedPubDate is present in 'PubmedData' -> 'History' (list)
        if 'PubmedData' in element and 'History' in element['PubmedData']:
            pubmed_pubdate = element['PubmedData']['History']
            if pubmed_pubdate:
                date = self.find_date_from_pub_status(pubmed_pubdate)
                published_date = self.format_date(date)

        # If 'MedlineCitation' contains DateCompleted, format it
        elif 'MedlineCitation' in element and 'DateCompleted' in element['MedlineCitation']:
            published_date = self.format_date(element['MedlineCitation']['DateCompleted'])

        # If 'MedlineCitation' contains PubDate, format it
        elif 'MedlineCitation' in element and 'Article' in element['MedlineCitation'] and 'Journal' in \
            element['MedlineCitation']['Article']:
            journal_issue = element['MedlineCitation']['Article']['Journal'].get('JournalIssue', {})
            if 'PubDate' in journal_issue:
                published_date = self.format_date(journal_issue['PubDate'])

        # If 'MedlineCitation' contains DateRevised, format it
        if 'MedlineCitation' in element and 'DateRevised' in element['MedlineCitation']:
            last_revision_date = self.format_date(element['MedlineCitation']['DateRevised'])

        return {'published_date': published_date, 'last_revision_date': last_revision_date}

    def find_date_from_pub_status(self, input_array: list) -> dict:
        """Find the date entry with PubStatus = 'pubmed'."""
        for entry in input_array:
            # Check if '@attributes' exists and contains the correct PubStatus
            if '@attributes' in entry and entry['@attributes'].get('PubStatus') == 'pubmed':
                return entry

            # Check if entry has 'attributes' and if PubStatus is 'pubmed'
            if hasattr(entry, 'attributes') and entry.attributes.get('PubStatus') == 'pubmed':
                return entry

        # Return the first date if no matching PubStatus
        return input_array[0] if input_array else {}


    def format_date(self, date_array: dict) -> str:
        """Format the date from a dictionary and return it as a string."""
        try:
            year = int(date_array.get('Year', 0))
            # Convert the month to an integer (it could be a string, e.g., 'January')
            month = int(date_array.get('Month', 1)) if date_array.get('Month', '').isdigit() else self.convert_month(
                date_array.get('Month', ''))
            day = int(date_array.get('Day', 1))


            # Use Python's datetime module to create the date
            return datetime(year, month, day).strftime('%Y-%m-%d')
        except Exception as e:
            logging.error(f"Error formatting date: {e}")
            return ''

    def convert_month(self, month_name: str) -> int:
        """Convert month name (e.g., 'January', 'Feb') to month number."""
        months = {
            'January': 1, 'Feb': 2, 'February': 2, 'Mar': 3, 'March': 3, 'Apr': 4, 'April': 4,
            'May': 5, 'Jun': 6, 'June': 6, 'Jul': 7, 'July': 7, 'Aug': 8, 'August': 8,
            'Sep': 9, 'September': 9, 'Oct': 10, 'October': 10, 'Nov': 11, 'November': 11,
            'Dec': 12, 'December': 12
        }

        return months.get(month_name, 1)  # Default to 1 (January) if not found

    def find_mutations(self, abstract: str) -> list:
        """Find mutations in the abstract based on the protein's name."""
        # Create the mutation pattern, ensuring to escape the protein name properly
        mutation_pattern = r'(?:(?:\b' + re.escape(
            self.protein.name) + r'\b(?:[^\w\s])?|(?<![A-Za-z\d-]))[A-Z]\d{2,5}[A-Z]\b(?![A-Za-z\d-]))'

        # Find all matches in the abstract
        matches = re.findall(mutation_pattern, abstract)

        # Return unique matches
        return list(set(matches))  # Return unique mutations

    def prepare_unique_mutations(self, mutations):
        """Prepare and return unique mutations."""
        return list(set(mutations))

    def process(self, xml_data):
        if not self.protein:
            logging.error(f"Protein with ID {self.protein_id} not found.")
            return

        try:
            for element in xml_data:
                # Extract the PMID and article title
                pmid = int(element['MedlineCitation']['PMID'])
                article_title = None

                if 'MedlineCitation' in element:
                    article = element['MedlineCitation']
                    if 'Article' in article:
                        article_title = article['Article'].get('ArticleTitle', None)
                    elif 'Book' in article:
                        article_title = article['Book'].get('BookTitle', None)
                    elif 'ArticleTitle' in article:
                        article_title = article.get('ArticleTitle', None)



                # Extract abstract and dates
                abstract = self.extract_abstract(element)
                dates = self.extract_dates(element)

                published_date = str(datetime.strptime(dates['published_date'], '%Y-%m-%d').date()) if 'published_date' in dates else ''
                last_revision_date = (
                    str(datetime.strptime(dates['last_revision_date'], '%Y-%m-%d').date())
                    if 'last_revision_date' in dates and dates['last_revision_date']
                    else ''
                )

                # Find mutations from the abstract
                mutations = self.find_mutations(abstract)

                with self.engine.connect() as connection:
                    with connection.begin():
                        # Update Article
                        article_update_query = """
                        UPDATE articles
                        SET title = :title, published_on = :published_on, last_revised_on = :last_revised_on, updated_at = NOW()
                        WHERE pmid = :pmid
                        AND protein_id = :protein_id
                        """

                        values = {
                            "title": str(article_title),
                            "published_on": published_date,
                            "pmid": str(pmid),
                            "protein_id": self.protein_id,
                            "last_revised_on": last_revision_date
                        }

                        connection.execute(
                            text(article_update_query),
                            {"title": str(article_title), "published_on": published_date, "pmid": str(pmid),
                            "protein_id": self.protein_id, "last_revised_on": last_revision_date}
                        )

                        # updated_article = article_update_result.mappings().fetchone()
                        # print(updated_article)

                        # Select Article
                        article_query = "SELECT * FROM articles WHERE pmid = :pmid AND protein_id = :protein_id"

                        article_result = connection.execute(
                            text(article_query),
                            {"pmid": pmid, "protein_id": self.protein_id}
                        )

                        article = article_result.mappings().fetchone()

                        mutations_to_insert = []
                        for mutation in mutations:
                            mutations_to_insert.append({
                                'article_id': article['id'],
                                'name': mutation,
                                'created_at': datetime.now(),
                                'updated_at': datetime.now()
                            })

                        if len(mutations_to_insert) > 0:
                            # Bulk insert the mutations
                            insert_mutation_query = """
                            INSERT INTO mutations (article_id, name, created_at, updated_at)
                            VALUES (:article_id, :name, :created_at, :updated_at)
                            """

                            connection.execute(
                                text(insert_mutation_query),
                                mutations_to_insert
                            )

                    # Update the article (if exists) or create a new one
                    # article = self.session.query(Article).filter_by(pmid=pmid, protein_id=self.protein_id).first()

                    # if article:
                    #     article.title = article_title
                    #     article.success = 1
                    #     article.published_on = dates['publishedDate']
                    #     article.updated_at = datetime.now()
                    #     self.session.commit()

                    #     # Prepare mutations for bulk insertion
                    #     for mutation in unique_mutations:
                    #         mutations_to_insert.append({
                    #             'article_id': article.id,
                    #             'name': mutation,
                    #             'created_at': datetime.now(),
                    #             'updated_at': datetime.now(),
                    #         })

                    # else:
                    #     logging.error(f"Article with PMID {pmid} not found for protein {self.protein_id}.")
                    #     continue

            # Perform bulk insert for mutations
            # if mutations_to_insert:
            #     self.session.bulk_insert_mappings(Mutation, mutations_to_insert)
            #     self.session.commit()

        except SQLAlchemyError as e:
            self.session.rollback()
            logging.error(f"Error processing mutations: {e}")
        except Exception as e:
            self.session.rollback()
            logging.error(f"Unexpected error: {e}")
