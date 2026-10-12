import { ACTIONS, EXT_ID, waitForElement } from "./util";

const CONFIG = {
  SELECTORS: {
    ACTION_BUTTON: '[data-summarizer]',
    TIPTAP_EDITOR: '.tiptap.ProseMirror',
    FORM_FILE_NAME: 'form.file_name',
    GENERATE_REPORT_BUTTON: '[data-generate-report]'
  }
} as const;

// State management
const state = {
  actionBtnEl: null as HTMLButtonElement | null,
  genReportBtnEl: null as HTMLButtonElement | null,
  isBindingAction: false,
  isBindingReport: false,
};

function main() {
  console.log('Script initialized');
  registerListener();

  // Register and observe Action Button
  initButtonBinding({
    selector: CONFIG.SELECTORS.ACTION_BUTTON,
    getEl: () => state.actionBtnEl,
    setEl: (el) => { state.actionBtnEl = el; },
    isBindingRef: () => state.isBindingAction,
    setBindingRef: (val) => { state.isBindingAction = val; },
    onBind: (btn) => {
      btn.addEventListener('click', async () => {
        try {
          await triggerSummarization(btn);
        } catch (err) {
          console.error("Summarization process failed:", err);
        }
      });
    }
  });

  // Register and observe Generate Report Button
  initButtonBinding({
    selector: CONFIG.SELECTORS.GENERATE_REPORT_BUTTON,
    getEl: () => state.genReportBtnEl,
    setEl: (el) => { state.genReportBtnEl = el; },
    isBindingRef: () => state.isBindingReport,
    setBindingRef: (val) => { state.isBindingReport = val; },
    onBind: (btn) => {
      btn.addEventListener('click', () => {
        const actionId = crypto.randomUUID();
        chrome.runtime.sendMessage(EXT_ID, {
          id: actionId,
          action: ACTIONS.PAPS_START,
          executiveSummary: btn.dataset.executiveSummary,
        });
      });
    }
  });
}

// ============================================================================
// 1. Extension Messaging
// ============================================================================

function registerListener() {
  chrome.runtime.onMessage.addListener((message, _, sendResponse) => {
    console.log('Message received:', message);

    if (message.action === ACTIONS.FINISH) {
      handleFinishAction()
        .then(() => sendResponse({ status: 'success', action: ACTIONS.FINISH }))
        .catch((err) => {
          console.error('Failed to handle completion:', err);
          sendResponse({ status: 'error', message: err.message });
        });
      return true;
    }

    if (message.action === ACTIONS.PAPS_COMPLETE) {
      sendReport()
        .then(() => console.log('report sent'))
        .catch((e) => console.error('failed to send report', e));
      sendResponse(true);
      return true;
    }

    console.warn('Unknown message received:', message);
  });
}

async function handleFinishAction(): Promise<boolean> {
  console.log('Received completion signal from extension background.');
  const { notebookOutput = '', deepseekOutput = '' } = await chrome.storage.local.get(['notebookOutput']);
  const combinedOutput = `${notebookOutput}\n\n${deepseekOutput}`.trim();
  pasteRawMarkdown(combinedOutput);
  return true;
}

// ============================================================================
// 2. DOM Observer & Reusable Button Binder
// ============================================================================

interface ButtonBindingOptions {
  selector: string;
  getEl: () => HTMLButtonElement | null;
  setEl: (el: HTMLButtonElement | null) => void;
  isBindingRef: () => boolean;
  setBindingRef: (val: boolean) => void;
  onBind: (btn: HTMLButtonElement) => void;
}

function initButtonBinding(options: ButtonBindingOptions) {
  const bindFn = async () => {
    if (options.isBindingRef()) return;
    options.setBindingRef(true);

    try {
      const btn = await waitForElement(options.selector) as HTMLButtonElement | null;
      const currentEl = options.getEl();
      if (!btn || currentEl === btn) return;

      options.setEl(btn);
      console.log(`Button bound successfully for selector: ${options.selector}`);
      options.onBind(btn);
    } catch {
      options.setEl(null);
      console.debug(`Button not present on current route for selector: ${options.selector}.`);
    } finally {
      options.setBindingRef(false);
    }
  };

  bindFn().finally(() => {
    observeChanges(bindFn, () => {
      const el = options.getEl();
      if (el && !document.body.contains(el)) {
        options.setEl(null);
        return true;
      }
      return false;
    });
  });
}

function observeChanges(action: () => void, shouldRebind?: () => boolean) {
  let lastUrl = location.href;

  const observer = new MutationObserver(() => {
    const currentUrl = location.href;
    if (currentUrl !== lastUrl) {
      lastUrl = currentUrl;
      console.log("URL changed to:", currentUrl);
      action();
      return;
    }

    if (shouldRebind && shouldRebind()) {
      action();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });
  window.addEventListener('popstate', action);
}

// ============================================================================
// 3. Payload & Actions
// ============================================================================

async function triggerSummarization(button: HTMLButtonElement) {
  console.log('Starting summarizer...');

  const filePath = button.dataset?.filePath;
  if (!filePath) throw new Error("No file path specified on data-file-path attribute");

  console.log("Downloading file from:", filePath);
  const fileData = await downloadFile(filePath);
  const fileName = extractFileName(filePath);
  const actionId = crypto.randomUUID();

  const payload = {
    id: actionId,
    action: ACTIONS.START,
    reportFile: {
      path: filePath,
      name: fileName,
      dataUrl: fileData.dataUrl,
      mimeType: fileData.mimeType,
    },
    feedback1: button.dataset.questionLikeTheMost,
    feedback2: button.dataset.questionFutureCourses
  };

  chrome.runtime.sendMessage(EXT_ID, payload, (response) => {
    if (chrome.runtime.lastError) {
      console.error("Extension communication error:", chrome.runtime.lastError.message);
      return;
    }
    console.log("Extension response:", response);
    console.log('Summarizer started');
  });
}

function extractFileName(filePath: string): string {
  const formInput = document.getElementById(CONFIG.SELECTORS.FORM_FILE_NAME) as HTMLInputElement | null;
  if (formInput?.value) return formInput.value;

  const fallbackName = filePath.split('/').pop()?.split('?')[0];
  return fallbackName || "document.pdf";
}

async function downloadFile(url: string) {
  const response = await fetch(url);
  if (!response.ok) throw new Error(`HTTP error fetching file! status: ${response.status}`);

  const blob = await response.blob();
  const mimeType = blob.type || 'application/pdf';

  return new Promise<{ dataUrl: string; mimeType: string }>((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => resolve({ dataUrl: reader.result as string, mimeType });
    reader.onerror = reject;
    reader.readAsDataURL(blob);
  });
}

function pasteRawMarkdown(markdownText: string) {
  const editorEl = document.querySelector(CONFIG.SELECTORS.TIPTAP_EDITOR) as HTMLElement | null;
  if (!editorEl) {
    console.error('TipTap element not found.');
    return;
  }

  editorEl.focus();

  const dataTransfer = new DataTransfer();
  dataTransfer.setData('text/plain', markdownText);

  const pasteEvent = new ClipboardEvent('paste', {
    clipboardData: dataTransfer,
    bubbles: true,
    cancelable: true,
    composed: true
  });

  editorEl.dispatchEvent(pasteEvent);
}

async function sendReport() {
  const { generatedReport } = await chrome.storage.local.get('generatedReport');
  window.dispatchEvent(new CustomEvent('CX_SUMMARIZER_COMMAND', {
    detail: {
      action: 'REPORT',
      generatedReport,
    }
  }));
}

main();