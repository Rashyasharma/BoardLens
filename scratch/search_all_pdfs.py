import os

workspace = r"c:\Users\HP11\Desktop\My Projects\BoardLens"
print("Scanning for PDF/Excel files in workspace...")
for root, dirs, files in os.walk(workspace):
    if "node_modules" in dirs:
        dirs.remove("node_modules")
    if "vendor" in dirs:
        dirs.remove("vendor")
    for f in files:
        if f.lower().endswith((".pdf", ".xlsx", ".xls", ".csv")):
            print(f"File: {os.path.join(root, f)}")
