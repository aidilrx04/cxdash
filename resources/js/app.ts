// PDFParse.setWorker('https://cdn.jsdelivr.net/npm/pdf-parse@latest/dist/pdf-parse/web/pdf.worker.mjs');


window.addEventListener('CX_SUMMARIZER_COMMAND', ((event: CustomEvent) => {
	const { action, generatedReport } = event.detail || {}
	if (action === 'REPORT') {
		// console.log('report received')

		// console.log(generatedReport)

		// renderReport(generatedReport)
		saveReport(generatedReport)

		return;
	}

	console.warn('unknown action received')
}) as EventListener)


// async function renderReport(report?: string) {
// 	// console.log(report)
// 	if (!report) return;

// 	const container = document.querySelector('[data-report-viewer] .fi-section-content');
// 	if (!container) return;

// 	// Clear previous content
// 	container.innerHTML = '';

// 	// Ensure proper data URL format for base64 string input
// 	const pdfSource = report.startsWith('data:')
// 		? report
// 		: `data:application/pdf;base64,${report}`;

// 	// Convert all pages of the PDF to images
// 	const images = await pdfToImg(pdfSource, {
// 		pages: 'all',
// 		imgType: 'png',
// 		scale: 1.5,
// 	});

// 	// Normalize single string return to an array if necessary
// 	const pageImages = Array.isArray(images) ? images : [images];

// 	// Insert rendered images into container
// 	pageImages.forEach((imgSrc, index) => {
// 		if (index === 2 || index === 3) {
// 			const img = document.createElement('img');
// 			img.src = imgSrc;
// 			img.style.display = 'block';
// 			img.style.width = '100%';
// 			img.style.height = 'auto';
// 			img.style.marginBottom = '16px';

// 			container.appendChild(img);
// 		}
// 	});
// }

// function base64ToArrayBuffer(base64: string) {
// 	var binaryString = atob(base64);
// 	var bytes = new Uint8Array(binaryString.length);
// 	for (var i = 0; i < binaryString.length; i++) {
// 		bytes[i] = binaryString.charCodeAt(i);
// 	}
// 	return bytes.buffer;
// }

// async function modifyReport(report?: string) {
// 	if (!report) return

// 	const bytes = base64ToArrayBuffer(report)

// 	const doc = await PDFDocument.load(bytes)
// 	// console.log(doc)
// 	const pages = doc.getPages()
// 	const execPage = pages[2]

// 	if (!execPage) return

// 	execPage.drawSquare({
// 		x: 533,
// 		y: 28,
// 		size: 13,
// 		color: rgb(1, 1, 1)
// 	})

// 	execPage.drawText('1', {
// 		x: 533,
// 		y: 31,
// 		size: 10,
// 	})

// 	const saved = await doc.saveAsBase64()
// 	return saved;
// }

async function saveReport(report?: string) {
	if (!report) return;

	try {
		// 1. Convert base64 string to Uint8Array
		const byteCharacters = atob(report);
		const byteNumbers = new Array(byteCharacters.length);
		for (let i = 0; i < byteCharacters.length; i++) {
			byteNumbers[i] = byteCharacters.charCodeAt(i);
		}
		const byteArray = new Uint8Array(byteNumbers);

		// 2. Create the File object
		const pdfFile = new File([byteArray], 'report.pdf', { type: 'application/pdf' });

		const reportIdEl = (document.querySelector('#cx-generate-report') as HTMLButtonElement)

		const reportId = reportIdEl.dataset.id

		if (!reportId) throw new Error('no report id detected')

		// 3. Append to FormData
		const formData = new FormData();
		formData.append('report', pdfFile);
		formData.append('id', reportId)

		// 4. Send the request
		const res = await fetch('/api/submit-report', {
			method: 'POST',
			// DO NOT set 'Content-Type': 'application/www-urlencoded' or 'multipart/form-data'.
			// Letting fetch handle headers automatically allows it to append the required multipart boundary.
			body: formData
		});


		if (!res.ok) {
			throw new Error(`Server returned status ${res.status}`);
		}

		const data = await res.json()

		const downloadEl = document.createElement('a')
		downloadEl.href = data.downloadPath
		downloadEl.setAttribute('download', 'result.pdf')
		downloadEl.target = '_blank'

		document.body.appendChild(downloadEl)

		downloadEl.click()

	} catch (e) {
		console.error(e);
	}
}

// const modi = await modifyReport(localStorage.getItem('testreport')!)


// renderReport(modi)
document.addEventListener('DOMContentLoaded', () => {
	// saveReport(localStorage.getItem('testreport')!)
})