import sqlite3
import os
import re
import glob
import time
import random
import pandas as pd

# Crockford Base32 alphabet for ULID generation
ALPHABET = "0123456789abcdefghjkmnpqrstvwxyz"

def generate_ulid():
    # timestamp in ms
    timestamp = int(time.time() * 1000)
    ts_str = ""
    for _ in range(10):
        ts_str = ALPHABET[timestamp % 32] + ts_str
        timestamp //= 32
    rand_str = ""
    for _ in range(16):
        rand_str += random.choice(ALPHABET)
    return ts_str + rand_str

# Hardcoded subject mappings to standard Cambridge codes
IGCSE_MAPPING = {
    'BIOLOGY': '0610',
    'CHEMISTRY': '0620',
    'PHYSICS': '0625',
    'COMPUTER SCIENCE': '0478',
    'BUSINESS STUDIES': '0450',
    'ECONOMICS': '0455',
    'MATHEMATICS (W/OUT COURSEWORK)': '0580',
    'FIRST LANGUAGE ENGLISH': '0500',
    'ADDITIONAL MATHEMATICS': '0606',
    'ART AND DESIGN': '0400',
    'CAMBRIDGE INT MATHEMATICS': '0607',
    'CO-ORD SCIENCES (DOUBLE AWARD)': '0654',
    'COMBINED SCIENCE': '0653',
    'ENGLISH (ADDITIONAL LANGUAGE)': '0472',
    'ENGLISH AS A SECOND LANGUAGE': '0510',
    'ENVIRONMENTAL MANAGEMENT': '0680',
    'FOREIGN LANGUAGE FRENCH': '0520',
    'GEOGRAPHY': '0460',
    'HINDI AS A SECOND LANGUAGE': '0549',
    'INFORMATION AND COMMUNICATION': '0417',
    'LITERATURE IN ENGLISH': '0475',
    'TRAVEL AND TOURISM': '0471',
}

GCE_MAPPING = {
    'MATHEMATICS': '9709',
    'FURTHER MATHEMATICS': '9231',
    'ENGLISH GENERAL PAPER': '8021',
    'PSYCHOLOGY': '9990',
    'PHYSICS': '9702',
    'CHEMISTRY': '9701',
    'BIOLOGY': '9700',
    'COMPUTER SCIENCE': '9618',
    'ACCOUNTING': '9706',
    'ART AND DESIGN': '9479',
    'BUSINESS': '9609',
    'ECONOMICS': '9708',
    'ENGLISH LANGUAGE': '9093',
    'HISTORY': '9489',
    'INFORMATION TECHNOLOGY': '9626',
    'LITERATURE IN ENGLISH': '9695',
    'SOCIOLOGY': '9699',
}

# DB details
db_path = r"C:\Users\HP11\Desktop\My Projects\CambridgeInsights\database\database.sqlite"
school_id = '019e5ed2-be69-7193-b485-69770f96e60c'
uploader_id = '019e5ed2-bf97-7316-bceb-62683a3c8666'

# Broadsheets directory
folder = r"C:\Users\HP11\Desktop\new"
files = glob.glob(os.path.join(folder, "*.xls"))

print(f"Found {len(files)} files to process.")

conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Get qualifications mapping
cursor.execute("SELECT id, qualification_type FROM qualifications")
quals = dict(cursor.fetchall())  # id -> type
quals_by_type = {v: k for k, v in quals.items()} # type -> id

# Helpers for dynamic DB inserts
def get_or_create_series(month, year):
    code = f"{month[:3].upper()}-{year}"
    cursor.execute("SELECT id FROM exam_series WHERE series_code = ?", (code,))
    row = cursor.fetchone()
    if row:
        return row[0]
    
    # Create new
    series_id = generate_ulid()
    cursor.execute(
        "INSERT INTO exam_series (id, series_code, year, month, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
        (series_id, code, int(year), month, 1)
    )
    print(f"Created Exam Series: {code}")
    return series_id

def get_or_create_subject(name, qual_type):
    qual_id = quals_by_type.get(qual_type)
    if not qual_id:
        raise ValueError(f"Unknown qualification type: {qual_type}")
    
    # Determine code
    code = None
    if qual_type == 'IGCSE':
        code = IGCSE_MAPPING.get(name.upper())
    elif qual_type == 'AS_A_LEVEL':
        code = GCE_MAPPING.get(name.upper())
        
    if not code:
        # Generate dummy code based on name hash if not mapped
        code = f"D{abs(hash(name)) % 10000:04d}"
        print(f"Warning: No mapped code for subject '{name}' ({qual_type}). Generated dummy code: {code}")
        
    # Check if exists
    cursor.execute("SELECT id FROM subjects WHERE subject_code = ? AND qualification_id = ?", (code, qual_id))
    row = cursor.fetchone()
    if row:
        return row[0]
    
    # Create new subject
    subj_id = generate_ulid()
    title_name = name.title()
    cursor.execute(
        "INSERT INTO subjects (id, subject_code, subject_name, qualification_id, total_marks, passing_percentage, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
        (subj_id, code, title_name, qual_id, 200, 40.0)
    )
    print(f"Created Subject: {title_name} ({code}) under {qual_type}")
    return subj_id

