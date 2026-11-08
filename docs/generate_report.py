"""Generate LAPORAN.html dari LAPORAN.md untuk dicetak menjadi PDF.

Jalankan dari root proyek:
    python docs/generate_report.py
"""

from pathlib import Path

import markdown

ROOT = Path(__file__).resolve().parent.parent
SOURCE = ROOT / "LAPORAN.md"
OUTPUT = ROOT / "LAPORAN.html"

CSS = """
@page {
    size: A4;
    margin: 17mm 15mm 18mm 15mm;
}

* { box-sizing: border-box; }

body {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    font-size: 10.5pt;
    line-height: 1.55;
    color: #1f2937;
    margin: 0;
    background: #ffffff;
}

h1 {
    font-size: 23pt;
    line-height: 1.25;
    color: #0f172a;
    text-align: center;
    margin: 30mm 0 6mm;
    letter-spacing: 0.3px;
}

h1 + h2 {
    page-break-before: avoid;
    text-align: center;
    border: none;
    color: #1d4ed8;
    font-size: 15pt;
    margin-top: 0;
}

h1 + h2 + h3 {
    text-align: center;
    border: none;
    color: #334155;
    font-size: 13pt;
    margin-top: 2mm;
}

h2 {
    page-break-before: always;
    font-size: 16pt;
    color: #0f172a;
    border-bottom: 2.5px solid #1d4ed8;
    padding-bottom: 2mm;
    margin: 0 0 5mm;
}

h3 {
    font-size: 12.5pt;
    color: #1e3a8a;
    margin: 6mm 0 2mm;
    page-break-after: avoid;
}

h4 {
    font-size: 11pt;
    color: #334155;
    margin: 4mm 0 1.5mm;
    page-break-after: avoid;
}

p { margin: 0 0 2.6mm; text-align: justify; }

ul, ol { margin: 0 0 3mm 5mm; padding-left: 4mm; }
li { margin-bottom: 1.2mm; }

a { color: #1d4ed8; text-decoration: none; }

table {
    width: 100%;
    border-collapse: collapse;
    margin: 3mm 0 5mm;
    font-size: 9.3pt;
    page-break-inside: avoid;
}

th, td {
    border: 1px solid #cbd5e1;
    padding: 1.6mm 2.2mm;
    vertical-align: top;
    text-align: left;
}

th {
    background: #e2e8f0;
    color: #0f172a;
    font-weight: 600;
}

tr:nth-child(even) td { background: #f8fafc; }

pre {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-left: 3px solid #1d4ed8;
    border-radius: 3px;
    padding: 2.5mm 3mm;
    font-family: "Cascadia Mono", Consolas, "Courier New", monospace;
    font-size: 8.2pt;
    line-height: 1.4;
    white-space: pre-wrap;
    page-break-inside: avoid;
    margin: 2.5mm 0 4mm;
}

code {
    font-family: "Cascadia Mono", Consolas, "Courier New", monospace;
    font-size: 9pt;
    background: #f1f5f9;
    padding: 0.3mm 1mm;
    border-radius: 2px;
}

pre code { background: none; padding: 0; font-size: inherit; }

blockquote {
    border-left: 3px solid #94a3b8;
    margin: 3mm 0;
    padding: 1mm 4mm;
    color: #475569;
    background: #f8fafc;
}

img {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 3mm auto 1.5mm;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    page-break-inside: avoid;
}

img + em, p > em:only-child {
    display: block;
    text-align: center;
    font-size: 9pt;
    color: #475569;
}

hr {
    border: none;
    border-top: 1px solid #cbd5e1;
    margin: 5mm 0;
}

strong { color: #0f172a; }
"""


def main() -> None:
    text = SOURCE.read_text(encoding="utf-8")
    body = markdown.markdown(
        text,
        extensions=[
            "tables",
            "fenced_code",
            "sane_lists",
            "attr_list",
            "md_in_html",
        ],
        output_format="html5",
    )

    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Praktikum Backend Development — Sistem Perpustakaan</title>
<style>{CSS}</style>
</head>
<body>
{body}
</body>
</html>
"""

    OUTPUT.write_text(html, encoding="utf-8")
    print(f"Berhasil menulis {OUTPUT}")


if __name__ == "__main__":
    main()
