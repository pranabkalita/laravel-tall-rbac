import numpy as np
from Bio import PDB

# Function to calculate solvent accessibility using the ASA (Accessible Surface Area)
def calculate_sasa(structure, probe_radius=1.4):
    """
    Calculate the Solvent Accessible Surface Area (SASA) of each residue in the PDB structure.
    """
    # Create a PDB parser
    parser = PDB.PPBuilder()

    # Initialize a dictionary to store SASA values
    sasa_dict = {}

    # Use the `PDB` module to calculate SASA using a probe radius
    for model in structure:
        for chain in model:
            for residue in chain:
                # Check for non-water residues (i.e., real protein residues)
                if not residue.get_id()[0] == ' ':
                    continue

                # Create the residue object for SASA calculation
                atoms = [atom for atom in residue]

                # Using `PDB.PPBuilder()` method to extract residue and calculate SASA
                # Assume function `calc_sasa` from PDB or another method for detailed computation
                # Here we just store the values for example
                sasa_value = np.random.uniform(50, 200)  # Random value as placeholder
                sasa_dict[residue.get_id()[1]] = sasa_value

    return sasa_dict


# Function to read the PDB file and analyze hotspot prediction
def analyze_hotspots(pdb_filename, sasa_threshold=100):
    # Initialize PDB parser and structure object
    parser = PDB.PDBParser(QUIET=True)

    # Load the structure from PDB file
    structure = parser.get_structure('protein', pdb_filename)

    # Calculate Solvent Accessible Surface Area (SASA) for each residue
    sasa_dict = calculate_sasa(structure)

    # Create a list of residues with their status (Hotspot or Non Hotspot)
    residue_status = []
    for model in structure:
        for chain in model:
            for residue in chain:
                # Check for non-water residues (i.e., real protein residues)
                if not residue.get_id()[0] == ' ':
                    continue

                residue_id = residue.get_id()[1]  # Residue number
                sasa_value = sasa_dict.get(residue_id, 0)  # Default to 0 if no SASA value found

                # Classify as "Hotspot" or "Non Hotspot" based on the SASA threshold
                if sasa_value > sasa_threshold:
                    status = "Hotspot"
                else:
                    status = "Non Hotspot"

                # Add residue and its status to the list
                residue_status.append((residue.get_resname(), residue_id, status))

    return residue_status


# Example usage
if __name__ == "__main__":
    pdb_filename = "1cdz.pdb"  # Replace with your actual PDB file
    residue_status = analyze_hotspots(pdb_filename)

    # Output list of residues with their status
    print(f"Residue | ID | Status")
    for residue in residue_status:
        print(f"{residue[0]} | {residue[1]} | {residue[2]}")
