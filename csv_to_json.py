import csv
import json

def convert_csv_to_json(input_file, output_file):
    # Open the CSV file for reading
    with open(input_file, 'r') as csv_file:
        # Read the CSV file using DictReader to treat the first row as header
        csv_reader = csv.DictReader(csv_file)
        
        # Convert each row to a dictionary and store in a list
        data = [row for row in csv_reader]

    # Write the data to a JSON file
    with open(output_file, 'w') as json_file:
        json.dump(data, json_file, indent=4)

# Example usage:
if __name__ == "__main__":
    input_csv_file = r'C:\xampp\htdocs\b12db\B12_Variant_DB2.csv'  # Replace with your CSV file path
    output_json_file = r'C:\xampp\htdocs\b12db\B12_Variant_DB7.json'  # Replace with desired output JSON file path

    convert_csv_to_json(input_csv_file, output_json_file)

