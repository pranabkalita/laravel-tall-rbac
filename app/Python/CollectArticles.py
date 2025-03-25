from Class.EdirectEntrez import EdirectEntrez

from sqlalchemy import create_engine, text

email = "your-email@example.com"

# Database configuration
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'biostation_tall'
}

# SQLAlchemy connection string
connection_string = f"mysql+mysqlconnector://{db_config['user']}:{db_config['password']}@{db_config['host']}/{db_config['database']}"
engine = create_engine(connection_string)

# Query to get proteins which do not have articles
proteins_without_articles_query = """
SELECT proteins.*
FROM proteins
LEFT JOIN articles ON proteins.id = articles.protein_id
WHERE articles.protein_id IS NULL
ORDER BY proteins.name ASC;
"""

with engine.connect() as connection:
    print("Executing SQL query...")
    result = connection.execute(text(proteins_without_articles_query))

    # Fetch the proteins without articles
    proteins = result.fetchall()
    print("Completed SQL query...")

    if not proteins:
        print("No proteins without articles found.")
    else:
        # Create EdirectEntrez instance and process the protein
        edirect = EdirectEntrez(email)

        # Install EDirect and update path
        print("Installing Edirect ...")
        edirect.install_edirect()
        print("Complete Installing Edirect ...")

        print("Updating Path ...")
        edirect.update_path()
        print("Complete Updating Path ...")

        # Directly access the columns by name (assuming 'name' exists as a column)
        for protein in proteins:
            protein_name = protein[1]
            search_term = F"{protein_name}[WORD] AND {protein_name}[TITL]"
            print("------------------------------------------------------")
            print(f"Processing protein: {protein_name}")

            search_term_query = text("""
                SELECT name
                FROM proteins
                WHERE name LIKE :protein
                AND name != :exact_protein
            """)

            result = connection.execute(search_term_query, {
                'protein': f'%{protein_name}%',
                'exact_protein': protein_name
            })

            similar_protein_names = [row[0] for row in result]

            if similar_protein_names:
                # query_str = f"NOT {' NOT '.join(similar_protein_names)}"
                exclusion_str = " OR ".join([f"{name}[WORD] OR {name}[TITL]" for name in similar_protein_names])
                query_str = f"({protein_name}[WORD] AND {protein_name}[TITL]) NOT ({exclusion_str})"
                search_term = query_str

            print("    - Search Term: " + search_term)

            # Search PubMed using the protein's name
            edirect.search_pubmed(protein_name, search_term)
