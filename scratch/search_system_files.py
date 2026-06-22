import os
import glob

search_paths = [
    r"C:\Users\HP11\Downloads",
    r"C:\Users\HP11\Documents",
    r"C:\Users\HP11\Desktop"
]

print("Searching for component files...")
for path in search_paths:
    if not os.path.exists(path):
        continue
    print(f"\nScanning: {path}")
    # Search recursively or top-level for xls, xlsx, csv
    for root, dirs, files in os.walk(path):
        # limit depth to 2 to avoid scanning too many files
        depth = root[len(path):].count(os.sep)
        if depth > 2:
            continue
        for f in files:
            f_lower = f.lower()
            if any(k in f_lower for k in ("component", "mark", "0510", "english")) and f_lower.endswith((".xls", ".xlsx", ".csv")):
                print(f"Found: {os.path.join(root, f)}")
