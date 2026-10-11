import { ACTIONS, EXT_ID, setInputValue, simulateHover, StorageUtils, waitForElement, waitForElements } from "./util";

// ==========================================
// 1. CONFIGURATION & CONSTANTS
// ==========================================
const config = {
  ALLOWED_FILES: [
    '0. GOAL Executive Summary Template.pdf',
    '0. Skill Executive Summary Template v3.pdf',
    'Skill Executive Summary Template v5.pdf'
  ],
  COMMAND_PROMPT: `generate an Executive Summary for this training report followed the template provided. Do not provide reference in the response. Do not put double/single quote. Response must be in Markdown format. For part Participants particularly praised, ignore satisfaction rating, just focus on any positive comment, may also refer to additional comments. for example

"Participants specifically praised aspects such as practical learning, hands-on examples, and specific modules like Power Pivot and the Pivot table."`,
  TIMEOUTS: {
    ELEMENT_WAIT: 10000,
    POPUP_WAIT: 5000,
    PROGRESS_RELOAD: 10000,
    STEP_TRANSITION: 10000,
    DOM_SETTLE: 500,
    DELETION_SETTLE: 2500,
    CLIPBOARD_WAIT: 100
  },
  SELECTORS: {
    ADD_SOURCE_BTN: 'button[aria-label="Add source"]',
    UPLOAD_TRIGGER: '[xapscottyuploadertrigger]',
    FILE_INPUT: 'input[type="file"]',
    CLOSE_POPUP_BTN: '[aria-label="Close"]',
    DROPZONE_SCROLL_AREA: '[xapscottyuploaderdropzone] .scroll-area-desktop',
    QUERY_BOX: '[aria-label="Query box"]',
    SUBMIT_BTN: '[type=submit]',
    COPY_BTN: '.xap-copy-to-clipboard',
    MORE_BTN: 'button[aria-label="More"]',
    MENU_CONTENT: '.mat-mdc-menu-content',
    DIALOG_FOOTER: '.dialog-footer',
    PROGRESS_BAR: '[role=progressbar]'
  }
};


// ==========================================
// 3. STEP HANDLERS
// ==========================================

/**
 * Step 1: Uploads the file stored in chrome.storage into NotebookLM
 */
async function handleFileUploadStep() {
  console.log('[Workflow] Running Step 1: File Upload');

  const fileData = await StorageUtils.getPendingFile();
  if (!fileData) {
    console.warn('[Workflow] No file found in storage to upload.');
    return;
  }

  // 1. Open "Add Source" Modal
  const addSourcesBtn = await waitForElement(config.SELECTORS.ADD_SOURCE_BTN) as HTMLButtonElement;
  addSourcesBtn.click();

  // 2. Trigger hover event to mount hidden <input type="file">
  const uploadTrigger = await waitForElement(config.SELECTORS.UPLOAD_TRIGGER);
  simulateHover(uploadTrigger);

  // 3. Attach file payload to input element
  const fileInput = await waitForElement(config.SELECTORS.FILE_INPUT) as HTMLInputElement;
  const dataTransfer = new DataTransfer();
  dataTransfer.items.add(fileData.fileObj);
  fileInput.files = dataTransfer.files;

  fileInput.dispatchEvent(new Event('input', { bubbles: true }));
  fileInput.dispatchEvent(new Event('change', { bubbles: true }));

  console.log('[Workflow] File attached. Scheduling transition to Step 2...');

  // 4. Update query parameters to step=2 and reload
  setTimeout(() => {
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.set('step', '2');
    window.location.search = searchParams.toString();
  }, config.TIMEOUTS.STEP_TRANSITION);
}

/**
 * Step 2: Executes prompt, injects action controls, and handles output workflow
 */
async function handleExecutionStep() {
  console.log('[Workflow] Running Step 2: Query Execution');

  const fileData = await StorageUtils.getPendingFile();
  const file = fileData?.raw;
  console.log('[Workflow] Target File:', file);

  // 1. Dismiss initial popup if present
  try {
    const closePopup = await waitForElement(config.SELECTORS.CLOSE_POPUP_BTN, config.TIMEOUTS.POPUP_WAIT) as HTMLButtonElement;
    closePopup.click();
    console.log('[Workflow] Popup dismissed');
  } catch (e) {
    console.log('[Workflow] No popup detected');
  }

  // 2. Locate file container in scroll area
  const scrollArea = await waitForElement(config.SELECTORS.DROPZONE_SCROLL_AREA);
  const allowedWithCurrent = [...config.ALLOWED_FILES, file?.name];
  let targetFileContainer = null;

  for (const child of scrollArea.children) {
    const listedFileName = child.children[2]?.textContent?.trim();

    if (!allowedWithCurrent.includes(listedFileName)) {
      console.warn(`[Workflow] File "${listedFileName}" is not in allowed list.`);
    }

    if (listedFileName === file?.name) {
      targetFileContainer = child;
    }
  }

  if (!targetFileContainer) {
    throw new Error(`Target file container for "${file?.name}" was not found.`);
  }

  // 3. Check if file is still processing
  const progressBar = targetFileContainer.querySelector(config.SELECTORS.PROGRESS_BAR);
  if (progressBar) {
    console.log('[Workflow] File is still processing. Reloading in 10s...');
    setTimeout(() => location.reload(), config.TIMEOUTS.PROGRESS_RELOAD);
    return;
  }

  // 4. Populate Query Box and Submit
  const queryBox = await waitForElement(config.SELECTORS.QUERY_BOX) as HTMLInputElement;
  setInputValue(queryBox, config.COMMAND_PROMPT);

  const enterEvent = new KeyboardEvent('keydown', {
    key: 'Enter',
    code: 'Enter',
    keyCode: 13,
    which: 13,
    bubbles: true,
    cancelable: true
  });
  queryBox.dispatchEvent(enterEvent)

  // const submitBtn = await waitForElement(config.SELECTORS.SUBMIT_BTN) as HTMLButtonElement;
  // submitBtn.click();

  const submitContainer = await waitForElement('.bottom-right-container') as HTMLDivElement

  // 5. Inject custom "Continue" trigger button
  injectContinueButton(submitContainer);
}

