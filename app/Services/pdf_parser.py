import fitz
import re
import sys
import json

# Known multi-line subject title fragments to join
SUBJECT_CODE_PATTERN = re.compile(r'^\d{4}$')
GRADE_PATTERN = re.compile(r'^([A-Ea-e]\*?|[A-Ea-e]\*?\([A-Ea-e]\*?\)|ungraded|UNGRADED|[A-Ea-e]\*?-[A-Ea-e]\*?)$', re.IGNORECASE)


def clean_provisional_grade(g_val):
    raw = g_val.strip()
    m = re.match(r'^([A-Za-z*]+)\(', raw)
    if m:
        extracted = m.group(1)
    else:
        extracted = raw
        
    if extracted in ('a', 'b', 'c', 'd', 'e', 'aa', 'bb', 'cc', 'dd', 'ee'):
        return extracted
    
    upp = extracted.upper()
    if upp in ("UNGRADED", "U"):
        return "U"
    if upp in ("PENDING", "PEND", "Q"):
        return "Q"
    if upp in ("NO RESULT", "NORESULT", "NO_RESULT", "X"):
        return "X"
    return upp if len(upp) <= 2 else extracted


def get_midpoint_pum(grade, qualification=None, subject_code=None):
    g = grade.strip()
    if g in ('a', 'b', 'c', 'd', 'e'):
        m_map = {
            'a': 90.0,  # Midpoint of 80-100 range
            'b': 74.5,  # Midpoint of 70-79 range
            'c': 64.5,  # Midpoint of 60-69 range
            'd': 54.5,  # Midpoint of 50-59 range
            'e': 44.5   # Midpoint of 40-49 range
        }
        return m_map.get(g, 0.0)
    
    g_upper = g.upper()
    if g_upper in ('A*', 'A*A*'):
        return 95.0
    elif g_upper in ('A', 'AA'):
        return 84.5
    elif g_upper in ('B', 'BB'):
        return 74.5
    elif g_upper in ('C', 'CC'):
        return 64.5
    elif g_upper in ('D', 'DD'):
        return 54.5
    elif g_upper in ('E', 'EE'):
        return 44.5
    elif g_upper in ('F', 'FF'):
        return 34.5
    elif g_upper in ('G', 'GG'):
        return 24.5
    return 0.0


def is_grade(s):
    """Return True if s looks like a Cambridge grade line."""
    s = s.strip()
    # e.g. "A(a)", "A*(a*)", "B(b)", "E(e)", "U", "Ungraded", "G(g)", "F(f)", "A*A*", "AA", "BB", "FF", "BB (76)", "BB(bb)"
    if re.match(r'^[A-Ga-g]\*?\([a-g]\*?\)$', s, re.IGNORECASE):
        return True
    if re.match(r'^[A-Ga-g]\*?[A-Ga-g]\*?(\([a-g]\*?[a-g]\*?\))?$', s, re.IGNORECASE):
        return True
    if s.upper() in ('U', 'UNGRADED', 'X', 'PENDING', 'PEND', 'Q', 'NO RESULT', 'NORESULT', 'NO_RESULT'):
        return True
    return False


def is_pum(s):
    """Return True if s is a numeric PUM."""
    return s.strip().isdigit()


def is_subject_code(s):
    """Return True if s is a 4-digit syllabus code."""
    return bool(SUBJECT_CODE_PATTERN.match(s.strip()))


