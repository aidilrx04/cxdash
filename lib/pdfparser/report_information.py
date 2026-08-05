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

GENERAL_QUESTIONS = [
    'What did you like most about the course?',
    'What would you change about this course?',
    'The courses you would probably like to attend.',
    'Additional comments'
]

EVALUATION_HEADERS = [
    ['A. Content', 'Rating', None, None, None, None, 'Total'],
    ['B. Trainer', 'Rating', None, None, None, None, 'Total'],
    ['C. Organiser', 'Rating', None, None, None, None, 'Total']
]

tables = []

def get_participants(tables: list):
    participants = []
    for table in tables:
        if table[0][0] != 'No' and table[0][1] != 'Trainee\'s Name':
            continue

        for participant in table[2:]:
            participants.append(normalize_multiline(participant[1]))
    return participants

def get_general_feedbacks(tables: list):
    feedbacks = {}

    for table in tables:
        # print(table)
        header = table[0][0].strip()
        if header in GENERAL_QUESTIONS:
            feedbacks[header] = []

            for row in table[1:]:
                response = {
                    'name': normalize_multiline(row[1]),
                    'response':row[2]
                }
                feedbacks[header].append(response)
    
    return feedbacks

def header_is_same(header1:list,header2:list)->bool:
    # print(header1,header2)
    if len(header1) != len(header2):
        return False
    for i in range(len(header1)):
        col_header_1 = header1[i]
        col_header_2 = header2[i]

        if col_header_1 != col_header_2:
            return False

    return True

def sanitize_tables(tables:list)->list:
    new_tables = []
    prev_header = []
    for table in tables:
        if header_is_same(table[0],prev_header):
            # todo: can find actual content here by comparing potential multi header rows against each other
            # print('table is prv', table[0])
            table = table[1:]
            new_tables[len(new_tables)-1] += table
        else:
            prev_header = table[0]
            new_tables.append(table)
    return new_tables

def get_evaluation_feedbacks(tables:list):
    feedbacks = {}
    for table in tables:
        for header in EVALUATION_HEADERS:
            if header_is_same(table[0], header):
                feedbacks[header[0]] = {}
                for row in table[2:]:
                    feedbacks[header[0]][row[0]] = [
                        int(row[1]),
                        int(row[2]),
                        int(row[3]),
                        int(row[4]),
                        int(row[5]),
                    ]
    return feedbacks

def normalize_multiline(input:str)->str:
    return input.replace('\n',' ')

def parse_pdf(file_path):
    information = {}

    
    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File not found: {file_path}")

    texts = ""
    tables = []
    with pdfplumber.open(file_path) as pdf:
        for page in pdf.pages:
            text = page.extract_text()
            extracted_tables = page.extract_tables()
            # print(text)
            if text:
                texts += text + "\n"
            if extracted_tables:
                for table in extracted_tables:
                    tables.append(table)

    tables = sanitize_tables(tables)
    # print(tables)
    # return
    participants = get_participants(tables)
    # print(tables)
    general_feedbacks = get_general_feedbacks(tables)
    evaluation_feedbacks = get_evaluation_feedbacks(tables)
    # return

    information['participants'] = participants
    information['general_feedbacks'] = general_feedbacks
    information['evaluation_feedbacks'] = evaluation_feedbacks

    # Extract information using regexes
    for key, pattern in regexs.items():
        match = re.search(pattern, texts)
        if match:
            information[key] = match.group(1).strip()
        else:
            information[key] = None

    return information

def main():
    default_file = os.path.join(os.path.dirname(__file__), 'sample.pdf')
    default_output = os.path.join(os.path.dirname(__file__), 'result.json')

    parser = argparse.ArgumentParser(description="Extract training report data from a PDF file.")
    parser.add_argument("file_path", nargs="?", default=default_file, help="Path to the PDF file")
    parser.add_argument('output_path',nargs="?",default=default_output,help="Path to write the file")
    
    args = parser.parse_args()

    information = parse_pdf(args.file_path)
    with open(args.output_path,'w') as output:
        output.write(json.dumps(information))
    return information

if __name__ == "__main__":
    main()