// ==========================================
// 4. AUXILIARY PIPELINE ACTIONS
// ==========================================

/**
 * Injects a custom "Continue" button above the submit container
 */
function injectContinueButton(container: HTMLElement) {
  container.style.position = 'relative';

  const continueBtn = document.createElement('button');
  continueBtn.type = 'button';
  continueBtn.textContent = 'Continue';

  Object.assign(continueBtn.style, {
    display: 'block',
    marginLeft: '10px',
    marginRight: '10px',
    padding: '10px 16px',
    backgroundColor: '#10a37f',
    color: '#ffffff',
    border: 'none',
    borderRadius: '6px',
    fontSize: '14px',
    fontWeight: '600',
    cursor: 'pointer',
    boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
    transition: 'background-color 0.2s ease'
  });

  continueBtn.addEventListener('click', (event) => {
    event.preventDefault();
    finishOrchestration();
  });

  // if (submitBtn.parentElement && submitBtn.parentElement.children[0]) {
  //   submitBtn.parentElement.children[0].after(continueBtn);
  // }

  container.insertBefore(continueBtn, container.children[0],)
}

/**
 * Finalization pipeline: copies output, cleans non-allowed files, notifies extension
 */
async function finishOrchestration() {
  console.log('[Workflow] Finishing orchestration pipeline...');
  await copyGeneratedOutput();
  await removeUnallowedSources();

  chrome.runtime.sendMessage(EXT_ID, {
    action: ACTIONS.NOTEBOOK_COMPLETE
  });
}

/**
 * Copies generated result to clipboard and stores in storage
 */
async function copyGeneratedOutput() {
  const copyBtns = await waitForElements(config.SELECTORS.COPY_BTN);
  const copyBtn = copyBtns[copyBtns.length - 1];
  if (!copyBtn) throw new Error("Copy button not found");

  (copyBtn as HTMLButtonElement).click();
  await new Promise(resolve => setTimeout(resolve, config.TIMEOUTS.CLIPBOARD_WAIT));

  try {
    const copiedText = await navigator.clipboard.readText();
    console.log('[Workflow] Captured generated output successfully.');

    await chrome.storage.local.set({ notebookOutput: copiedText });
  } catch (err) {
    console.error('[Workflow] Failed to read text from clipboard:', err);
  }
}

/**
 * Cleans up non-allowed uploaded sources from the sidebar
 */
async function removeUnallowedSources() {
  const scrollArea = await waitForElement(config.SELECTORS.DROPZONE_SCROLL_AREA);

  while (true) {
    const currentItems = Array.from(scrollArea.children);

    const itemToDelete = currentItems.find(item => {
      const fileName = item.children[2]?.textContent?.trim();
      return fileName && !config.ALLOWED_FILES.includes(fileName);
    });

    if (!itemToDelete) {
      console.log('[Workflow] All non-allowed files removed successfully.');
      break;
    }

    const fileName = itemToDelete.children[2].textContent.trim();
    console.log(`[Workflow] Deleting source: ${fileName}`);

    try {
      const moreBtn = itemToDelete.children[3]?.querySelector(config.SELECTORS.MORE_BTN) as HTMLInputElement;
      if (!moreBtn) throw new Error('More button not found');
      moreBtn.click();

      const menuContent = await waitForElement(config.SELECTORS.MENU_CONTENT);
      (menuContent.children[0]?.children[0] as HTMLInputElement)?.click();

      await new Promise(resolve => setTimeout(resolve, config.TIMEOUTS.DOM_SETTLE));

      const dialogFooter = await waitForElement(config.SELECTORS.DIALOG_FOOTER);
      (dialogFooter.children[1] as HTMLInputElement)?.click();

      await new Promise(resolve => setTimeout(resolve, config.TIMEOUTS.DELETION_SETTLE));
    } catch (err) {
      console.error(`[Workflow] Error deleting file "${fileName}":`, err);
      break;
    }
  }
}

async function main() {
  const store = await chrome.storage.local.get('id')

  if (!store.id) {
    console.log('[NOTEBOOK] Summarizer is not running.')
    return;
  }

  console.log('[NOTEBOOK] Running summarizer')

  const params = new URLSearchParams(window.location.search)
  const step = params.get('step')

  if (step && step == '2') {
    await handleExecutionStep()
  }
  else {
    await handleFileUploadStep()
  }
}

main();
// (async function init() {
//   const params = new URLSearchParams(window.location.search);
//   const step = params.get('step');

//   if (step && step === '2') {
//     await handleExecutionStep();
//   } else {
//     await handleFileUploadStep();
//   }
// })();