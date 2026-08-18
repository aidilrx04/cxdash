const CONFIG = {
  EXTENSION_ID: 'hjjclicidmkobencbdmlbnicdnpbcooo',
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

const DOMUtils = {
  /**
   * Polls the DOM until an element matching the selector is found or times out.
   */
  waitForElement( selector, timeout = CONFIG.TIMEOUTS.ELEMENT_WAIT )
  {
    return new Promise( ( resolve, reject ) =>
    {
      const startTime = Date.now();
      const interval = setInterval( () =>
      {
        const el = document.querySelector( selector );
        if ( el )
        {
          clearInterval( interval );
          resolve( el );
        } else if ( Date.now() - startTime > timeout )
        {
          clearInterval( interval );
          reject( new Error( `Timeout waiting for selector: "${selector}"` ) );
        }
      }, 500 );
    } );
  },
  waitForElements( selector, timeout = CONFIG.TIMEOUTS.ELEMENT_WAIT )
  {
    return new Promise( ( resolve, reject ) =>
    {
      const startTime = Date.now();
      const interval = setInterval( () =>
      {
        const els = document.querySelectorAll( selector );
        if ( els.length > 0 )
        {
          clearInterval( interval );
          resolve( els );
        } else if ( Date.now() - startTime > timeout )
        {
          clearInterval( interval );
          reject( new Error( `Timeout waiting for selector: "${selector}"` ) );
        }
      }, 500 );
    } );
  },

  /**
   * Simulates full mouse and pointer hover events to trigger lazy-loaded inputs.
   */
  simulateHover( element )
  {
    const opts = { bubbles: true, cancelable: true, view: window };
    [ 'mouseover', 'mouseenter', 'mousemove' ].forEach( type =>
      element.dispatchEvent( new MouseEvent( type, opts ) )
    );
    [ 'pointerover', 'pointerenter', 'pointermove' ].forEach( type =>
      element.dispatchEvent( new PointerEvent( type, opts ) )
    );
  },

  /**
   * Updates an input/textarea value and fires proper framework events.
   */
  setInputValue( element, value )
  {
    element.value = value;
    element.focus();
    element.dispatchEvent( new Event( 'input', { bubbles: true } ) );
    element.dispatchEvent( new Event( 'change', { bubbles: true } ) );
  }
};

( async function ()
{
  const feedback1 = ( await chrome.storage.local.get( [ 'feedback1' ] ) ).feedback1;
  const feedback2 = ( await chrome.storage.local.get( [ 'feedback2' ] ) ).feedback2;

  // console.log( feedback1, 'balls', feedback2 );

  const PROMPT1 = 'Based on this feedback, summarize what the participants like from this course in a single paragraph. Response must be in Markdown format. Emphasis important keywords. Do not response with additional information, only the paragraph. Do not add header';
  const PROMPT2 = 'Based on this feedback, summarize the course that participants would like to attend in a single paragraph. Response must be in Markdown format. Emphasis important keywords. Do not response with additional information, only the paragraph. Do not add header';

  const textarea = await DOMUtils.waitForElement( 'textarea' );

  // 1. Update text using Native Setter to ensure framework state updates
  const nativeTextareaValueSetter = Object.getOwnPropertyDescriptor(
    window.HTMLTextAreaElement.prototype,
    'value'
  ).set;

  const fullText = `${feedback1}\n\n${PROMPT1}\n\n${feedback2}\n\n${PROMPT2}`;
  nativeTextareaValueSetter.call( textarea, fullText );

  // 2. Dispatch input events so the UI notices the text change
  textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
  textarea.dispatchEvent( new Event( 'change', { bubbles: true } ) );

  // 3. Focus the textarea
  textarea.focus();

  // 4. Create and dispatch the Enter Key Event sequence
  const enterEventInit = {
    key: 'Enter',
    code: 'Enter',
    keyCode: 13,
    which: 13,
    bubbles: true,
    cancelable: true,
    shiftKey: false // Set to true if you meant Shift+Enter
  };

  textarea.dispatchEvent( new KeyboardEvent( 'keydown', enterEventInit ) );
  textarea.dispatchEvent( new KeyboardEvent( 'keypress', enterEventInit ) );
  textarea.dispatchEvent( new KeyboardEvent( 'keyup', enterEventInit ) );


  const btns = await DOMUtils.waitForElements( '[role=button]' );
  const submitBtn = btns[ btns.length - 1 ];

  // 1. Create the "Continue" button element
  const continueBtn = document.createElement( 'button' );
  continueBtn.type = 'button';
  continueBtn.textContent = 'Continue';

  // 2. Apply custom styling
  Object.assign( continueBtn.style, {
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
    transition: 'background-color 0.2s ease'
  } );

  // 3. Attach click listener
  continueBtn.addEventListener( 'click', async ( event ) =>
  {
    event.preventDefault();
    event.stopPropagation();
    const copyBtns = await DOMUtils.waitForElements( 'div[role="button"]:has(path[d^="M6.14929"])' );
    const copyBtn = copyBtns[ copyBtns.length - 1 ];

    copyBtn.click();
    await new Promise( resolve => setTimeout( resolve, CONFIG.TIMEOUTS.CLIPBOARD_WAIT ) );

    try
    {
      const copiedText = await navigator.clipboard.readText();
      console.log( '[Workflow] Captured generated output successfully.' );

      await chrome.storage.local.set( { deepseekOutput: copiedText } );
    } catch ( err )
    {
      console.error( '[Workflow] Failed to read text from clipboard:', err );
    }

    chrome.runtime.sendMessage( CONFIG.EXTENSION_ID, {
      action: 'DEEPSEEK_COMPLETE'
    } );
  } );

  // 4. Insert as 3rd child of submitBtn's grandparent
  const grandParent = submitBtn.parentElement?.parentElement;

  if ( grandParent )
  {
    const thirdChild = grandParent.children[ 2 ];

    if ( thirdChild )
    {
      // If a 3rd child already exists, insert before it (moving existing 3rd child to 4th)
      grandParent.insertBefore( continueBtn, thirdChild );
    } else
    {
      // If grandparent has fewer than 3 children, append to the end
      grandParent.appendChild( continueBtn );
    }
  }
} )();