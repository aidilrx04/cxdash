import { ACTIONS, EXT_ID, waitForElement, waitForElements } from "./util";

/**
 * Configuration & Constant Rules
 */
const CONFIG = {
  PROMPTS: {
    LIKE_SUMMARY:
      'Based on this feedback, summarize what the participants like from this course in a single paragraph. Response must be in Markdown format. Emphasis important keywords. Do not response with additional information, only the paragraph. Do not add header',
    FUTURE_COURSES:
      'Based on this feedback, summarize the course that participants would like to attend in a single paragraph. Response must be in Markdown format. Emphasis important keywords. Do not response with additional information, only the paragraph. Do not add header',
  },
  TIMEOUTS: {
    ELEMENT_WAIT: 10000,
    CLIPBOARD_WAIT: 100,
  },
  SELECTORS: {
    TEXTAREA: 'textarea',
    BUTTONS: '[role="button"]',
    COPY_BUTTON: 'div[role="button"]:has(path[d^="M6.14929"])',
  },
};

function setInputValue(textarea: HTMLTextAreaElement, value: string) {
  /** @ts-ignore-line */
  const nativeSetter = Object.getOwnPropertyDescriptor(
    window.HTMLTextAreaElement.prototype,
    'value'
  ).set;

  /** @ts-ignore-line */
  nativeSetter.call(textarea, value);
  textarea.dispatchEvent(new Event('input', { bubbles: true }));
  textarea.dispatchEvent(new Event('change', { bubbles: true }));
  textarea.focus();
}

/**
 * Simulates pressing the Enter key on a target element.
 */
function dispatchEnterKey(element: Element) {
  const enterEventInit = {
    key: 'Enter',
    code: 'Enter',
    keyCode: 13,
    which: 13,
    bubbles: true,
    cancelable: true,
    shiftKey: false,
  };

  ['keydown', 'keypress', 'keyup'].forEach((eventType) => {
    element.dispatchEvent(new KeyboardEvent(eventType, enterEventInit));
  });
}

/**
 * UI Builder Service
 */
const UIBuilder = {
  createContinueButton(onClickHandler: (this: HTMLButtonElement, ev: PointerEvent) => any) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = 'Continue';

    Object.assign(btn.style, {
      display: 'inline-block',
      marginRight: '8px',
      padding: '8px 16px',
      backgroundColor: '#10a37f',
      color: '#ffffff',
      border: 'none',
      borderRadius: '6px',
      fontSize: '14px',
      fontWeight: '600',
      cursor: 'pointer',
      boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
      transition: 'background-color 0.2s ease',
    });

    btn.addEventListener('click', onClickHandler);
    return btn;
  },

  injectButtonAtPosition(button: Element, targetContainer: Element, targetIndex = 2) {
    const referenceChild = targetContainer.children[targetIndex];
    if (referenceChild) {
      targetContainer.insertBefore(button, referenceChild);
    } else {
      targetContainer.appendChild(button);
    }
  },
};

/**
 * Main Workflow Execution
 */
(async function initWorkflow() {
  const store = await chrome.storage.local.get('id')

  if (!store.id) {
    console.log('[DEEPSEEK] Summarizer is not running.')
    return;
  }

  try {
    // 1. Fetch data from chrome storage
    const { feedback1 = '', feedback2 = '' } = await chrome.storage.local.get([
      'feedback1',
      'feedback2',
    ]);

    // 2. Locate and fill prompt in text area
    const textarea = await waitForElement(CONFIG.SELECTORS.TEXTAREA) as HTMLTextAreaElement;
    const fullText = `${feedback1}\n\n${CONFIG.PROMPTS.LIKE_SUMMARY}\n\n${feedback2}\n\n${CONFIG.PROMPTS.FUTURE_COURSES}`;

    setInputValue(textarea, fullText);
    dispatchEnterKey(textarea);

    // 3. Locate reference button container
    const buttons = await waitForElements(CONFIG.SELECTORS.BUTTONS);
    const submitBtn = buttons[buttons.length - 1];
    const targetContainer = submitBtn?.parentElement?.parentElement;

    if (!targetContainer) {
      throw new Error('Target button container grand-parent element not found.');
    }

    // 4. Build and inject custom UI button
    const continueBtn = UIBuilder.createContinueButton(async (event) => {
      event.preventDefault();
      event.stopPropagation();

      try {
        const copyBtns = await waitForElements(CONFIG.SELECTORS.COPY_BUTTON);
        const copyBtn = copyBtns[copyBtns.length - 1] as HTMLElement;

        copyBtn.click();
        await new Promise((resolve) =>
          setTimeout(resolve, CONFIG.TIMEOUTS.CLIPBOARD_WAIT)
        );

        const copiedText = await navigator.clipboard.readText();
        console.log('[Workflow] Captured generated output successfully.');

        await chrome.storage.local.set({ deepseekOutput: copiedText });
      } catch (err) {
        console.error('[Workflow] Failed reading clipboard or copying text:', err);
      } finally {
        chrome.runtime.sendMessage(EXT_ID, {
          action: ACTIONS.DEEPSEEK_COMPLETE,
        });
      }
    });

    UIBuilder.injectButtonAtPosition(continueBtn, targetContainer, 2);
  } catch (error) {
    console.error('[Workflow Initialization Failed]:', error);
  }
})();