def parse_provisional_page(lines):
    # Clean up lines
    lines = [l.strip() for l in lines if l.strip()]

    # Find Candidate Name dynamically by finding 'Candidate Name' label or checking index
    cand_name = None
    try:
        # Layout 1 (stacked labels at lines[5..9]): 'Candidate Name' is at some index, name is at lines[1]
        # Layout 2 (labels are column headers or stacked, name is near label):
        # Let's search for "Candidate Name" index
        cand_name_lbl_idx = -1
        for idx, line in enumerate(lines):
            if "Candidate Name" in line:
                cand_name_lbl_idx = idx
                break
        
        if cand_name_lbl_idx != -1:
            # If the label is at index 3, and name is at 6 (November 2019 layout: Candidate Name, Date of Birth, Centre/Cand No, then ARYAN, 15/12/2004, IN016/0001)
            # In November 2019, labels are: [3] Candidate Name, [4] Date of Birth, [5] Centre / Cand. No.
            # Values are: [6] ARYAN, [7] 15/12/2004, [8] IN016 / 0001
            if cand_name_lbl_idx == 3 and len(lines) > 6:
                cand_name = lines[6].strip()
            elif cand_name_lbl_idx == 5 and len(lines) > 1: # March 2020 layout
                cand_name = lines[1].strip()
            else:
                # Fallback: check surrounding lines
                if cand_name_lbl_idx + 1 < len(lines) and not any(x in lines[cand_name_lbl_idx+1] for x in ["Date of Birth", "Centre", "Session"]):
                    cand_name = lines[cand_name_lbl_idx+1].strip()
        
        # If still not found, default to lines[1] if it's not a known label/header
        if not cand_name and len(lines) > 1:
            cand_name = lines[1].strip()
    except Exception:
        if len(lines) > 1:
            cand_name = lines[1].strip()

    # --- Centre/Cand No ---
    cand_no = None
    for line in lines:
        m = re.search(r'[A-Z]{2}\d{3}\s*/\s*(\d+)', line)
        if m:
            cand_no = m.group(1).zfill(4)
            break

    # --- Series / Session ---
    series = None
    for line in lines:
        m = re.search(r'(March|June|November)\s+(\d{4})', line, re.IGNORECASE)
        if m:
            series = {"month": m.group(1).capitalize(), "year": int(m.group(2))}
            break

    # --- Qualification ---
    qual = "IGCSE"
    for line in lines:
        u = line.upper()
        if "GCE" in u or "AS & A LEVEL" in u or "AS AND A LEVEL" in u:
            if "IGCSE" not in u:
                qual = "AS_A_LEVEL"
                break
        if u.strip() == "GCE AS AND A LEVEL" or u.strip() == "AS_A_LEVEL":
            qual = "AS_A_LEVEL"
            break

    # --- Find start of results block ---
    start_idx = -1
    for idx, line in enumerate(lines):
        if "Percentage Uniform Mark" in line:
            start_idx = idx + 1
            break
    if start_idx == -1:
        for idx, line in enumerate(lines):
            if line.strip() == "Result":
                start_idx = idx + 1
                break
    if start_idx == -1:
        for idx, line in enumerate(lines):
            if "Syllabus Title" in line:
                start_idx = idx + 1
                break

    results = {}

    if start_idx != -1:
        i = start_idx
        while i < len(lines):
            line = lines[i]

            # Stop at footer
            if any(x in line for x in ["This Provisional", "THIS IS NOT", "Date Printed:", "not an official"]):
                break

            # Try parsing Layout 3: Grade -> PUM -> Code -> Title (e.g. November 2020 layout with PUM)
            if i + 3 < len(lines):
                g_val = lines[i].strip()
                p_val = lines[i+1].strip()
                c_val = lines[i+2].strip()
                t_val = lines[i+3].strip()

                if is_grade(g_val) and is_pum(p_val) and is_subject_code(c_val):
                    clean_grade = clean_provisional_grade(g_val)
                    if clean_grade in ("Q", "X"):
                        p_val = "0"
                    pum = float(p_val)
                    
                    title_parts = [t_val]
                    next_idx = i + 4
                    while next_idx < len(lines):
                        nxt_line = lines[next_idx]
                        if is_grade(nxt_line) or is_subject_code(nxt_line) or any(x in nxt_line for x in ["This Provisional", "THIS IS NOT", "Date Printed:", "not an official"]):
                            break
                        title_parts.append(nxt_line)
                        next_idx += 1
                    
                    title_clean = " ".join(title_parts).strip()
                    title_clean = re.sub(r'\s+With Grade\s+.*', '', title_clean, flags=re.IGNORECASE).strip()
                    results[c_val] = {
                        "grade": clean_grade,
                        "pum": pum,
                        "raw_value": f"{g_val} ({int(pum)})",
                        "title": title_clean.upper(),
                        "subject_code": c_val
                    }
                    i = next_idx
                    continue

            # Try parsing Layout 4: Grade -> Code -> Title (e.g. November 2020 layout with NO RESULT / no PUM)
            if i + 2 < len(lines):
                g_val = lines[i].strip()
                c_val = lines[i+1].strip()
                t_val = lines[i+2].strip()

                if is_grade(g_val) and is_subject_code(c_val):
                    clean_grade = clean_provisional_grade(g_val)
                    if clean_grade in ("Q", "X"):
                        pum = 0.0
                    else:
                        pum = get_midpoint_pum(clean_grade, qual, c_val)
                    
                    title_parts = [t_val]
                    next_idx = i + 3
                    while next_idx < len(lines):
                        nxt_line = lines[next_idx]
                        if is_grade(nxt_line) or is_subject_code(nxt_line) or any(x in nxt_line for x in ["This Provisional", "THIS IS NOT", "Date Printed:", "not an official"]):
                            break
                        title_parts.append(nxt_line)
                        next_idx += 1
                    
                    title_clean = " ".join(title_parts).strip()
                    title_clean = re.sub(r'\s+With Grade\s+.*', '', title_clean, flags=re.IGNORECASE).strip()
                    results[c_val] = {
                        "grade": clean_grade,
                        "pum": pum,
                        "raw_value": f"{g_val} ({int(pum)})",
                        "title": title_clean.upper(),
                        "subject_code": c_val
                    }
                    i = next_idx
                    continue

            # Try parsing Layout 1: Code -> Title -> Grade -> PUM
            if i + 3 < len(lines):
                c_val = lines[i].strip()
                t_val = lines[i+1].strip()
                g_val = lines[i+2].strip()
                p_val = lines[i+3].strip()
                
                if is_subject_code(c_val) and is_grade(g_val) and is_pum(p_val):
                    clean_grade = clean_provisional_grade(g_val)
                    if clean_grade in ("Q", "X"):
                        p_val = "0"
                    pum = float(p_val)
                    results[c_val] = {
                        "grade": clean_grade,
                        "pum": pum,
                        "raw_value": f"{g_val} ({int(pum)})",
                        "title": t_val.upper(),
                        "subject_code": c_val
                    }
                    next_idx = i + 4
                    if next_idx < len(lines) and "Oral/Aural" in lines[next_idx]:
                        next_idx += 1
                    i = next_idx
                    continue

            # Try parsing Layout 2: Title (can wrap) -> Grade -> PUM -> Code
            title_parts = []
            j = i
            while j < len(lines):
                curr_line = lines[j]
                if is_grade(curr_line) or is_pum(curr_line) or is_subject_code(curr_line):
                    break
                if any(x in curr_line for x in ["This Provisional", "THIS IS NOT", "not an official"]):
                    break
                title_parts.append(curr_line)
                j += 1

            title = " ".join(title_parts).strip()

            if j + 2 < len(lines):
                grade_line = lines[j]
                pum_line = lines[j + 1]
                code_line = lines[j + 2]

                if is_grade(grade_line) and is_pum(pum_line) and is_subject_code(code_line):
                    clean_grade = clean_provisional_grade(grade_line)
                    if clean_grade in ("Q", "X"):
                        pum_line = "0"
                    pum = float(pum_line.strip())
                    code = code_line.strip()

                    title_clean = re.sub(r'\s+With Grade\s+.*', '', title, flags=re.IGNORECASE).strip()
                    if title_clean.endswith("SECOND"):
                        title_clean = title_clean.strip() + " LANGUAGE"

                    results[code] = {
                        "grade": clean_grade,
                        "pum": pum,
                        "raw_value": f"{grade_line} ({int(pum)})",
                        "title": title_clean.upper(),
                        "subject_code": code
                    }

                    i = j + 3
                    continue

            i += 1

    return cand_name, cand_no, series, qual, results


