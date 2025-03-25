import time
import logging
from Bio import Entrez
from typing import List


class BProteinChron:
    def __init__(self, email: str):
        self.email = email
        Entrez.email = self.email  # NCBI requires email to use their API

    def search_protein(self, protein: str) -> int:
        """Search PubMed for a protein and return the count of matching articles."""
        try:
            handle = Entrez.esearch(db="pubmed", term=protein, retmax=0)  # retmax=0 only returns the count
            record = Entrez.read(handle)
            handle.close()
            return int(record["Count"])
        except Exception as e:
            logging.error(f"Error during protein search: {e}")
            return 0

    def fetch_pmids(self, protein: str) -> List[str]:
        """Fetch PMIDs for articles related to a given protein."""
        try:
            handle = Entrez.esearch(db="pubmed", term=protein, retmax=100)  # You can adjust retmax as needed
            record = Entrez.read(handle)
            handle.close()
            return record["IdList"]
        except Exception as e:
            logging.error(f"Error fetching PMIDs: {e}")
            return []

    def fetch_pmid(self, pmid: str):
        """Fetch detailed information for a specific PubMed ID (PMID)."""
        try:
            handle = Entrez.efetch(db="pubmed", id=pmid, rettype="xml", retmode="xml")
            record = Entrez.read(handle)
            handle.close()
            return record
        except Exception as e:
            logging.error(f"Error fetching data for PMID {pmid}: {e}")
            return None

    def fetch_batch_pmid_with_abstract(self, pmids: List[str]):
        """Fetch detailed records for a batch of PMIDs with abstracts."""
        try:
            handle = Entrez.efetch(db="pubmed", id=','.join(pmids), rettype="xml", retmode="xml")
            records = Entrez.read(handle)
            handle.close()
            return records
        except Exception as e:
            logging.error(f"Error fetching batch PMIDs: {e}")
            return None

    def fetch_abstracts_for_pmids(self, pmids: List[str]) -> List[str]:
        """Fetch abstracts for a list of PMIDs."""
        abstracts = []
        for pmid in pmids:
            record = self.fetch_pmid(pmid)
            if record:
                # Extract the abstract from the PubMed record (if it exists)
                try:
                    abstract = record[0]["PubmedData"]["ArticleIdList"]["ArticleId"][0]
                    abstracts.append(abstract)
                except KeyError:
                    abstracts.append("No abstract available")
            time.sleep(1)  # Respect NCBI's usage limits
        return abstracts
