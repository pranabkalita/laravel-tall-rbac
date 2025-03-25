import re
import html
import subprocess
import xml.etree.ElementTree as ET

class EdirectEntrez:
    def __init__(self, email):
        self.email = email
        self.install_command = 'curl -fsSL https://ftp.ncbi.nlm.nih.gov/entrez/entrezdirect/install-edirect.sh | sh'
        self.update_path_command = 'export PATH=${HOME}/edirect:${PATH}'
        self.search_term = None

    def install_edirect(self):
        """Installs EDirect using WSL."""
        install_command = 'curl -fsSL https://ftp.ncbi.nlm.nih.gov/entrez/entrezdirect/install-edirect.sh | sh'
        install_result = subprocess.run(['wsl', '-e', 'bash', '-c', install_command], capture_output=True, text=True)
        if install_result.returncode != 0:
            raise Exception(f"Error installing EDirect: {install_result.stderr}")
        print("EDirect installed successfully.")

    def update_path(self):
        """Update PATH to include EDirect tools."""
        subprocess.run(['wsl', '-e', 'bash', '-c', self.update_path_command], capture_output=True, text=True)

    def search_pubmed(self, protein_name, search_term):
        """Search PubMed for the provided search term and fetch UIDs."""
        self.search_term = search_term
        # Search in title only by using [TITLE] field
        command = f'esearch -db pubmed -query "{search_term}[WORD] AND {search_term}[TITLE]" | efetch -format docsum > ./pmids/xml/{protein_name}.xml'

        # Run the command
        result = subprocess.run(['wsl', '-e', 'bash', '-c', f'{self.update_path_command} && {command}'], capture_output=True, text=True)
        if result.returncode != 0:
            raise Exception(f"Error searching PubMed: {result.stderr}")

        print(f"      - Search for {search_term} completed successfully. Data saved to {protein_name}.xml.")

        # Now filter the articles by titles containing capital letters and save PMIDs
        self.filter_articles_by_capital_letters(f'./pmids/xml/{protein_name}.xml', protein_name)

    def wrap_in_root(self, xml):
        # Use a regex to find all <DocumentSummarySet> tags, ignoring spaces or newlines between them
        document_summary_sets = re.findall(r'<DocumentSummarySet.*?>.*?</DocumentSummarySet>', xml, re.DOTALL)

        # If we found more than one <DocumentSummarySet> tag, we need to wrap them in <Root>
        if len(document_summary_sets) > 1:
            # Extract all the <DocumentSummarySet> content
            document_summary_set_content = ''.join(document_summary_sets)

            # Now wrap everything in a single <Root> element
            final_xml = f'<?xml version="1.0" encoding="UTF-8" ?>\n<!DOCTYPE DocumentSummarySet>\n<Root>{document_summary_set_content}</Root>'

            return final_xml
        else:
            return xml

    def filter_articles_by_capital_letters(self, xml_file, protein_name):
        """Filter articles by titles containing capital letters and save their PMIDs."""
        # Read XML data from the file
        with open(xml_file, 'r', encoding='ISO-8859-1') as f:
            xml_data = f.read()

        # Strip out any unwanted content
        xml_data = xml_data.strip()
        xml_data = self.wrap_in_root(xml_data)

        # Write the modified XML data to a new file
        with open(f'./pmids/modified_xml/{protein_name}_modified.xml', 'w', encoding='utf-8') as new_file:
            new_file.write(xml_data)
        print(f"Modified XML data saved to {protein_name}.xml.")

        # Now parse the modified XML data
        # xml_data = html.unescape(xml_data) # DO NOT REMOVE, USEFUL
        xml_data = re.sub(r'&(?![a-zA-Z0-9#]+;)', '&amp;', xml_data)

        try:
            tree = ET.ElementTree(ET.fromstring(xml_data))
            print("XML parsed successfully.")
        except ET.ParseError as e:
            print(f"Error parsing XML: {e}")

        root = tree.getroot()

        articles_with_capital_letters = []
        pmids_with_capital_letters = []

        # Define the regex pattern for protein name in capital letters
        pattern = r'(\[|\{|\()?' + re.escape(protein_name.upper()) + r'(\]|\}|\))?'

        # Iterate through DocumentSummary elements
        for docsum in root.findall(".//DocumentSummary"):
            pmid = docsum.find(".//Id")
            title = docsum.find(".//Title")

            if title is not None and pmid is not None:
                full_title = ''.join(title.itertext())
                clean_title = re.sub(r'<[^>]+>', '', full_title)
                if len(protein_name) <= 3:
                    if re.search(pattern, clean_title):
                        articles_with_capital_letters.append(clean_title)
                        pmids_with_capital_letters.append(pmid.text)
                else:
                    if re.search(pattern, clean_title, re.IGNORECASE):
                        articles_with_capital_letters.append(clean_title)
                        pmids_with_capital_letters.append(pmid.text)

        # Save PMIDs to file with .txt extension
        if pmids_with_capital_letters:
            with open(f'./pmids/txt/{protein_name}.txt', 'w') as file:
                for pmid in pmids_with_capital_letters:
                    file.write(f"{pmid}\n")
            print(f"Found {len(pmids_with_capital_letters)} PMIDs with capital letters in the title. Saved to {protein_name}.txt.")
            print("------------------------------------------------------")
        else:
            print("No articles with capital letters in the title were found.")

    def filter_articles_by_capital_letters_working(self, xml_file, protein_name):
        """Filter articles by titles containing capital letters and save their PMIDs."""
        try:
            tree = ET.parse(xml_file)
            root = tree.getroot()

            articles_with_capital_letters = []
            pmids_with_capital_letters = []

            pattern = r'(\[|\{|\()?' + re.escape(protein_name.upper()) + r'(\]|\}|\))?'

            # Iterate through DocumentSummary elements
            for docsum in root.findall(".//DocumentSummary"):
                # Get PMID
                pmid = docsum.find(".//Id")
                # Get Title (optional if you want to check for capital letters)
                title = docsum.find(".//Title")  # Ensure this matches the XML structure
                if title is not None and pmid is not None:
                    if len(protein_name) <= 3:
                        if re.search(pattern, title.text):
                            articles_with_capital_letters.append(title.text)
                            pmids_with_capital_letters.append(pmid.text)
                    else:
                        if re.search(pattern, title.text, re.IGNORECASE):
                            articles_with_capital_letters.append(title.text)
                            pmids_with_capital_letters.append(pmid.text)

            # Save PMIDs to file with .txt extension
            if pmids_with_capital_letters:
                with open(f'./pmids/txt/{protein_name}.txt', 'w') as file:
                    for pmid in pmids_with_capital_letters:
                        file.write(f"{pmid}\n")
                print(f"Found {len(pmids_with_capital_letters)} PMIDs with capital letters in the title. Saved to {protein_name}.txt.")
            else:
                print("No articles with capital letters in the title were found.")

        except Exception as e:
            print(f"Error processing XML file: {e}")


# edirect = EdirectEntrez('example@mail.com')
# edirect.search_pubmed('EGFR', 'EGFR')
# edirect.filter_articles_by_capital_letters(f'./pmids/EGFR.xml', 'MET')
