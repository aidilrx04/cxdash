/**
 * Configuration & Constant Rules
 */
const CONFIG = {
  EXTENSION_ID: 'hjjclicidmkobencbdmlbnicdnpbcooo',
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

/**
 * DOM Utility Helpers
 */
const DOMUtils = {
  /**
   * Polls the DOM until element(s) matching the selector are found or times out.
   */
  waitForElements( selector, timeout = CONFIG.TIMEOUTS.ELEMENT_WAIT )
  {
    return new Promise( ( resolve, reject ) =>
    {
      const startTime = Date.now();
      const interval = setInterval( () =>
      {
        const elements = document.querySelectorAll( selector );
        if ( elements.length > 0 )
        {
          clearInterval( interval );
          resolve( Array.from( elements ) );
        } else if ( Date.now() - startTime > timeout )
        {
          clearInterval( interval );
          reject( new Error( `Timeout waiting for selector: "${selector}"` ) );
        }
      }, 300 );
    } );
  },

  /**
   * Helper to fetch a single element using waitForElements.
   */
  async waitForElement( selector, timeout )
  {
    const elements = await this.waitForElements( selector, timeout );
    return elements[ 0 ];
  },

  /**
   * Sets the value of a framework-managed (React/Vue/Svelte) textarea.
   */
  setInputValue( textarea, value )
  {
    const nativeSetter = Object.getOwnPropertyDescriptor(
      window.HTMLTextAreaElement.prototype,
      'value'
    ).set;

    nativeSetter.call( textarea, value );
    textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
    textarea.dispatchEvent( new Event( 'change', { bubbles: true } ) );
    textarea.focus();
  },

  /**
   * Simulates pressing the Enter key on a target element.
   */
  dispatchEnterKey( element )
  {
    const enterEventInit = {
      key: 'Enter',
      code: 'Enter',
      keyCode: 13,
      which: 13,
      bubbles: true,
      cancelable: true,
      shiftKey: false,
    };

    [ 'keydown', 'keypress', 'keyup' ].forEach( ( eventType ) =>
    {
      element.dispatchEvent( new KeyboardEvent( eventType, enterEventInit ) );
    } );
  },
};

/**
 * UI Builder Service
 */
const UIBuilder = {
  createContinueButton( onClickHandler )
  {
    const btn = document.createElement( 'button' );
    btn.type = 'button';
    btn.textContent = 'Continue';

    Object.assign( btn.style, {
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
    } );

    btn.addEventListener( 'click', onClickHandler );
    return btn;
  },

  injectButtonAtPosition( button, targetContainer, targetIndex = 2 )
  {
    const referenceChild = targetContainer.children[ targetIndex ];
    if ( referenceChild )
    {
      targetContainer.insertBefore( button, referenceChild );
    } else
    {
      targetContainer.appendChild( button );
    }
  },
};

/**
 * Main Workflow Execution
 */
( async function initWorkflow()
{
  try
  {
    // 1. Fetch data from chrome storage
    const { feedback1 = '', feedback2 = '' } = await chrome.storage.local.get( [
      'feedback1',
      'feedback2',
    ] );

    // 2. Locate and fill prompt in text area
    const textarea = await DOMUtils.waitForElement( CONFIG.SELECTORS.TEXTAREA );
    const fullText = `${feedback1}\n\n${CONFIG.PROMPTS.LIKE_SUMMARY}\n\n${feedback2}\n\n${CONFIG.PROMPTS.FUTURE_COURSES}`;

    DOMUtils.setInputValue( textarea, fullText );
    DOMUtils.dispatchEnterKey( textarea );

    // 3. Locate reference button container
    const buttons = await DOMUtils.waitForElements( CONFIG.SELECTORS.BUTTONS );
    const submitBtn = buttons[ buttons.length - 1 ];
    const targetContainer = submitBtn?.parentElement?.parentElement;

    if ( !targetContainer )
    {
      throw new Error( 'Target button container grand-parent element not found.' );
    }

    // 4. Build and inject custom UI button
    const continueBtn = UIBuilder.createContinueButton( async ( event ) =>
    {
      event.preventDefault();
      event.stopPropagation();

      try
      {
        const copyBtns = await DOMUtils.waitForElements( CONFIG.SELECTORS.COPY_BUTTON );
        const copyBtn = copyBtns[ copyBtns.length - 1 ];

        copyBtn.click();
        await new Promise( ( resolve ) =>
          setTimeout( resolve, CONFIG.TIMEOUTS.CLIPBOARD_WAIT )
        );

        const copiedText = await navigator.clipboard.readText();
        console.log( '[Workflow] Captured generated output successfully.' );

        await chrome.storage.local.set( { deepseekOutput: copiedText } );
      } catch ( err )
      {
        console.error( '[Workflow] Failed reading clipboard or copying text:', err );
      } finally
      {
        chrome.runtime.sendMessage( CONFIG.EXTENSION_ID, {
          action: 'DEEPSEEK_COMPLETE',
        } );
      }
    } );

    UIBuilder.injectButtonAtPosition( continueBtn, targetContainer, 2 );
  } catch ( error )
  {
    console.error( '[Workflow Initialization Failed]:', error );
  }
} )();