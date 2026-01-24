#!/usr/bin/env python3
"""
Convert all table names in SQL file from lowercase to UPPERCASE
Handles: CREATE TABLE, INSERT INTO, SELECT FROM, JOIN, ALTER TABLE, etc.
"""

import re
import sys

def convert_sql_table_names(input_file, output_file):
    """
    Read SQL file and convert all table names to UPPERCASE
    """
    print(f"Reading: {input_file}")
    print(f"Output: {output_file}")
    
    with open(input_file, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    # Pattern to match backtick-quoted table names
    # This regex captures `table_name` and converts to `TABLE_NAME`
    def uppercase_table(match):
        return "`" + match.group(1).upper() + "`"
    
    # Find all backtick-quoted identifiers and convert lowercase ones to uppercase
    # This will handle: `table_name`, `column_name`, etc.
    lines = content.split('\n')
    converted_lines = []
    
    for i, line in enumerate(lines, 1):
        if i % 100000 == 0:
            print(f"  Processing line {i}...", file=sys.stderr)
        
        # Convert backtick-quoted identifiers to uppercase
        # Only convert if they're table-related (after CREATE TABLE, INSERT INTO, FROM, JOIN, etc.)
        
        # CREATE TABLE `table_name` -> CREATE TABLE `TABLE_NAME`
        line = re.sub(r'(CREATE TABLE|ALTER TABLE|DROP TABLE|TRUNCATE TABLE)\s+`([^`]+)`', 
                      lambda m: m.group(1) + ' `' + m.group(2).upper() + '`', line, flags=re.IGNORECASE)
        
        # INSERT INTO `table_name` -> INSERT INTO `TABLE_NAME`
        line = re.sub(r'(INSERT INTO)\s+`([^`]+)`', 
                      lambda m: m.group(1) + ' `' + m.group(2).upper() + '`', line, flags=re.IGNORECASE)
        
        # FROM `table_name` -> FROM `TABLE_NAME`
        line = re.sub(r'\s(FROM|INTO|JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN)\s+`([^`]+)`', 
                      lambda m: ' ' + m.group(1) + ' `' + m.group(2).upper() + '`', line, flags=re.IGNORECASE)
        
        # UPDATE `table_name` -> UPDATE `TABLE_NAME`
        line = re.sub(r'(UPDATE)\s+`([^`]+)`', 
                      lambda m: m.group(1) + ' `' + m.group(2).upper() + '`', line, flags=re.IGNORECASE)
        
        converted_lines.append(line)
    
    content = '\n'.join(converted_lines)
    
    print(f"Writing: {output_file}")
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("✓ Conversion complete!")

if __name__ == '__main__':
    input_file = '/home/me/UbuntuDevFiles/HospitaloftheHolyCross/Hospital-of-the-Holycross---eKonsultaApp/database_queries/main_db/jerlanlo_pbe_hckonsulta.sql'
    output_file = '/home/me/UbuntuDevFiles/HospitaloftheHolyCross/Hospital-of-the-Holycross---eKonsultaApp/database_queries/main_db/jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql'
    
    convert_sql_table_names(input_file, output_file)
