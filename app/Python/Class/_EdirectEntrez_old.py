import subprocess

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
        command = f'esearch -db pubmed -query "{search_term}[WORD][TIAB]" | efetch -format uid > ./pmids/{protein_name}.txt'
        # command = f'esearch -db pubmed -query "{search_term}" | efetch -format uid > ./pmids/{protein_name}.txt'
        # command = f'esearch -db pubmed -query "\\b{search_term}\\b[TIAB]" | efetch -format uid > ./pmids/{protein_name}.txt'
        result = subprocess.run(['wsl', '-e', 'bash', '-c', f'{self.update_path_command} && {command}'], capture_output=True, text=True)
        if result.returncode != 0:
            raise Exception(f"Error searching PubMed: {result.stderr}")
        print(f"Search for {search_term} completed successfully. UIDs saved to {protein_name}.txt.")
        print("------------------------------------------------------")