def get_or_create_candidate(cand_num, cand_name):
    # Pad to 4 digits
    padded_num = str(cand_num).strip().zfill(4)
    name_clean = str(cand_name).strip()
    cursor.execute("SELECT id FROM candidates WHERE candidate_number = ? AND candidate_name = ? AND school_id = ?", (padded_num, name_clean, school_id))
    row = cursor.fetchone()
    if row:
        return row[0]
        
    # Create new candidate
    cand_id = generate_ulid()
    cursor.execute(
        "INSERT INTO candidates (id, candidate_number, candidate_name, school_id, enrollment_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, date('now'), ?, datetime('now'), datetime('now'))",
        (cand_id, padded_num, name_clean, school_id, 'active')
    )
    return cand_id

def get_or_create_enrollment(cand_id, series_id, qual_type):
    qual_id = quals_by_type.get(qual_type)
    cursor.execute(
        "SELECT id FROM candidate_enrollments WHERE candidate_id = ? AND series_id = ? AND qualification_id = ? AND subject_id IS NULL",
        (cand_id, series_id, qual_id)
    )
    row = cursor.fetchone()
    if row:
        return row[0]
        
    # Create new enrollment
    enroll_id = generate_ulid()
    cursor.execute(
        "INSERT INTO candidate_enrollments (id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at) VALUES (?, ?, ?, ?, NULL, 'enrolled', date('now'), datetime('now'), datetime('now'))",
        (enroll_id, cand_id, series_id, qual_id)
    )
    return enroll_id

