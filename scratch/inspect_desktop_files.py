import os
import glob

desktop = r"C:\Users\HP11\Desktop"
print(f"Listing files in {desktop}:")
for f in glob.glob(os.path.join(desktop, "*")):
    if os.path.isdir(f):
        print(f"Dir: {os.path.basename(f)}")
        # List files inside directory if it's "new" or similar
        if os.path.basename(f).lower() in ("new", "raw", "assets", "imports", "downloads"):
            for sub in glob.glob(os.path.join(f, "*")):
                print(f"  File: {os.path.basename(sub)}")
    else:
        print(f"File: {os.path.basename(f)}")
