import { ACTIONS, EXT_ID, waitForElement } from "./util";

let actionBtnEl: HTMLButtonElement | null = null;
let isBinding = false; // Prevents concurrent binding calls

const config = {
  SELECTORS: {
    ACTION_BUTTON: '[data-summarizer]',
    TIPTAP_EDITOR: '.tiptap.ProseMirror',
    FORM_FILE_NAME: 'form.file_name',
    GENERATE_REPORT_BUTTON: '[data-generate-report]'
  }
} as const;

function main() {
  console.log('Script initialized');
  registerListener();
  registerActionButton();
  bindGenerateReportButton()
}

// ============================================================================
// 1. Extension Messaging
// ============================================================================

function registerListener() {
  chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
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
        .then(() => {
          console.log('report sent')
        })
        .catch((e) => {
          console.error('failed to send report', e)
        });
      sendResponse(true)
      return true
    }

    console.warn('Unknown message received:', message);
  });
}

async function handleFinishAction(): Promise<boolean> {
  console.log('Received completion signal from extension background.');

  const { notebookOutput = '', deepseekOutput = '' } = await chrome.storage.local.get([
    'notebookOutput',
    // 'deepseekOutput'
  ]);

  const combinedOutput = `${notebookOutput}\n\n${deepseekOutput}`.trim();
  pasteRawMarkdown(combinedOutput);

  return true;
}

// ============================================================================
// 2. DOM Observer & Listener Binding
// ============================================================================

function registerActionButton() {
  bindActionButton().finally(() => {
    observeChanges();
  });
}

function observeChanges() {
  let lastUrl = location.href;

  const observer = new MutationObserver(() => {
    const currentUrl = location.href;

    if (currentUrl !== lastUrl) {
      lastUrl = currentUrl;
      console.log("URL changed to:", currentUrl);
      bindActionButton();
      return;
    }

    // FIX 2: Only attempt re-bind if button existed before but was removed from DOM by Livewire
    if (actionBtnEl && !document.body.contains(actionBtnEl)) {
      actionBtnEl = null;
      bindActionButton();
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });
  window.addEventListener('popstate', () => bindActionButton());
}

async function bindActionButton() {
  if (isBinding) return;
  isBinding = true;

  try {
    const summarizerBtn = await waitForElement(config.SELECTORS.ACTION_BUTTON) as HTMLButtonElement | null;
    if (!summarizerBtn || actionBtnEl === summarizerBtn) return;

    actionBtnEl = summarizerBtn;
    console.log('Action button bound successfully');

    actionBtnEl.addEventListener('click', async () => {
      try {
        await triggerSummarization(actionBtnEl!);
      } catch (err) {
        console.error("Summarization process failed:", err);
      }
    });
  } catch (e) {
    actionBtnEl = null;
    // Log gracefully instead of throwing to prevent unhandled promise rejections
    console.debug('Action button not present on current route.');
  } finally {
    isBinding = false;
  }
}

// ============================================================================
// 3. Payload & Actions
// ============================================================================

async function triggerSummarization(button: HTMLButtonElement) {
  console.log('Starting summarizer...')

  const filePath = button.dataset?.filePath;
  if (!filePath) throw new Error("No file path specified on data-file-path attribute");

  console.log("Downloading file from:", filePath);
  const fileData = await downloadFile(filePath);
  const fileName = extractFileName(filePath);

  const actionId = crypto.randomUUID()

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

  // console.log(payload)

  chrome.runtime.sendMessage(EXT_ID, payload, (response) => {
    if (chrome.runtime.lastError) {
      console.error("Extension communication error:", chrome.runtime.lastError.message);
      return;
    }
    console.log("Extension response:", response);
    console.log('Summarizer started')
  });
}

function extractFileName(filePath: string): string {
  const formInput = document.getElementById(config.SELECTORS.FORM_FILE_NAME) as HTMLInputElement | null;
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
  const editorEl = document.querySelector(config.SELECTORS.TIPTAP_EDITOR) as HTMLElement | null;
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

async function bindGenerateReportButton() {
  const btn = await waitForElement(config.SELECTORS.GENERATE_REPORT_BUTTON) as HTMLButtonElement

  btn.addEventListener('click', () => {
    chrome.runtime.sendMessage(EXT_ID, {
      action: ACTIONS.PAPS_START,
      executiveSummary: btn.dataset.executiveSummary,
    })
  })

  console.log('generate report button binded')

}

async function sendReport() {
  const { generatedReport } = await chrome.storage.local.get('generatedReport')
  window.dispatchEvent(new CustomEvent('CX_SUMMARIZER_COMMAND', {
    detail: {
      action: 'REPORT',
      generatedReport,
    }
  }))
}

main();