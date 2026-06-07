libs = ['pypdf', 'pdfplumber', 'fitz', 'pdfminer', 'openpyxl', 'pandas']
for lib in libs:
    try:
        __import__(lib)
        print(f"Library {lib} is installed.")
    except ImportError:
        print(f"Library {lib} is NOT installed.")
