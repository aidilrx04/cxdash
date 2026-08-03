import os
import re
import pdfplumber
import json
import argparse

regexs = {
    "program_title": r"Program Title\s+(.*?)(?=\s+Client|\n|$)",
    "client_name": r"Client\s+(.*?)(?=\s+Assessment|\n|$)",
    "trainer_name": r"Pre\s*-\s*Test\s+Post\s*-\s*Test\s+Variance\n\s*([A-Za-z\s\.\,\'\-]+?)(?=\s+\d{2}[-/]\d{2}[-/]\d{4}|\n|$)",
    "total_participant": r"Total\s+Participants?\s+(\d+)",
    "total_evaluation": r"Total\s+Evaluations?\s+(?:Received\s+)?(\d+)",
    "overall_satisfaction": r"Overall\s+Satisfaction\s*[:\-]?\s*([\d\.]+%?)",
    "status": r"Status\s*[:\-]?\s*([A-Za-z\s]+?)(?=\n|$)",
    "pss_score": r"Participant\s+Sentiment\s+Score\s*[:\-]?\s*([\d\.]+)",
}

def parse_pdf(file_path):
    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File not found: {file_path}")

    texts = ""
    with pdfplumber.open(file_path) as pdf:
        for page in pdf.pages:
            text = page.extract_text()
            if text:
                texts += text + "\n"

    # Extract information using regexes
    information = {}
    for key, pattern in regexs.items():
        match = re.search(pattern, texts)
        if match:
            information[key] = match.group(1).strip()
        else:
            information[key] = None

    return information

def main():
    default_file = os.path.join(os.path.dirname(__file__), 'sample2.pdf')

    parser = argparse.ArgumentParser(description="Extract training report data from a PDF file.")
    parser.add_argument("file_path", nargs="?", default=default_file, help="Path to the PDF file")
    
    args = parser.parse_args()

    information = parse_pdf(args.file_path)
    print(json.dumps(information, indent=4))
    return information

if __name__ == "__main__":
    main()