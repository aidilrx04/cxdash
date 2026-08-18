/**
 * NotebookLM Automation Content Script
 */

// ==========================================
// 1. CONFIGURATION & CONSTANTS
// ==========================================
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

// ==========================================
// 2. UTILITY HELPERS
// ==========================================
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

const StorageUtils = {
  /**
   * Retrieves pending file from chrome.storage and reconstructs a File object.
   */
  async getPendingFile()
  {
    const store = await chrome.storage.local.get( [ 'pendingFile' ] );
    if ( !store.pendingFile ) return null;

    const { name, dataUrl, mimeType } = store.pendingFile;
    const response = await fetch( dataUrl );
    const blob = await response.blob();

    return {
      raw: store.pendingFile,
      fileObj: new File( [ blob ], name, { type: mimeType } )
    };
  }
};

// ==========================================
// 3. STEP HANDLERS
// ==========================================

/**
 * Step 1: Uploads the file stored in chrome.storage into NotebookLM
 */
async function handleFileUploadStep()
{
  console.log( '[Workflow] Running Step 1: File Upload' );

  const fileData = await StorageUtils.getPendingFile();
  if ( !fileData )
  {
    console.warn( '[Workflow] No file found in storage to upload.' );
    return;
  }

  // 1. Open "Add Source" Modal
  const addSourcesBtn = await DOMUtils.waitForElement( CONFIG.SELECTORS.ADD_SOURCE_BTN );
  addSourcesBtn.click();

  // 2. Trigger hover event to mount hidden <input type="file">
  const uploadTrigger = await DOMUtils.waitForElement( CONFIG.SELECTORS.UPLOAD_TRIGGER );
  DOMUtils.simulateHover( uploadTrigger );

  // 3. Attach file payload to input element
  const fileInput = await DOMUtils.waitForElement( CONFIG.SELECTORS.FILE_INPUT );
  const dataTransfer = new DataTransfer();
  dataTransfer.items.add( fileData.fileObj );
  fileInput.files = dataTransfer.files;

  fileInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
  fileInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );

  console.log( '[Workflow] File attached. Scheduling transition to Step 2...' );

  // 4. Update query parameters to step=2 and reload
  setTimeout( () =>
  {
    const searchParams = new URLSearchParams( window.location.search );
    searchParams.set( 'step', '2' );
    window.location.search = searchParams.toString();
  }, CONFIG.TIMEOUTS.STEP_TRANSITION );
}

/**
 * Step 2: Executes prompt, injects action controls, and handles output workflow
 */
async function handleExecutionStep()
{
  console.log( '[Workflow] Running Step 2: Query Execution' );

  const fileData = await StorageUtils.getPendingFile();
  const file = fileData?.raw;
  console.log( '[Workflow] Target File:', file );

  // 1. Dismiss initial popup if present
  try
  {
    const closePopup = await DOMUtils.waitForElement( CONFIG.SELECTORS.CLOSE_POPUP_BTN, CONFIG.TIMEOUTS.POPUP_WAIT );
    closePopup.click();
    console.log( '[Workflow] Popup dismissed' );
  } catch ( e )
  {
    console.log( '[Workflow] No popup detected' );
  }

  // 2. Locate file container in scroll area
  const scrollArea = await DOMUtils.waitForElement( CONFIG.SELECTORS.DROPZONE_SCROLL_AREA );
  const allowedWithCurrent = [ ...CONFIG.ALLOWED_FILES, file?.name ];
  let targetFileContainer = null;

  for ( const child of scrollArea.children )
  {
    const listedFileName = child.children[ 2 ]?.textContent?.trim();

    if ( !allowedWithCurrent.includes( listedFileName ) )
    {
      console.warn( `[Workflow] File "${listedFileName}" is not in allowed list.` );
    }

    if ( listedFileName === file?.name )
    {
      targetFileContainer = child;
    }
  }

  if ( !targetFileContainer )
  {
    throw new Error( `Target file container for "${file?.name}" was not found.` );
  }

  // 3. Check if file is still processing
  const progressBar = targetFileContainer.querySelector( CONFIG.SELECTORS.PROGRESS_BAR );
  if ( progressBar )
  {
    console.log( '[Workflow] File is still processing. Reloading in 10s...' );
    setTimeout( () => location.reload(), CONFIG.TIMEOUTS.PROGRESS_RELOAD );
    return;
  }

  // 4. Populate Query Box and Submit
  const queryBox = await DOMUtils.waitForElement( CONFIG.SELECTORS.QUERY_BOX );
  DOMUtils.setInputValue( queryBox, CONFIG.COMMAND_PROMPT );

  const submitBtn = await DOMUtils.waitForElement( CONFIG.SELECTORS.SUBMIT_BTN );
  submitBtn.click();

  // 5. Inject custom "Continue" trigger button
  injectContinueButton( submitBtn );
}

