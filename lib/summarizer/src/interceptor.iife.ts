function killAllIntervals() {
	// 1. Clear all active running intervals in the browser
	// (By incrementing through timer IDs and calling native clearInterval)
	const maxTimerId = window.setInterval(() => { }, 0);
	for (let i = 1; i <= maxTimerId; i++) {
		window.clearInterval(i);
	}

	// 2. Override standard window.setInterval
	(window as any)._originalSetInterval = window.setInterval.bind(window);
	(window as any)._originalClearInterval = window.clearInterval.bind(window);

	window.setInterval = function (...args: any[]): number {
		console.log('[Override] Blocked window.setInterval call');
		return 999999;
	} as typeof window.setInterval;

	window.clearInterval = function (id?: number): void {
		if (id === 999999) return;
		(window as any)._originalClearInterval(id);
	} as typeof window.clearInterval;

	// 3. Intercept Angular's Zone.js macroTask runner (if Zone.js is active)
	if ((window as any).Zone) {
		const originalScheduleTask = (window as any).Zone.prototype.scheduleTask;
		(window as any).Zone.prototype.scheduleTask = function (task: any) {
			if (task.type === 'macroTask' && task.source === 'setInterval') {
				console.log('[Override] Blocked Zone.js setInterval task');
				return task; // Prevent task from being scheduled
			}
			return originalScheduleTask.apply(this, arguments);
		};
	}
}

/**
 * 1. Main World Function: Clears CKEditor content via direct API access.
 * @param targetSelector - CSS selector to target the editor container or .ck-content
 */
export function clearCKEditorDirect(targetSelector: string = '.ck-content'): boolean {
	const editableEl = document.querySelector<HTMLElement>(targetSelector);

	if (!editableEl) {
		console.error('[MainWorld] CKEditor element not found:', targetSelector);
		return false;
	}

	// Resolve the editable element if container was passed
	const ckContentEl = editableEl.classList.contains('ck-content')
		? editableEl
		: editableEl.querySelector<HTMLElement>('.ck-content');

	// Retrieve instance attached directly to the DOM node or global Angular component
	const editor = (ckContentEl as any)?.ckeditorInstance ||
		(window as any).ng?.getComponent(ckContentEl || editableEl)?.editorInstance;

	if (editor && typeof editor.setData === 'function') {
		// Direct API call safely resets model, view, and Angular form controls
		editor.setData('');
		console.log('[MainWorld] CKEditor content cleared successfully via API.');
		return true;
	}

	console.warn('[MainWorld] Could not access ckeditorInstance on DOM element.');
	return false;
}

/**
 * 2. Main World Function: Listens for custom window events dispatched by the isolated content script.
 */
export function initIsolatedScriptListener(): void {
	window.addEventListener('CX_SUMMARIZER_COMMAND', ((event: CustomEvent) => {
		const { action, payload } = event.detail || {};

		console.log('[MainWorld] Received message from isolated script:', action);

		switch (action) {
			case 'CLEAR_EDITOR':
				clearCKEditorDirect(payload?.selector);
				break;

			case 'SET_EDITOR_HTML':
				// Optional payload handling to set new HTML
				const editableEl = document.querySelector<HTMLElement>(payload?.selector || '.ck-content');
				const editor = (editableEl as any)?.ckeditorInstance;
				if (editor && payload?.html) {
					editor.setData(payload.html);
				}
				break;

			default:
				console.warn('[MainWorld] Unknown action received:', action);
		}
	}) as EventListener);

	console.log('[MainWorld] Listener initialized for isolated script communication.');
}

// Auto-initialize listener when script runs in Main World
initIsolatedScriptListener();

killAllIntervals()