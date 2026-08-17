( async function ()
{

  const params = new URLSearchParams( location.search );
  const step = params.get( 'step' );

  if ( step == '2' || step == 2 )
  {
    step2();
  }
  else
  {
    step1();
  }

  return;
} )();

async function getStoredFile()
{
  const store = await chrome.storage.local.get( [ "pendingFile" ] );
  if ( !store.pendingFile ) return null;

  const { name, dataUrl, mimeType } = store.pendingFile;

  console.log( store.pendingFile );

  // Convert Base64 Data URL back to a Blob / File
  const res = await fetch( dataUrl );
  const blob = await res.blob();

  return new File( [ blob ], name, { type: mimeType } );
}

async function waitForElement( selector, timeout = 10000 )
{
  return new Promise( ( resolve, reject ) =>
  {
    const startTime = Date.now();
    const interval = setInterval( () =>
    {
      const el = document.querySelector( selector );
      // console.log( el, selector );
      if ( el ) { clearInterval( interval ); resolve( el ); }
      if ( Date.now() - startTime > timeout ) { clearInterval( interval ); reject( "Timeout waiting for " + selector ); }
    }, 500 );
  } );
};

async function step1()
{
  const file = await getStoredFile();

  if ( !file )
  {
    console.log( 'no file' );
    return;
  }

  const addSourcesBtn = await waitForElement( 'button[aria-label="Add source"]' );

  // return;

  addSourcesBtn.click();

  const dataTransfer = new DataTransfer();
  dataTransfer.items.add( file );

  const opts = { bubbles: true, cancelable: true, view: window };
  const uploadBtn = await waitForElement( '[xapscottyuploadertrigger]' );

  // Fire standard Mouse events
  uploadBtn.dispatchEvent( new MouseEvent( 'mouseover', opts ) );
  uploadBtn.dispatchEvent( new MouseEvent( 'mouseenter', opts ) );
  uploadBtn.dispatchEvent( new MouseEvent( 'mousemove', opts ) );

  // Fire modern Pointer events (required for modern Web Components)
  uploadBtn.dispatchEvent( new PointerEvent( 'pointerover', opts ) );
  uploadBtn.dispatchEvent( new PointerEvent( 'pointerenter', opts ) );
  uploadBtn.dispatchEvent( new PointerEvent( 'pointermove', opts ) );


  const fileInput = await waitForElement( 'input[type="file"]' );

  fileInput.files = dataTransfer.files;

  fileInput.dispatchEvent( new Event( 'input', { bubbles: true } ) );
  fileInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );

  // 1. Get current search parameters
  const searchParams = new URLSearchParams( window.location.search );

  // 2. Set or update 'step' to 2
  searchParams.set( 'step', '2' );

  setTimeout( () =>
  {
    window.location.search = searchParams.toString();
  }, 10000 );
}

const allowed = [ '0. GOAL Executive Summary Template.pdf', '0. Skill Executive Summary Template v3.pdf', 'Skill Executive Summary Template v5.pdf' ];