// ==========================================
// 4. AUXILIARY PIPELINE ACTIONS
// ==========================================

/**
 * Injects a custom "Continue" button above the submit container
 */
function injectContinueButton( submitBtn )
{
  submitBtn.style.position = 'relative';

  const continueBtn = document.createElement( 'button' );
  continueBtn.type = 'button';
  continueBtn.textContent = 'Continue';

  Object.assign( continueBtn.style, {
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
  } );

  continueBtn.addEventListener( 'click', ( event ) =>
  {
    event.preventDefault();
    finishOrchestration();
  } );

  if ( submitBtn.parentElement && submitBtn.parentElement.children[ 0 ] )
  {
    submitBtn.parentElement.children[ 0 ].after( continueBtn );
  }
}

/**
 * Finalization pipeline: copies output, cleans non-allowed files, notifies extension
 */
async function finishOrchestration()
{
  console.log( '[Workflow] Finishing orchestration pipeline...' );
  await copyGeneratedOutput();
  await removeUnallowedSources();

  chrome.runtime.sendMessage( CONFIG.EXTENSION_ID, {
    action: 'NOTEBOOK_COMPLETE'
  } );
}

/**
 * Copies generated result to clipboard and stores in storage
 */
async function copyGeneratedOutput()
{
  const copyBtn = document.querySelector( CONFIG.SELECTORS.COPY_BTN );
  if ( !copyBtn ) throw new Error( "Copy button not found" );

  copyBtn.click();
  await new Promise( resolve => setTimeout( resolve, CONFIG.TIMEOUTS.CLIPBOARD_WAIT ) );

  try
  {
    const copiedText = await navigator.clipboard.readText();
    console.log( '[Workflow] Captured generated output successfully.' );

    await chrome.storage.local.set( { notebookOutput: copiedText } );
  } catch ( err )
  {
    console.error( '[Workflow] Failed to read text from clipboard:', err );
  }
}

/**
 * Cleans up non-allowed uploaded sources from the sidebar
 */
async function removeUnallowedSources()
{
  const scrollArea = await DOMUtils.waitForElement( CONFIG.SELECTORS.DROPZONE_SCROLL_AREA );

  while ( true )
  {
    const currentItems = Array.from( scrollArea.children );

    const itemToDelete = currentItems.find( item =>
    {
      const fileName = item.children[ 2 ]?.textContent?.trim();
      return fileName && !CONFIG.ALLOWED_FILES.includes( fileName );
    } );

    if ( !itemToDelete )
    {
      console.log( '[Workflow] All non-allowed files removed successfully.' );
      break;
    }

    const fileName = itemToDelete.children[ 2 ].textContent.trim();
    console.log( `[Workflow] Deleting source: ${fileName}` );

    try
    {
      const moreBtn = itemToDelete.children[ 3 ]?.querySelector( CONFIG.SELECTORS.MORE_BTN );
      if ( !moreBtn ) throw new Error( 'More button not found' );
      moreBtn.click();

      const menuContent = await DOMUtils.waitForElement( CONFIG.SELECTORS.MENU_CONTENT );
      menuContent.children[ 0 ]?.children[ 0 ]?.click();

      await new Promise( resolve => setTimeout( resolve, CONFIG.TIMEOUTS.DOM_SETTLE ) );

      const dialogFooter = await DOMUtils.waitForElement( CONFIG.SELECTORS.DIALOG_FOOTER );
      dialogFooter.children[ 1 ]?.click();

      await new Promise( resolve => setTimeout( resolve, CONFIG.TIMEOUTS.DELETION_SETTLE ) );
    } catch ( err )
    {
      console.error( `[Workflow] Error deleting file "${fileName}":`, err );
      break;
    }
  }
}

// ==========================================
// 5. APPLICATION ENTRY POINT
// ==========================================
( async function init()
{
  const params = new URLSearchParams( window.location.search );
  const step = params.get( 'step' );

  if ( step === '2' || step === 2 )
  {
    await handleExecutionStep();
  } else
  {
    await handleFileUploadStep();
  }
} )();