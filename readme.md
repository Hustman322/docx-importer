WP DOCX Import CLI

Converts DOCX → HTML (via Node.js + Mammoth)
Uses first <h1> as post title
Removes duplicate <h1> from content
Creates WordPress draft posts
Supports categories and tags
Auto-sets featured image if matching file exists
Batch import from folder

Import single file:
wp docx import /path/to/file.docx --category=1 --tags="news,import"

Import folder:
wp docx import-folder /path/to/folder --category=1 --tags="news,import"

If an image exists with the same filename as the DOCX:
file.docx → file.jpg / file.png / file.webp
it will be uploaded and set as the featured image.

Output:
Creates WordPress posts in draft status
Logs progress in WP-CLI
Skips broken files without stopping import
