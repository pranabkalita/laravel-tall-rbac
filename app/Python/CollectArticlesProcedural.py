import subprocess

# Step 0: Install EDirect using WSL
install_command = 'curl -fsSL https://ftp.ncbi.nlm.nih.gov/entrez/entrezdirect/install-edirect.sh | sh'
install_result = subprocess.run(['wsl', '-e', 'bash', '-c', install_command], capture_output=True, text=True)

# Update PATH to include EDirect tools
update_path_command = 'export PATH=${HOME}/edirect:${PATH}'
subprocess.run(['wsl', '-e', 'bash', '-c', update_path_command], capture_output=True, text=True)

# Set your email address
email = "your-email@example.com"

# Step 1: Search PubMed for the query "BRCA1" and fetch all UIDs using WSL
search_term = "BRCA1"
# command = f'esearch -db pubmed -query "{search_term}[tiab]" | efetch -format uid > ./pmids/{protein_name}.txt'

command = f"esearch -db pubmed -query {search_term} | efetch -format uid > _ids.txt"
result = subprocess.run(['wsl', '-e', 'bash', '-c', f'{update_path_command} && {command}'], capture_output=True, text=True)
