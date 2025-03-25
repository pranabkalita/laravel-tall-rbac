<?php

function removeDuplicatesFromCSV($inputFile, $outputFile)
{
    // Open the input CSV file for reading
    if (($handle = fopen($inputFile, 'r')) !== false) {
        $uniqueRows = [];

        // Read each row of the CSV
        while (($data = fgetcsv($handle)) !== false) {
            // Convert the row to a string to use as a unique key
            $rowString = implode(',', $data);
            // Add to uniqueRows array if it's not already present
            if (!in_array($rowString, $uniqueRows)) {
                $uniqueRows[] = $rowString;
            }
        }
        fclose($handle);

        // Open the output CSV file for writing
        if (($handle = fopen($outputFile, 'w')) !== false) {
            // Write the unique rows back to the output file
            foreach ($uniqueRows as $row) {
                fputcsv($handle, explode(',', $row));
            }
            fclose($handle);
            echo "Duplicates removed. Unique rows saved to '{$outputFile}'.\n";
        } else {
            echo "Error: Unable to open output file '{$outputFile}'.\n";
        }
    } else {
        echo "Error: Unable to open input file '{$inputFile}'.\n";
    }
}

// Example usage
$inputFile = './proteins.csv'; // Path to your input CSV file
$outputFile = './proteins_unique.csv'; // Path to save the output CSV file
removeDuplicatesFromCSV($inputFile, $outputFile);
