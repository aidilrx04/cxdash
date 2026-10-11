export const EXT_ID = chrome.runtime.id;

export enum ACTIONS {
	START = 1,
	NOTEBOOK_START,
	NOTEBOOK_COMPLETE,
	DEEPSEEK_START,
	DEEPSEEK_COMPLETE,
	PAPS_START,
	PAPS_COMPLETE,
	FINISH
}

export enum STATUS {
	PENDING = 1,
	RUNNING,
	FAILED,
	COMPLETED,
}

export const CONFIG = {
	TIMEOUTS: {
		ELEMENT_WAIT: 10000,
	}
}

export interface PendingFilePayload {
	name: string;
	dataUrl: string;
	mimeType: string;
	path: string;
}

export interface ReconstructedFile {
	raw: PendingFilePayload;
	fileObj: File;
}

export async function waitForElements(selector: string, timeout = CONFIG.TIMEOUTS.ELEMENT_WAIT) {
	return new Promise<Element[]>((resolve, reject) => {
		const startTime = Date.now();
		const interval = setInterval(() => {
			const elements = document.querySelectorAll(selector);
			if (elements.length > 0) {
				clearInterval(interval);
				resolve(Array.from(elements));
			} else if (Date.now() - startTime > timeout) {
				clearInterval(interval);
				reject(new Error(`Timeout waiting for selector: "${selector}"`));
			}
		}, 300);
	});
}

/**
 * Helper to fetch a single element using waitForElements.
 */
export async function waitForElement(selector: string, timeout?: number) {
	const elements = await waitForElements(selector, timeout);
	return elements[0];
}

export async function wait(ms: number) {
	await (new Promise((resolve, reject) => setTimeout(resolve, ms)))
}

/**
  * Simulates full mouse and pointer hover events to trigger lazy-loaded inputs.
  */
export function simulateHover(element: Element) {
	const opts = { bubbles: true, cancelable: true, view: window };
	['mouseover', 'mouseenter', 'mousemove'].forEach(type =>
		element.dispatchEvent(new MouseEvent(type, opts))
	);
	['pointerover', 'pointerenter', 'pointermove'].forEach(type =>
		element.dispatchEvent(new PointerEvent(type, opts))
	);
}

/**
  * Updates an input/textarea value and fires proper framework events.
  */
export function setInputValue(element: HTMLInputElement, value: string) {
	element.value = value;
	element.focus();
	element.dispatchEvent(new Event('input', { bubbles: true }));
	element.dispatchEvent(new Event('change', { bubbles: true }));
}


export const StorageUtils = {
	/**
	 * Retrieves pending file from chrome.storage and reconstructs a File object.
	 */
	async getPendingFile(): Promise<ReconstructedFile | null> {
		const store = await chrome.storage.local.get(['reportFile']);
		const pendingFile = store.reportFile as PendingFilePayload | undefined;

		if (!pendingFile) return null;

		const { name, dataUrl, mimeType, path } = pendingFile;
		const response = await fetch(dataUrl);
		const blob = await response.blob();

		return {
			raw: pendingFile,
			fileObj: new File([blob], name, { type: mimeType })
		};
	}
};