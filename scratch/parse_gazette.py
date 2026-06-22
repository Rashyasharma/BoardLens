import os
import re
import glob

def parse_file(path):
    print(f"Parsing: {os.path.basename(path)}")
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()
    
    # Extract year and exam name from header
    year_match = re.search(r"EXAMINATION\s*(?:\([^)]*\))?-(\d{4})", content)
    exam_match = re.search(r"C\.B\.S\.E\.\s*-\s*([^-\n\r]+)", content)
    
    exam_year = int(year_match.group(1)) if year_match else None
    exam_name = exam_match.group(1).strip() if exam_match else ""
    
    qual_type = "CLASS_10" if "SECONDARY" in exam_name.upper() else "CLASS_12"
    
    lines = content.splitlines()
    students = []
    
    i = 0
    while i < len(lines):
        line = lines[i]
        match = re.match(r"^\s*(\d{8})\s+([MFO])\s+(.+)$", line)
        if match:
            roll = match.group(1)
            gender = match.group(2)
            rest = match.group(3)
            
            parts = re.split(r"\s{2,}", rest.strip())
            name = parts[0]
            
            subjects = []
            result_status = "PASS"
            for p in parts[1:]:
                p = p.strip()
                if re.match(r"^\d{3}$", p):
                    subjects.append(p)
                elif p in ("PASS", "COMP", "ABST", "FAIL", "COMPTT.", "ER"):
                    result_status = p
            
            # Find next non-empty line
            marks_line = ""
            j = i + 1
            while j < len(lines):
                next_line = lines[j]
                if next_line.strip() == "":
                    j += 1
                    continue
                if re.match(r"^\s*(\d{8})\s+", next_line):
                    break
                if "TOTAL" in next_line or "SCHOOL" in next_line:
                    break
                marks_line = next_line
                break
            
            subject_results = []
            if marks_line:
                m_parts = [p.strip() for p in marks_line.split() if p.strip()]
                
                # Let's decide if this is pure marks, or pairs of marks and grades
                # If m_parts has roughly the same number of elements as subjects, it's pure marks.
                # If it has 2x elements, it's pairs.
                if len(m_parts) >= len(subjects) * 2:
                    # Pairs
                    for idx, sub_code in enumerate(subjects):
                        m_obt = None
                        gr = None
                        if idx * 2 < len(m_parts):
                            m_obt = m_parts[idx * 2]
                        if idx * 2 + 1 < len(m_parts):
                            gr = m_parts[idx * 2 + 1]
                        subject_results.append({
                            "subject_code": sub_code,
                            "marks_obtained": m_obt,
                            "grade": gr
                        })
                else:
                    # Pure marks
                    for idx, sub_code in enumerate(subjects):
                        m_obt = None
                        if idx < len(m_parts):
                            m_obt = m_parts[idx]
                        subject_results.append({
                            "subject_code": sub_code,
                            "marks_obtained": m_obt,
                            "grade": None
                        })
            else:
                # Absent or no marks line
                for sub_code in subjects:
                    subject_results.append({
                        "subject_code": sub_code,
                        "marks_obtained": "ABST" if result_status == "ABST" else None,
                        "grade": "F" if result_status == "ABST" else None
                    })
            
            students.append({
                "roll_number": roll,
                "gender": gender,
                "student_name": name,
                "result_status": result_status,
                "subjects": subject_results
            })
            i = j - 1
        i += 1
        
    print(f"  Found {len(students)} students.")
    if students:
        print(f"  First student: {students[0]}")
    return students

files = glob.glob(r"C:\Users\HP11\Desktop\Downloads\11140*.TXT") + glob.glob(r"C:\Users\HP11\Desktop\Downloads\11140*.txt")
for path in files:
    parse_file(path)