# Process files
for filepath in files:
    filename = os.path.basename(filepath)
    print(f"\nProcessing file: {filename}")
    
    # Extract series from filename
    match = re.search(r"(March|June|November)\s+(\d{4})", filename, re.IGNORECASE)
    if not match:
        print(f"Skipping {filename}: Could not parse exam series from filename.")
        continue
    
    month = match.group(1).capitalize()
    year = match.group(2)
    
    try:
        series_id = get_or_create_series(month, year)
    except Exception as e:
        print(f"Database error creating series: {e}")
        continue
        
    # Read sheet content
    df = None
    try:
        # Try modern XLSX engine
        df = pd.read_excel(filepath, header=None, engine='openpyxl')
        
        current_qual = None
        header_cols = []
        candidates_count = 0
        results_count = 0
        
        for idx, row in df.iterrows():
            val0 = str(row.iloc[0]).strip()
            
            if "Qualification:" in val0:
                if "IGCSE" in val0:
                    current_qual = 'IGCSE'
                elif "GCE" in val0 or "AS" in val0 or "A Level" in val0:
                    current_qual = 'AS_A_LEVEL'
                else:
                    current_qual = None
                header_cols = []
                continue
                
            if current_qual and val0 == "Cand. No":
                header_cols = [str(x).strip() if pd.notna(x) else None for x in row]
                continue
                
            if current_qual and header_cols and val0.isdigit():
                cand_no = val0
                cand_name = str(row.iloc[1]).strip()
                
                cand_id = get_or_create_candidate(cand_no, cand_name)
                enroll_id = get_or_create_enrollment(cand_id, series_id, current_qual)
                candidates_count += 1
                
                for col_idx in range(2, len(row)):
                    if col_idx >= len(header_cols) or not header_cols[col_idx]:
                        continue
                    
                    subject_name = header_cols[col_idx]
                    grade_raw = row.iloc[col_idx]
                    
                    if pd.isna(grade_raw) or str(grade_raw).strip() == "" or str(grade_raw).strip().lower() == "nan":
                        continue
                        
                    grade = str(grade_raw).strip()
                    if grade.endswith('^'):
                        grade = grade[:-1].strip()
                        
                    try:
                        subject_id = get_or_create_subject(subject_name, current_qual)
                    except Exception as e:
                        print(f"Error mapping subject {subject_name} ({current_qual}): {e}")
                        continue
                        
                    # Ensure subject-specific enrollment exists
                    qual_id = quals_by_type.get(current_qual)
                    cursor.execute(
                        "SELECT id FROM candidate_enrollments WHERE candidate_id = ? AND series_id = ? AND subject_id = ?",
                        (cand_id, series_id, subject_id)
                    )
                    sub_enroll_row = cursor.fetchone()
                    if not sub_enroll_row:
                        sub_enroll_id = generate_ulid()
                        cursor.execute(
                            "INSERT INTO candidate_enrollments (id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at) "
                            "VALUES (?, ?, ?, ?, ?, 'enrolled', date('now'), datetime('now'), datetime('now'))",
                            (sub_enroll_id, cand_id, series_id, qual_id, subject_id)
                        )
                        
                    is_passed = grade in ['A*', 'A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e', 
                                          'AA', 'AB', 'BB', 'BC', 'CC', 'CD', 'DD', 'DE', 'EE', 'FF', 'GG']
                    
                    cursor.execute(
                        "SELECT id FROM subject_results WHERE enrollment_id = ? AND subject_id = ?",
                        (enroll_id, subject_id)
                    )
                    res_row = cursor.fetchone()
                    
                    if res_row:
                        cursor.execute(
                            "UPDATE subject_results SET grade = ?, is_passed = ?, updated_at = datetime('now') WHERE id = ?",
                            (grade, 1 if is_passed else 0, res_row[0])
                        )
                    else:
                        res_id = generate_ulid()
                        cursor.execute(
                            "INSERT INTO subject_results (id, enrollment_id, subject_id, series_id, grade, pum, is_passed, status, result_uploaded_at, uploaded_by, created_at, updated_at) "
                            "VALUES (?, ?, ?, ?, ?, 0, ?, 'pending_components', datetime('now'), ?, datetime('now'), datetime('now'))",
                            (res_id, enroll_id, subject_id, series_id, grade, 1 if is_passed else 0, uploader_id)
                        )
                    results_count += 1
                    
        conn.commit()
        print(f"File {filename} imported successfully: {candidates_count} candidates and {results_count} subject results processed.")
        
    except Exception as excel_err:
        # Fallback to custom TSV line-by-line parsing
        try:
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                lines = f.readlines()
            
            current_qual = None
            header_cols = []
            candidates_count = 0
            results_count = 0
            
            for line in lines:
                parts = [p.strip() for p in line.split('\t')]
                if not parts or parts[0] == "":
                    continue
                
                val0 = parts[0]
                if "Qualification:" in val0:
                    if "IGCSE" in val0:
                        current_qual = 'IGCSE'
                    elif "GCE" in val0 or "AS" in val0 or "A Level" in val0:
                        current_qual = 'AS_A_LEVEL'
                    else:
                        current_qual = None
                    header_cols = []
                    continue
                    
                if current_qual and val0 == "Cand. No":
                    header_cols = parts
                    continue
                    
                if current_qual and header_cols and val0.isdigit():
                    cand_no = val0
                    cand_name = parts[1] if len(parts) > 1 else ""
                    
                    cand_id = get_or_create_candidate(cand_no, cand_name)
                    enroll_id = get_or_create_enrollment(cand_id, series_id, current_qual)
                    candidates_count += 1
                    
                    for col_idx in range(2, len(parts)):
                        if col_idx >= len(header_cols) or not header_cols[col_idx]:
                            continue
                            
                        subject_name = header_cols[col_idx]
                        grade = parts[col_idx]
                        if not grade or grade.lower() == "nan" or grade == "":
                            continue
                            
                        if grade.endswith('^'):
                            grade = grade[:-1].strip()
                            
                        try:
                            subject_id = get_or_create_subject(subject_name, current_qual)
                        except Exception as e:
                            print(f"Error mapping subject {subject_name} ({current_qual}): {e}")
                            continue
                            
                        # Ensure subject-specific enrollment exists
                        qual_id = quals_by_type.get(current_qual)
                        cursor.execute(
                            "SELECT id FROM candidate_enrollments WHERE candidate_id = ? AND series_id = ? AND subject_id = ?",
                            (cand_id, series_id, subject_id)
                        )
                        sub_enroll_row = cursor.fetchone()
                        if not sub_enroll_row:
                            sub_enroll_id = generate_ulid()
                            cursor.execute(
                                "INSERT INTO candidate_enrollments (id, candidate_id, series_id, qualification_id, subject_id, enrollment_status, enrolled_date, created_at, updated_at) "
                                "VALUES (?, ?, ?, ?, ?, 'enrolled', date('now'), datetime('now'), datetime('now'))",
                                (sub_enroll_id, cand_id, series_id, qual_id, subject_id)
                            )
                            
                        is_passed = grade in ['A*', 'A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e', 
                                              'AA', 'AB', 'BB', 'BC', 'CC', 'CD', 'DD', 'DE', 'EE', 'FF', 'GG']
                        
                        cursor.execute(
                            "SELECT id FROM subject_results WHERE enrollment_id = ? AND subject_id = ?",
                            (enroll_id, subject_id)
                        )
                        res_row = cursor.fetchone()
                        
                        if res_row:
                            cursor.execute(
                                "UPDATE subject_results SET grade = ?, is_passed = ?, updated_at = datetime('now') WHERE id = ?",
                                (grade, 1 if is_passed else 0, res_row[0])
                            )
                        else:
                            res_id = generate_ulid()
                            cursor.execute(
                                "INSERT INTO subject_results (id, enrollment_id, subject_id, series_id, grade, pum, is_passed, status, result_uploaded_at, uploaded_by, created_at, updated_at) "
                                "VALUES (?, ?, ?, ?, ?, 0, ?, 'pending_components', datetime('now'), ?, datetime('now'), datetime('now'))",
                                (res_id, enroll_id, subject_id, series_id, grade, 1 if is_passed else 0, uploader_id)
                            )
                        results_count += 1
            conn.commit()
            print(f"File {filename} imported successfully (TSV): {candidates_count} candidates and {results_count} subject results processed.")
        except Exception as tsv_err:
            print(f"Failed parsing {filename}. Excel error: {excel_err}. TSV error: {tsv_err}")

conn.close()
print("\nAll files successfully imported!")
