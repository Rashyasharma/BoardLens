import os
import glob
import pandas as pd

folders = [
    r"C:\Users\HP11\Desktop\new",
    r"c:\Users\HP11\Desktop\My Projects\BoardLens"
]

print("Searching for Excel files...")
for folder in folders:
    if not os.path.exists(folder):
        continue
    print(f"\nFolder: {folder}")
    files = glob.glob(os.path.join(folder, "*.xls*"))
    for f in files:
        print(f"File: {os.path.basename(f)}")
        try:
            xl = pd.ExcelFile(f)
            print(f"  Sheets: {xl.sheet_names}")
        except Exception as e:
            print(f"  Error: {e}")
