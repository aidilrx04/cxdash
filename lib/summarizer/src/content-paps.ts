import { ACTIONS, EXT_ID, wait, waitForElement, waitForElements } from './util';

const LOGIN_HASH = '#/sessions/signin'
const DASHBOARD_HASH = '#/administrator/dashboard'
const REPORT_HASH = '#/administrator/training-session/1/group/1/detail/report'

const PAPS_EMAIL_ADDRESS = import.meta.env.VITE_EMAIL_ADDRESS;
const PAPS_PASSWORD = import.meta.env.VITE_PASSWORD;

async function main() {
  console.log('CXSummarizer init')

  // TODO: check action before doing anything
  // const { action } = await chrome.storage.local.get('action')

  await login()
  await goToReport();
  await openReportSettingTab()
  await fillFormFields()
  await openTrainerFeedbackTab()
  await updateReport()
  await downloadReport();
  await finish()
}


async function login() {
  if (window.location.hash !== LOGIN_HASH) {
    console.log('logged in already probably')
    return
  }

  const emailAddressEl = await waitForElement('#email[formcontrolname="username"]') as HTMLInputElement
  const passwordEl = await waitForElement('#password[formcontrolname="password"]') as HTMLInputElement
  const submitBtnEl = await waitForElement('[type="submit"]') as HTMLInputElement

  console.log(emailAddressEl, passwordEl, submitBtnEl)

  setInputValue(emailAddressEl, PAPS_EMAIL_ADDRESS);
  setInputValue(passwordEl, PAPS_PASSWORD);

  await wait(1500)
  submitBtnEl.click()

  // Polling loop to check if the hash changes to #/administrator/dashboard
  const targetHash = DASHBOARD_HASH;
  const timeoutMs = 10000;
  const intervalMs = 500;
  const startTime = Date.now();

  while (Date.now() - startTime < timeoutMs) {
    if (window.location.hash.includes(targetHash)) {
      return true;
    }
    await wait(intervalMs);
  }

  return false;
}

function setInputValue(element: HTMLInputElement, value: string) {
  element.focus();

  // Use the native setter prototype to trigger Angular/React property trackers
  const valueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value')?.set;
  if (valueSetter) {
    valueSetter.call(element, value);
  } else {
    element.value = value;
  }

  // Dispatch standard DOM events to notify Reactive Forms
  element.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
  element.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
  element.dispatchEvent(new Event('blur', { bubbles: true, cancelable: true }));
}

async function goToReport() {
  window.location.hash = REPORT_HASH;
  window.dispatchEvent(new HashChangeEvent('hashchange'));
}

async function openReportSettingTab() {
  const tabsEls = await waitForElements('a.nav-link[role="tab"]')
  const reportSettingTab = tabsEls[tabsEls.length - 1] as HTMLElement

  reportSettingTab.click()
  // console.log(reportSettingTab)
}

function findElementByText<T extends HTMLElement>(
  selector: string,
  text: string
): T | null {
  const elements = Array.from(document.querySelectorAll<T>(selector));
  const normalizedTarget = text.trim().toLowerCase();

  return elements.find(el => {
    // Check both innerText (rendered) and textContent (raw)
    const content = (el.innerText || el.textContent || '').trim().toLowerCase();
    return content === normalizedTarget || content.includes(normalizedTarget);
  }) || null;
}

async function fillFormFields() {
  const editorEl = await waitForElement('.ck-content[contenteditable="true"]') as HTMLElement
  if (!editorEl) {
    console.error('[CXSummarizer] Target CKEditor .ck-content element not found.');
    return false;
  }

  const executiveSummary = await chrome.storage.local.get('executiveSummary')

  const textToPaste = executiveSummary.executiveSummary as string
  // 1. Focus the editor so insertion happens at current cursor position
  await wait(1500)
  clearCKEditorContent(editorEl)
  pasteHtmlToCKEditor(editorEl, textToPaste)
}

export function pasteHtmlToCKEditor(editorEl: HTMLElement, htmlString: string): boolean {
  // 1. Focus the editor so insertion happens at current cursor position
  editorEl.focus();

  // 2. Prepare DataTransfer with HTML payload
  const dataTransfer = new DataTransfer();
  dataTransfer.setData('text/html', htmlString);

  // Optional plain text fallback (strips tags for non-HTML listeners)
  const plainTextFallback = htmlString.replace(/<[^>]*>/g, '');
  dataTransfer.setData('text/plain', plainTextFallback);

  // 3. Dispatch simulated ClipboardEvent
  const pasteEvent = new ClipboardEvent('paste', {
    clipboardData: dataTransfer,
    bubbles: true,
    cancelable: true,
  });

  const dispatched = editorEl.dispatchEvent(pasteEvent);

  // 4. Trigger DOM input event to ensure change tracking fires
  editorEl.dispatchEvent(new Event('input', { bubbles: true }));

  return dispatched;
}

/**
 * Clears all content from the target CKEditor 5 instance.
 * @param ckEditorEl - The `.ck-content` editable element or root CKEditor wrapper.
 */
export async function clearCKEditorContent(ckEditorEl: HTMLElement): Promise<boolean> {
  requestClearCKEditor()

  return false;
}

function requestClearCKEditor(selector: string = '.ck-content') {
  window.dispatchEvent(
    new CustomEvent('CX_SUMMARIZER_COMMAND', {
      detail: {
        action: 'CLEAR_EDITOR',
        payload: { selector }
      }
    })
  );
}

async function openTrainerFeedbackTab() {
  const trainerFeedbackTabEl = await waitForElement('#ngb-nav-9') as HTMLAnchorElement

  trainerFeedbackTabEl.click()
}

async function updateReport() {
  const updateReportButtonEl = await waitForElement('form .text-left [type=submit') as HTMLInputElement

  updateReportButtonEl.click()

  return await new Promise((resolve, reject) => {
    let intervalId = setInterval(() => {
      console.log('updating...')
      // console.log(updateReportButtonEl.textContent, updateReportButtonEl.textContent === 'Update Report')
      if (updateReportButtonEl.textContent !== 'Updating') {
        console.log('updated')
        clearInterval(intervalId)
        resolve(true)
      }
    }, 500)
  })
}

async function downloadReport() {
  const rawToken = localStorage.getItem('token');
  const token = rawToken ? JSON.parse(rawToken) : null;
  const endpoint = 'https://pgtraining-api.peopleaps.com/api/administrator/report/master/generate/1';
  try {
    // 1. Send POST request directly
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Accept': 'application/pdf',
        // Pass Authorization header if required by backend API:
        'Authorization': `Bearer ${token}`
      },
      // Pass form payload if required by the button form state
    });

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    // 2. Consume as ArrayBuffer
    const arrayBuffer = await response.arrayBuffer();

    // 3. Convert ArrayBuffer to Base64 String
    const base64String = arrayBufferToBase64(arrayBuffer);

    // 4. Save to chrome.storage.local
    await chrome.storage.local.set({ generatedReport: base64String });
    console.log('Report successfully saved to chrome.storage.local!');

  } catch (error) {
    console.error('Failed to download and store report:', error);
  }
}
function arrayBufferToBase64(buffer: ArrayBuffer): string {
  let binary = '';
  const bytes = new Uint8Array(buffer);
  const len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}

async function finish() {
  chrome.runtime.sendMessage(EXT_ID, {
    action: ACTIONS.PAPS_COMPLETE
  })
}
main();