def parse_pdf(file_path):
    try:
        doc = fitz.open(file_path)
    except Exception as e:
        return {"error": f"Failed to open PDF: {str(e)}"}

    candidates = []
    subjects_mapped = {}
    global_series = None
    global_qual = "IGCSE"

    is_provisional = False
    is_electronic = False
    is_entry_report = False

    for page_idx, page in enumerate(doc):
        text = page.get_text()
        lines = [line.strip() for line in text.split('\n') if line.strip()]

        if len(lines) < 5:
            continue

        # Detect format
        is_provisional = False
        is_electronic = False
        is_entry_report = False
        is_broadsheet = False
        first_lines_text = " ".join(lines[:10]).lower()
        if "provisional" in first_lines_text:
            is_provisional = True
        elif "electronic statement" in first_lines_text:
            is_electronic = True
        elif "entry report grouped" in first_lines_text or "entry report" in first_lines_text:
            is_entry_report = True
        elif "results broadsheet" in first_lines_text or "broadsheet" in first_lines_text:
            is_broadsheet = True

        if is_entry_report:
            # Let's find the syllabus details
            # Typically page starts with Page, Entry Report..., June 2026 Series, Lucky..., IGCSE 0417: ..., Date created
            # We want to extract Series, Qualification, Subject code & title
            # and map candidates listed on the page
            series = None
            for line in lines[:8]:
                m = re.search(r'(March|June|November)\s+(\d{4})\s+Series', line, re.IGNORECASE)
                if m:
                    series = {"month": m.group(1).capitalize(), "year": int(m.group(2))}
                    break
            
            # Find subject line: e.g. "IGCSE 0417: Information and Communication Technology" or "GCE AS & A Level 9709: Mathematics"
            subject_code = None
            subject_title = None
            qual = "IGCSE"
            for line in lines[:8]:
                m = re.search(r'(IGCSE|GCE AS & A Level)\s+(\d{4})\s*:\s*(.*)', line, re.IGNORECASE)
                if m:
                    qual = "IGCSE" if m.group(1).upper() == "IGCSE" else "AS_A_LEVEL"
                    subject_code = m.group(2)
                    subject_title = m.group(3).strip().upper()
                    break

            if series and not global_series:
                global_series = series
            if qual == "AS_A_LEVEL":
                global_qual = "AS_A_LEVEL"

            # Parse candidates: find table headers: 'Option', 'Candidate No', 'Candidate Name', 'Status'
            # Subsequent lines are repeating blocks of 4 lines: option, cand_no, cand_name, status
            # e.g., 'DY', '5014', 'PRINCE GEHLOT', 'Complete'
            start_idx = -1
            for idx, line in enumerate(lines):
                if line == "Status" and idx > 0 and lines[idx-1] == "Candidate Name" and lines[idx-2] == "Candidate No":
                    start_idx = idx + 1
                    break

            if start_idx != -1 and subject_code:
                # Add to subjects_mapped
                if subject_code not in subjects_mapped:
                    subjects_mapped[subject_code] = {
                        "column": subject_code,
                        "header_name": subject_title if subject_title else f"Subject {subject_code}",
                        "subject_code": subject_code
                    }

                i = start_idx
                while i + 3 < len(lines):
                    opt = lines[i].strip()
                    c_no = lines[i+1].strip()
                    c_name = lines[i+2].strip()
                    status = lines[i+3].strip()

                    # Validate c_no is candidate number
                    if c_no.isdigit() and len(c_no) == 4 and status in ("Complete", "Pending", "Withdrawn"):
                        # Find if candidate already in candidates
                        existing_cand = None
                        for c in candidates:
                            if c["candidate_number"] == c_no:
                                existing_cand = c
                                break
                        
                        if not existing_cand:
                            existing_cand = {
                                "candidate_number": c_no,
                                "candidate_name": c_name.upper(),
                                "results": {}
                            }
                            candidates.append(existing_cand)
                        
                        # Add subject entry with dummy/registered grade or flag it as entry
                        existing_cand["results"][subject_code] = {
                            "grade": "ENTRY",
                            "pum": 0.0,
                            "raw_value": f"Registered ({opt})",
                            "subject_code": subject_code
                        }
                        i += 4
                    else:
                        i += 1
            continue

        cand_name = None
        cand_no = None
        series = None
        qual = "IGCSE"
        results = {}

        if is_provisional:
            cand_name, cand_no, series, qual, results = parse_provisional_page(lines)
        elif is_electronic:
            # Electronic format parsing (unchanged)
            cand_name = lines[1] if len(lines) > 1 else None
            cand_no = None
            for line in lines:
                m = re.search(r'[A-Z]{2}\d{3}\s*/\s*(\d+)', line)
                if m:
                    cand_no = m.group(1).zfill(4)
                    break
            series = None
            for line in lines:
                m = re.search(r'(March|June|November)\s+(\d{4})', line, re.IGNORECASE)
                if m:
                    series = {"month": m.group(1).capitalize(), "year": int(m.group(2))}
                    break
            qual = "IGCSE"
            for line in lines:
                u = line.upper()
                if ("GCE" in u or "AS & A LEVEL" in u) and "IGCSE" not in u:
                    qual = "AS_A_LEVEL"
                    break
 
            start_idx = -1
            for idx, line in enumerate(lines):
                if any(k in line for k in ["Percentage Uniform Mark", "Uniform Mark", "Result"]):
                    start_idx = idx + 1
                    break

            if start_idx != -1:
                i = start_idx
                while i < len(lines):
                    if any(x in lines[i] for x in ["This is an electronic", "THIS IS NOT", "Date Printed:"]):
                        break
                    # In electronic statements, each subject row is a block of 3 or 4 lines.
                    # Standard with PUM:
                    # [res_val] (e.g. B(b))
                    # [pum_val] (e.g. 71)
                    # [code_val] (e.g. 0455)
                    # [title_val] (e.g. ECONOMICS)
                    #
                    # Without PUM (e.g. English General Paper or Ungraded without mark):
                    # [res_val] (e.g. UNGRADED)
                    # [code_val] (e.g. 8021)
                    # [title_val] (e.g. ENGLISH GENERAL PAPER)
                    if i + 2 < len(lines):
                        first = lines[i]
                        second = lines[i+1]
                        third = lines[i+2]
                        
                        # Case 1: Standard with PUM (second line is digit, third line is 4-digit code)
                        if second.isdigit() and len(second) <= 3 and third.isdigit() and len(third) == 4 and i + 3 < len(lines):
                            res_val = first
                            pum_val = second
                            code_val = third
                            title_val = lines[i+3]
                            
                            clean_grade = res_val.split('(')[0].strip()
                            if clean_grade.lower() == "ungraded":
                                clean_grade = "U"
                            if clean_grade.upper() in ("PENDING", "PEND", "Q"):
                                clean_grade = "Q"
                                pum_val = "0"
                            elif clean_grade.upper() in ("NO RESULT", "NORESULT", "NO_RESULT", "X"):
                                clean_grade = "X"
                                pum_val = "0"
                            pum = float(pum_val)
                            results[code_val] = {
                                "grade": clean_grade,
                                "pum": pum,
                                "raw_value": f"{res_val} ({pum_val})",
                                "title": title_val.upper(),
                                "subject_code": code_val
                            }
                            i += 4
                            continue
                        
                        # Case 2: Without PUM (second line is 4-digit code)
                        elif second.isdigit() and len(second) == 4:
                            res_val = first
                            code_val = second
                            title_val = third
                            
                            clean_grade = res_val.split('(')[0].strip()
                            if clean_grade.lower() == "ungraded":
                                clean_grade = "U"
                            if clean_grade.upper() in ("PENDING", "PEND", "Q"):
                                clean_grade = "Q"
                            elif clean_grade.upper() in ("NO RESULT", "NORESULT", "NO_RESULT", "X"):
                                clean_grade = "X"
                            # Map standard PUM fallback using midpoint ranges
                            pum = get_midpoint_pum(clean_grade, qual, code_val)
                            
                            results[code_val] = {
                                "grade": clean_grade,
                                "pum": pum,
                                "raw_value": res_val,
                                "title": title_val.upper(),
                                "subject_code": code_val
                            }
                            i += 3
                            continue
                    i += 1
        elif is_broadsheet:
            # Parse Centre Broadsheet format
            # Centre No., Centre Name, Qualification, Session, Cand. No, Candidate name, etc.
            # Page layout starts with Centre/broadsheet details in lines 0 to 17
            # Find Session (e.g. November 2025)
            series = None
            for line in lines[:25]:
                m = re.search(r'(March|June|November)\s+(\d{4})', line, re.IGNORECASE)
                if m:
                    series = {"month": m.group(1).capitalize(), "year": int(m.group(2))}
                    break

            # Find Qualification
            qual = "IGCSE"
            for line in lines[:25]:
                u = line.upper()
                if "GCE" in u or "AS & A LEVEL" in u or "AS LEVEL" in u or "A LEVEL" in u:
                    qual = "AS_A_LEVEL"
                    break

            # Find subject header names
            # Start headers collection from "Candidate name" (or "Candidate Name") index up to Centre Name/Centre Details
            cand_name_idx = -1
            for idx, line in enumerate(lines[:20]):
                if "candidate name" in line.lower():
                    cand_name_idx = idx
                    break

            subject_headers = []
            if cand_name_idx != -1:
                # Subjects are between "Candidate name" and Centre details (Lucky International School, etc.)
                # E.g. Candidate name at 7, then BIOLOGY, BUSINESS, CHEMISTRY, ECONOMICS, ENGLISH GENERAL PAPER, INFORMATION TECHNOLOGY, PHYSICS.
                # Then Centre Details: Lucky International School at 15
                j = cand_name_idx + 1
                while j < len(lines):
                    if lines[j].upper() in ("LUCKY INTERNATIONAL SCHOOL", "CENTRE NAME", "IN016"):
                        break
                    if any(x in lines[j] for x in ["GCE", "IGCSE", "Session", "Qualification", "November", "March", "June"]):
                        break
                    subject_headers.append(lines[j])
                    j += 1

            # Map headers to subject codes
            subject_codes = []
            fallback_map = {
                'BUSINESS STUDIES': '0450', 'BUSINESS': '9609',
                'COMBINED SCIENCE': '0653', 'CO-ORD SCIENCES (DOUBLE AWARD)': '0654',
                'ENGLISH AS A SECOND LANGUAGE': '0510', 'ENGLISH AS A SECOND': '0510',
                'LANGUAGE': '0510', 'HINDI AS A SECOND LANGUAGE': '0549',
                'INFORMATION AND COMMUNICATION': '0417', 'INFORMATION': '0417',
                'COMMUNICATION': '0417', 'INFORMATION TECHNOLOGY': '9626',
                'MATHEMATICS': '0580', 'MATHEMATICS (W/OUT COURSEWORK)': '0580',
                'MATHEMATICS ADVANCED': '9709', 'PURE MATHEMATICS': '9709', 'MATHEMATICS (9709)': '9709',
                'PHYSICS': '0625', 'CHEMISTRY': '0620', 'BIOLOGY': '0610',
                'COMPUTER SCIENCE': '0478', 'ECONOMICS': '0455', 'ENGLISH GENERAL PAPER': '8021',
                'PSYCHOLOGY': '9990', 'ART AND DESIGN': '0400', 'ENVIRONMENTAL MANAGEMENT': '0680',
                'ACCOUNTING': '0452'
            }

            # Map the clean subject headers to their codes
            # Handle split/wrapped headers: if "ENGLISH AS A SECOND" and next is "LANGUAGE", join them
            clean_headers = []
            k = 0
            while k < len(subject_headers):
                h = subject_headers[k].strip()
                if h == "ENGLISH AS A SECOND" and k + 1 < len(subject_headers) and subject_headers[k+1].strip() == "LANGUAGE":
                    h = "ENGLISH AS A SECOND LANGUAGE"
                    k += 1
                elif h == "INFORMATION AND" and k + 1 < len(subject_headers) and subject_headers[k+1].strip() == "COMMUNICATION":
                    h = "INFORMATION AND COMMUNICATION"
                    k += 1
                clean_headers.append(h)
                k += 1

            for h in clean_headers:
                code = fallback_map.get(h.upper(), None)
                if not code:
                    # check if header contains digits
                    m = re.search(r'\b(\d{4})\b', h)
                    if m:
                        code = m.group(1)
                if not code:
                    # Look up from the database directly using sqlite3
                    import sqlite3
                    import os
                    # Locate database path
                    db_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../database/database.sqlite'))
                    if os.path.exists(db_path):
                        try:
                            conn = sqlite3.connect(db_path)
                            cursor = conn.cursor()
                            # Try exact matches
                            cursor.execute("SELECT subject_code FROM subjects WHERE UPPER(subject_name) = ? LIMIT 1", (h.upper(),))
                            row = cursor.fetchone()
                            if row:
                                code = row[0]
                            else:
                                # Try partial matches
                                cursor.execute("SELECT subject_code FROM subjects WHERE UPPER(subject_name) LIKE ? LIMIT 1", (f"%{h.upper()}%",))
                                row = cursor.fetchone()
                                if row:
                                    code = row[0]
                            conn.close()
                        except Exception:
                            pass
                if code:
                    subject_codes.append((h, code))

            # Candidates start after the details block. Let's find first candidate number (4 digits, numeric)
            # Find index where candidate numbers and grades are
            # Let's parse all candidates sequentially
            results = {}
            # Loop through lines to find 4-digit candidate numbers followed by candidate name
            i = 0
            while i < len(lines):
                line = lines[i].strip()
                if line.isdigit() and len(line) == 4:
                    # Confirm next line is candidate name (not a number, not a grade)
                    if i + 1 < len(lines):
                        next_line = lines[i+1].strip()
                        if not next_line.isdigit() and not is_grade(next_line) and not next_line.endswith('^'):
                            cand_no = line
                            cand_name = next_line
                            
                            # Gather results for this candidate
                            cand_results = {}
                            # The subsequent lines contain the grades for the mapped subjects
                            # They can be grades, or U, or absent, or double grades, or suffixed with ^ for AS Level (e.g. e^)
                            grades_found = []
                            j = i + 2
                            # Retrieve grades up to clean_headers count
                            while j < len(lines) and len(grades_found) < len(clean_headers):
                                val = lines[j].strip()
                                # Stop if we hit next candidate number
                                if val.isdigit() and len(val) == 4 and j + 1 < len(lines) and not lines[j+1].strip().isdigit() and not is_grade(lines[j+1].strip()) and not lines[j+1].strip().endswith('^'):
                                    break
                                if any(x in val for x in ["Indicates an AS", "Electronic Results", "LUCKY INTERNATIONAL"]):
                                    break
                                grades_found.append(val)
                                j += 1

                            # Map grades_found to subject_codes
                            for idx, s_info in enumerate(subject_codes):
                                if idx < len(grades_found):
                                    g_val = grades_found[idx]
                                    # Clean up e^ or c^ AS Level indicator
                                    clean_g = re.sub(r'\^', '', g_val).strip().upper()
                                    if clean_g == "UNGRADED":
                                        clean_g = "U"
                                    if clean_g in ("PENDING", "PEND", "Q"):
                                        clean_g = "Q"
                                    elif clean_g in ("NO RESULT", "NORESULT", "NO_RESULT", "X"):
                                        clean_g = "X"
                                    # Map standard PUM fallback using midpoint ranges
                                    pum = get_midpoint_pum(clean_g, qual, s_info[1])

                                    cand_results[s_info[1]] = {
                                        "grade": clean_g,
                                        "pum": pum,
                                        "raw_value": g_val,
                                        "title": s_info[0].upper(),
                                        "subject_code": s_info[1]
                                    }

                            # Skip this candidate's lines
                            # Register subjects mapped
                            for code, r_val in cand_results.items():
                                if code not in subjects_mapped:
                                    sub_qual = "IGCSE"
                                    if len(code) == 4 and code[0] in ('9', '8'):
                                        sub_qual = "AS_A_LEVEL"
                                    subjects_mapped[code] = {
                                        "column": code,
                                        "header_name": r_val["title"],
                                        "subject_code": code,
                                        "qualification": sub_qual
                                    }

                            candidates.append({
                                "candidate_number": cand_no,
                                "candidate_name": cand_name.upper().strip(),
                                "results": {k: {kk: vv for kk, vv in v.items() if kk != 'title'} for k, v in cand_results.items()}
                            })
                            i = j - 1
                i += 1
        else:
            continue  # Unknown format

        if not cand_no:
            continue

        if series and not global_series:
            global_series = series
        if qual and qual != "IGCSE":
            global_qual = qual

        # Register subject mappings from results
        for code, res in results.items():
            if code not in subjects_mapped:
                # Determine qualification type for this subject based on code
                # Cambridge codes starting with 9 or 8 or 9xxx/8xxx/97xx are typically A Level.
                # IGCSE codes start with 0 (e.g. 0455, 0580, 0653).
                sub_qual = "IGCSE"
                if len(code) == 4 and code[0] in ('9', '8'):
                    sub_qual = "AS_A_LEVEL"

                subjects_mapped[code] = {
                    "column": code,
                    "header_name": res.get("title", code),
                    "subject_code": code,
                    "qualification": sub_qual
                }

        candidates.append({
            "candidate_number": cand_no,
            "candidate_name": cand_name.upper().strip() if cand_name else f"Candidate {cand_no}",
            "results": {k: {kk: vv for kk, vv in v.items() if kk != 'title'} for k, v in results.items()}
        })

    doc.close()

    # Extract raw text for fallback AI mapping
    raw_text_dump = ""
    try:
        doc = fitz.open(file_path)
        for page in doc:
            raw_text_dump += page.get_text() + "\n--- PAGE BREAK ---\n"
        doc.close()
    except Exception:
        pass

    # Determine dominant qualification or default
    return {
        "series": global_series,
        "qualification": global_qual,
        "ai_used": True,
        "model_name": "PDF Statement Parser",
        "headers": {},
        "subjects_mapped": subjects_mapped,
        "candidates": candidates,
        "raw_text": raw_text_dump.strip()
    }


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)

    pdf_path = sys.argv[1]
    result = parse_pdf(pdf_path)
    print(json.dumps(result))
