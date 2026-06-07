import sqlite3
import time
import random

db_path = r"C:\Users\HP11\Desktop\My Projects\CambridgeInsights\database\database.sqlite"
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

ALPHABET = "0123456789abcdefghjkmnpqrstvwxyz"
def generate_ulid():
    timestamp = int(time.time() * 1000)
    ts_str = ""
    for _ in range(10):
        ts_str = ALPHABET[timestamp % 32] + ts_str
        timestamp //= 32
    rand_str = ""
    for _ in range(16):
        rand_str += random.choice(ALPHABET)
    return ts_str + rand_str

# Get qualification IDs
cursor.execute("SELECT id, qualification_type FROM qualifications")
quals = dict(cursor.fetchall())
quals_by_type = {v: k for k, v in quals.items()}
igcse_qual_id = quals_by_type.get('IGCSE')
gce_qual_id = quals_by_type.get('AS_A_LEVEL')

# 1. Merge Accounting dummy codes (D6412, D3753) into IGCSE Accounting (0452)
# Check if 0452 exists, if not create it
cursor.execute("SELECT id FROM subjects WHERE subject_code = ? AND qualification_id = ?", ('0452', igcse_qual_id))
acc_row = cursor.fetchone()
if acc_row:
    acc_id = acc_row[0]
else:
    acc_id = generate_ulid()
    cursor.execute(
        "INSERT INTO subjects (id, subject_code, subject_name, qualification_id, total_marks, passing_percentage, created_at, updated_at) "
        "VALUES (?, ?, ?, ?, 200, 40.0, datetime('now'), datetime('now'))",
        (acc_id, '0452', 'Accounting', igcse_qual_id)
    )
    print("Created IGCSE Accounting (0452)")

# Find dummy codes
cursor.execute("SELECT id, subject_code FROM subjects WHERE subject_code IN ('D6412', 'D3753')")
dummy_rows = cursor.fetchall()
for dummy_id, dummy_code in dummy_rows:
    print(f"Merging dummy subject {dummy_code} into 0452...")
    
    # Update subject_results defensively
    cursor.execute("SELECT id, enrollment_id, series_id, grade, pum FROM subject_results WHERE subject_id = ?", (dummy_id,))
    dummy_results = cursor.fetchall()
    for res_id, enroll_id, series_id, grade, pum in dummy_results:
        # Check if already exists for 0452
        cursor.execute("SELECT id FROM subject_results WHERE enrollment_id = ? AND subject_id = ? AND series_id = ?", (enroll_id, acc_id, series_id))
        existing_res = cursor.fetchone()
        if existing_res:
            # Delete duplicate dummy record
            cursor.execute("DELETE FROM component_marks WHERE subject_result_id = ?", (res_id,))
            cursor.execute("DELETE FROM subject_results WHERE id = ?", (res_id,))
        else:
            # Update to target subject
            cursor.execute("UPDATE subject_results SET subject_id = ? WHERE id = ?", (acc_id, res_id))
            
    # Update candidate_enrollments defensively
    cursor.execute("SELECT id, candidate_id, series_id FROM candidate_enrollments WHERE subject_id = ?", (dummy_id,))
    dummy_enrolls = cursor.fetchall()
    for en_id, cand_id, series_id in dummy_enrolls:
        # Check if already exists for 0452
        cursor.execute("SELECT id FROM candidate_enrollments WHERE candidate_id = ? AND series_id = ? AND subject_id = ?", (cand_id, series_id, acc_id))
        existing_en = cursor.fetchone()
        if existing_en:
            # Delete duplicate dummy record
            cursor.execute("DELETE FROM candidate_enrollments WHERE id = ?", (en_id,))
        else:
            # Update to target subject
            cursor.execute("UPDATE candidate_enrollments SET subject_id = ? WHERE id = ?", (acc_id, en_id))
            
    # Delete dummy subject
    cursor.execute("DELETE FROM subjects WHERE id = ?", (dummy_id,))

# 2. Rename subjects if necessary
renames = [
    # (code, qual_id, new_name)
    ('0625', igcse_qual_id, 'Physics (Pure)'),
    ('0620', igcse_qual_id, 'Chemistry (Pure)'),
    ('0610', igcse_qual_id, 'Biology (Pure)'),
    ('0520', igcse_qual_id, 'French (Foreign Language)'),
    ('8021', gce_qual_id, 'English General Paper')
]

for code, qid, new_name in renames:
    cursor.execute("UPDATE subjects SET subject_name = ?, updated_at = datetime('now') WHERE subject_code = ? AND qualification_id = ?", (new_name, code, qid))
    print(f"Renamed {code} to {new_name}")

# 3. Delete redundant subjects with 0 results
cursor.execute("SELECT id, subject_code, subject_name FROM subjects WHERE subject_code = '9231'")
redundant = cursor.fetchall()
for rid, rcode, rname in redundant:
    cursor.execute("SELECT COUNT(*) FROM subject_results WHERE subject_id = ?", (rid,))
    res_count = cursor.fetchone()[0]
    if res_count == 0:
        cursor.execute("DELETE FROM subjects WHERE id = ?", (rid,))
        print(f"Deleted redundant subject {rname} ({rcode})")

