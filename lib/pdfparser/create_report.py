import os
import argparse
import sys
# import pypdf
import pymupdf

FILE_DIR = os.path.dirname(__file__)
FONT = os.path.join(FILE_DIR, 'dejavu-sans.bold.ttf')

def main():
	generated_report_path = sys.argv[1]
	completed_report_path = sys.argv[2]
	output_path = sys.argv[3]

	gen_report = pymupdf.open(generated_report_path)
	complete_report = pymupdf.open(completed_report_path)
	page = gen_report[2]

	create_execsum_page(page)

	final_report = merge_execsum_page(complete_report, gen_report)

	metadata = final_report.metadata
	metadata['title'] = "PEOPLEAPS - Report"

	final_report.set_metadata(metadata)
	final_report.save(output_path)

	print(output_path)


def create_execsum_page(page: pymupdf.Page) -> pymupdf.Page:
	# --- 1. Executive Summary Redaction & Replacement ---
	rect = pymupdf.Rect(50, 80, 150, 100)
	
	# Add redaction annotation with white fill to clear out text underneath
	page.add_redact_annot(rect, fill=(1, 1, 1))
	page.apply_redactions()  # Permanently removes text and fills the area

	# Insert new text on top
	page.insert_font(fontname="dejavu-sans", fontfile=FONT)
	p = pymupdf.Point(56.7, 95.65)
	page.insert_text(p,
						"EXECUTIVE SUMMARY\n",
						fontsize=9.5,
						fontname='dejavu-sans',
						color=(8 / 255, 72 / 255, 113 / 255))

	# --- 2. Page Number Redaction & Replacement ---
	r = 3
	page_num_rect = pymupdf.Rect(536 - r, 805.5 - r, 533 + r * 2, 810.5 + r * 2)
	
	page.add_redact_annot(page_num_rect, fill=(1, 1, 1))
	page.apply_redactions()

	page.insert_text(pymupdf.Point(533, 810.5),
						"1",
						fontname="helv",
						fontsize=9,
						color=(0, 0, 0))

	return page

def merge_execsum_page(complete: pymupdf.Document, gen: pymupdf.Document) -> pymupdf.Document:
	final = pymupdf.open()
	final.insert_pdf(complete, from_page=0,to_page=0)
	final.insert_pdf(gen,from_page=2,to_page=2)
	final.insert_pdf(complete,from_page=1,to_page=complete.page_count)

	return final

if __name__ == '__main__':
	main()
	exit(0)