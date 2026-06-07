import fitz
import re

doc = fitz.open(r"C:\Users\HP11\Desktop\Electronic Statements of Results for March 2026.pdf")
print(f"Total pages: {len(doc)}")

for page_idx, page in enumerate(doc):
    text = page.get_text()
    lines = [line.strip() for line in text.split('\n') if line.strip()]
    
    # Extract candidate name (usually 2nd line, after "Electronic Statement of Results")
    cand_name = ""
    if len(lines) > 1:
        cand_name = lines[1]
        
    # Find Centre / Cand No
    cand_no = ""
    for line in lines:
        if "/" in line and any(char.isdigit() for char in line):
            # Check if it has the IN016/XXXX format
            m = re.search(r'([A-Z0-9]+)\s*/\s*(\d+)', line)
            if m:
                cand_no = m.group(2)
                break
                
    # Find series
    series_match = None
    for line in lines:
        m = re.search(r'(March|June|November)\s+(\d{4})', line, re.IGNORECASE)
        if m:
            series_match = m.group(0)
            break
            
    # Find qualification
    qual = "IGCSE"
    for line in lines:
        if "GCE" in line or "AS & A Level" in line:
            qual = "AS_A_LEVEL"
            break
            
    # Find results section
    # Let's look for "Percentage Uniform Mark" or similar table headers
    start_idx = -1
    for idx, line in enumerate(lines):
        if "Percentage Uniform Mark" in line:
            start_idx = idx + 1
            break
            
    results = []
    if start_idx != -1:
        # Loop through the remaining lines to parse result blocks
        i = start_idx
        while i < len(lines):
            if "This is an electronic statement" in lines[i] or "THIS IS NOT" in lines[i] or "Date Printed:" in lines[i]:
                break
            
            # Pattern check: standard Cambridge grade format, e.g. A*(a*), A(a), B(b), a(as), etc., or just grade
            # followed by numeric PUM, followed by 4-digit code
            if i + 3 < len(lines):
                res_val = lines[i]
                pum_val = lines[i+1]
                code_val = lines[i+2]
                title_val = lines[i+3]
                
                # Verify code is 4 digit
                if code_val.isdigit() and len(code_val) == 4 and (pum_val.isdigit() or pum_val == 'N/A' or pum_val == '-'):
                    results.append({
                        'subject_code': code_val,
                        'subject_title': title_val,
                        'grade': res_val,
                        'pum': pum_val
                    })
                    i += 4
                    continue
            i += 1
            
    print(f"Page {page_idx}: Name: {cand_name}, No: {cand_no}, Series: {series_match}, Qual: {qual}, Results: {results}")

doc.close()