async function step2()
{
  const COMMAND = `generate an Executive Summary for this training report followed the template provided. Do not provide reference in the response. Do not put double/single quote. Response must be in Markdown format. For part Participants particularly praised, ignore satisfaction rating, just focus on any positive comment, may also refer to additional comments. for example

"Participants specifically praised aspects such as practical learning, hands-on examples, and specific modules like Power Pivot and the Pivot table."`;
  const store = await chrome.storage.local.get( [ 'pendingFile' ] );
  const file = store.pendingFile;
  console.log( file );
  // await new Promise( ( rs, rj ) => setTimeout( () => rs(), 1000 ) );
  try
  {
    const closePopup = await waitForElement( '[aria-label="Close"]', 5000 );

    closePopup.click();
    console.log( 'popup closed' );
  }
  catch ( e )
  {
    console.log( 'no popup' );
  }

  const scrollArea = await waitForElement( '[xapscottyuploaderdropzone] .scroll-area-desktop' );
  const allowedWithCurrent = [ ...allowed, file.name ];
  let fileContainer;

  for ( const c of scrollArea.children )
  {
    const listedFileName = c.children[ 2 ].textContent;
    if ( !allowedWithCurrent.includes( listedFileName ) )
    {
      console.warn( listedFileName, 'is not in allowed list' );
    }

    if ( listedFileName == file.name )
    {
      fileContainer = c;
    }
  }

  if ( !fileContainer )
  {
    throw new Error( 'File Container is not set' );
  }

  // find progressbar
  const progressbar = fileContainer.querySelector( '[role=progressbar]' );

  if ( progressbar )
  {
    setTimeout( () =>
    {
      location.reload();
    }, 10 * 1000 ); // 10 seconds
    console.log( 'reload on progress' );
    return;
  }

  const textarea = await waitForElement( '[aria-label="Query box"]' );
  textarea.value = COMMAND;
  textarea.focus();

  // 2. Dispatch input & change events so the app knows value updated
  textarea.dispatchEvent( new Event( 'input', { bubbles: true } ) );
  textarea.dispatchEvent( new Event( 'change', { bubbles: true } ) );

  const submitBtn = await waitForElement( '[type=submit]' );
  submitBtn.click();

  submitBtn.style.position = 'relative';

  // 1. Create the "Continue" button element
  const continueBtn = document.createElement( 'button' );
  continueBtn.type = 'button'; // Explicit type prevents accidental form submit
  continueBtn.textContent = 'Continue';

  // 2. Add custom styling
  Object.assign( continueBtn.style, {
    display: 'block',
    marginLeft: '10px',
    marginRight: '10px',
    padding: '10px 16px',
    backgroundColor: '#10a37f', // Sleek modern green
    color: '#ffffff',
    border: 'none',
    borderRadius: '6px',
    fontSize: '14px',
    fontWeight: '600',
    cursor: 'pointer',
    boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)',
    transition: 'background-color 0.2s ease'
  } );

  // 3. Attach empty click listener
  continueBtn.addEventListener( 'click', ( event ) =>
  {
    event.preventDefault();
    finish();
  } );

  // Place button as the second child of submitBtn's parent
  if ( submitBtn.parentElement && submitBtn.parentElement.children[ 0 ] )
  {
    submitBtn.parentElement.children[ 0 ].after( continueBtn );
  }
}

async function finish()
{
  await copyOutput();
  await removeSources();
  // return;
  chrome.runtime.sendMessage( 'hjjclicidmkobencbdmlbnicdnpbcooo', {
    action: 'NOTEBOOK_COMPLETE'
  } );
}

async function copyOutput()
{
  const copyBtn = document.querySelector( '.xap-copy-to-clipboard' );

  if ( !copyBtn ) throw new Error( "Copy button not found" );

  // Note: standard .click() does not accept arguments. 
  // Use .click() directly or dispatch a MouseEvent if custom bubbling is required.
  copyBtn.click();

  // Wait a tiny frame (100ms) for the site's internal copy event to write to the clipboard
  await new Promise( resolve => setTimeout( resolve, 100 ) );

  // Read the copied text from the clipboard
  let copiedText = '';
  try
  {
    copiedText = await navigator.clipboard.readText();
    console.log( "Successfully captured copied text:", copiedText );
  } catch ( err )
  {
    console.error( "Failed to read clipboard:", err );
    return;
  }

  await chrome.storage.local.set( {
    notebookOutput: copiedText
  } );
}

async function removeSources()
{
  const scrollArea = await waitForElement( '[xapscottyuploaderdropzone] .scroll-area-desktop' );

  let itemsRemaining = true;

  while ( itemsRemaining )
  {
    // Convert live HTMLCollection into a static snapshot array on each pass
    const currentItems = Array.from( scrollArea.children );

    // Find the first item that is NOT in the allowed list
    const itemToDelete = currentItems.find( item =>
    {
      const fileName = item.children[ 2 ]?.textContent?.trim();
      return fileName && !allowed.includes( fileName );
    } );

    if ( !itemToDelete )
    {
      console.log( 'All non-allowed files removed successfully!' );
      itemsRemaining = false;
      break;
    }

    const fileName = itemToDelete.children[ 2 ].textContent.trim();
    console.log( 'Deleting file:', fileName );

    try
    {
      // 1. Click "More" options button
      const moreBtn = itemToDelete.children[ 3 ]?.querySelector( 'button[aria-label="More"]' );
      if ( !moreBtn ) throw new Error( 'More button not found' );
      moreBtn.click();

      // 2. Click the first menu item (Delete/Remove)
      const menuContent = await waitForElement( '.mat-mdc-menu-content' );
      menuContent.children[ 0 ]?.children[ 0 ]?.click();

      await new Promise( resolve => setTimeout( resolve, 500 ) );

      // 3. Confirm deletion in dialog
      const dialogFooter = await waitForElement( '.dialog-footer' );
      dialogFooter.children[ 1 ]?.click();

      console.log( 'Waiting for item removal to settle...' );
      // Allow Angular time to process deletion and update the DOM
      await new Promise( resolve => setTimeout( resolve, 2500 ) );

    } catch ( err )
    {
      console.error( `Failed while deleting ${fileName}:`, err );
      break; // Exit loop on error to prevent infinite loops
    }
  }
}