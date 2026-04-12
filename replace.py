import sys

path = "resources/views/layouts/app.blade.php"
with open(path, "r", encoding="utf-8") as f:
    lines = f.readlines()

# find footer boundaries
start_idx = -1
end_idx = -1
for i, line in enumerate(lines):
    if "@unless (trim($__env->yieldContent('hideFooter')))" in line:
        start_idx = i
    if "@endunless" in line and start_idx != -1 and i > start_idx and i - start_idx < 100:
        end_idx = i
        break

if start_idx != -1 and end_idx != -1:
    del lines[start_idx:end_idx+1]
    with open(path, "w", encoding="utf-8") as f:
        f.writelines(lines)
    print(f"Deleted footer from line {start_idx+1} to {end_idx+1}")
else:
    print("Could not find footer boundaries")