# 4. Sync components
components_map = {
    # IGCSE Subjects
    '0510': [
        ('1', 'Reading and Writing', 'paper'),
        ('2', 'Listening', 'paper'),
        ('3', 'Speaking Test', 'practical')
    ],
    '0549': [
        ('1', 'Reading and Writing', 'paper'),
        ('2', 'Listening', 'paper')
    ],
    '0520': [
        ('1', 'Listening', 'paper'),
        ('2', 'Reading', 'paper'),
        ('3', 'Speaking', 'practical'),
        ('4', 'Writing', 'paper')
    ],
    '0580': [
        ('2', 'Calculator', 'paper'),
        ('4', 'Non Calculator', 'paper')
    ],
    '0455': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'Structured Questions', 'paper')
    ],
    '0625': [
        ('2', 'Multiple Choice', 'paper'),
        ('4', 'Theory (Extended)', 'paper'),
        ('6', 'Alternative to Practical', 'practical')
    ],
    '0620': [
        ('2', 'Multiple Choice', 'paper'),
        ('4', 'Theory (Extended)', 'paper'),
        ('6', 'Alternative to Practical', 'practical')
    ],
    '0610': [
        ('2', 'Multiple Choice', 'paper'),
        ('4', 'Theory (Extended)', 'paper'),
        ('6', 'Alternative to Practical', 'practical')
    ],
    '0653': [
        ('2', 'Multiple Choice', 'paper'),
        ('4', 'Theory', 'paper'),
        ('6', 'Alternative to Practical', 'practical')
    ],
    '0452': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'Structured Written Paper', 'paper')
    ],
    '0478': [
        ('1', 'Theory', 'paper'),
        ('2', 'Problem-solving and Programming', 'paper')
    ],
    '0400': [
        ('1', 'Coursework', 'project'),
        ('2', 'Externally Set Assignment', 'other')
    ],
    
    # AS / A Level Subjects
    '9709': [
        ('1', 'Pure Mathematics 1', 'paper'),
        ('3', 'Pure Mathematics 3', 'paper'),
        ('5', 'Probability & Statistics 1', 'paper'),
        ('6', 'Probability & Statistics 2', 'paper')
    ],
    '9626': [
        ('1', 'Theory', 'paper'),
        ('2', 'Practical', 'practical'),
        ('3', 'Advanced Theory', 'paper'),
        ('4', 'Advanced Practical', 'practical')
    ],
    '9618': [
        ('1', 'Theory Fundamentals', 'paper'),
        ('2', 'Fundamental Problem Solving and Programming Skills', 'paper'),
        ('3', 'Advanced Theory', 'paper'),
        ('4', 'Practical', 'practical')
    ],
    '9990': [
        ('1', 'Approaches, Issues and Debates', 'paper'),
        ('2', 'Research Methods', 'paper'),
        ('3', 'Specialist Options: Approaches, Issues and Debates', 'paper'),
        ('4', 'Specialist Options: Application and Research Methods', 'paper')
    ],
    '9702': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'AS Level Structured Questions', 'paper'),
        ('3', 'Advanced Practical Skills', 'practical'),
        ('4', 'A Level Structured Questions', 'paper'),
        ('5', 'Planning, Analysis and Evaluation', 'paper')
    ],
    '9701': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'AS Level Structured Questions', 'paper'),
        ('3', 'Advanced Practical Skills', 'practical'),
        ('4', 'A Level Structured Questions', 'paper'),
        ('5', 'Planning, Analysis and Evaluation', 'paper')
    ],
    '9700': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'AS Level Structured Questions', 'paper'),
        ('3', 'Advanced Practical Skills', 'practical'),
        ('4', 'A Level Structured Questions', 'paper'),
        ('5', 'Planning, Analysis and Evaluation', 'paper')
    ],
    '9708': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'Data Response and Essays', 'paper'),
        ('3', 'Multiple Choice', 'paper'),
        ('4', 'Data Response and Essays', 'paper')
    ],
    '9609': [
        ('1', 'Business Concepts 1', 'paper'),
        ('2', 'Business Concepts 2', 'paper'),
        ('3', 'Business Decision-Making', 'paper'),
        ('4', 'Business Strategy', 'paper')
    ],
    '9706': [
        ('1', 'Multiple Choice', 'paper'),
        ('2', 'Fundamentals of Accounting', 'paper'),
        ('3', 'Financial Accounting', 'paper'),
        ('4', 'Management Accounting', 'paper')
    ],
    '8021': [
        ('1', 'Essay', 'paper'),
        ('2', 'Comprehension', 'paper')
    ]
}

# Go through each mapped subject, ensure subject exists, and sync components
for code, comps in components_map.items():
    # Find subject (could be under IGCSE or AS_A_LEVEL)
    cursor.execute("SELECT id, subject_name FROM subjects WHERE subject_code = ?", (code,))
    subj_rows = cursor.fetchall()
    
    for subj_id, subj_name in subj_rows:
        print(f"\nSyncing components for {subj_name} ({code})...")
        for comp_code, comp_name, comp_type in comps:
            # Check if component exists
            cursor.execute("SELECT id FROM components WHERE subject_id = ? AND component_code = ?", (subj_id, comp_code))
            comp_row = cursor.fetchone()
            
            if comp_row:
                # Update
                cursor.execute(
                    "UPDATE components SET component_name = ?, component_type = ?, updated_at = datetime('now') WHERE id = ?",
                    (comp_name, comp_type, comp_row[0])
                )
            else:
                # Insert
                comp_id = generate_ulid()
                cursor.execute(
                    "INSERT INTO components (id, subject_id, component_code, component_name, component_type, total_marks, created_at, updated_at) "
                    "VALUES (?, ?, ?, ?, ?, 100, datetime('now'), datetime('now'))",
                    (comp_id, subj_id, comp_code, comp_name, comp_type)
                )
                print(f"Created component {comp_code}: {comp_name}")

conn.commit()
conn.close()
print("\nSubjects and components successfully reframed and updated!")
