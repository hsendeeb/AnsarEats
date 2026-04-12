import sys

path = "resources/views/profile/show.blade.php"
with open(path, "r", encoding="utf-8") as f:
    text = f.read()

# Replace the specific lines 10 to 76
lines = text.split("\n")
# find the line that starts the sidebar
start_idx = -1
for i, line in enumerate(lines):
    if '<div class="grid grid-cols-1 md:grid-cols-3 gap-8">' in line:
        start_idx = i
        break

end_idx = -1
for i in range(start_idx, len(lines)):
    if '<!-- Form Section -->' in lines[i]:
        end_idx = i
        break

if start_idx != -1 and end_idx != -1:
    # Delete the sidebar lines entirely, and fix the grid container to just be a flex or block container.
    # Replace the grid start
    lines[start_idx] = '    <div class="max-w-3xl">'
    
    # delete from start_idx + 1 up to end_idx + 1 (the form section and md:col-span-2)
    # Actually, end_idx is <!-- Form Section -->, and end_idx+1 is <div class="md:col-span-2">
    del lines[start_idx+1 : end_idx+2]
    
    # We should also remove one closing </div> for the grid container since we removed the md:col-span-2 container
    # wait, if I replace `<div class="grid...">` with `<div class="max-w-3xl mx-auto">`, it still has one closing tag.
    # But I removed `<div class="md:col-span-2">`. So I need to remove one closing `</div>` at the end.
    for i in range(len(lines)-1, -1, -1):
        if '</div>' in lines[i]:
            # find the first `</div>` from the bottom up to line 199 
            # The structure was:
            #     </div>
            # </div>
            # @endsection
            # I can just delete the second to last line.
            break

# The safer way is to just do it procedurally or visually
# Actually I'll just write a script that does precise modification
