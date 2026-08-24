chrome.runtime.onMessage.addListener( async ( message, sender, sendResponse ) =>
{
  console.log( "message received" );
  console.log( message, sender, sendResponse );
  if ( message.action === 'process_complete' )
  {
    console.log( 'Received completion signal from extension background.' );

    sendResponse( { status: 'success' } );

    const notebookOutput = ( await chrome.storage.local.get( [ 'notebookOutput' ] ) ).notebookOutput;
    const deepseekOutput = ( await chrome.storage.local.get( [ 'deepseekOutput' ] ) ).deepseekOutput;

    pasteRawMarkdown( `${notebookOutput}\n\n${deepseekOutput}` );
  }
} );

( async function ()
{
  const summarizerBtn = document.querySelector( '[data-summarizer]' );

  if ( !summarizerBtn ) throw new Error( "Unable to find summarizer button" );

  summarizerBtn.addEventListener( 'click', async function ( event )
  {
    try
    {
      const filePath = summarizerBtn.dataset?.filePath;
      if ( !filePath ) throw new Error( "No file path specified on data-file-path attribute" );

      // 1. Download and convert the file to Base64
      console.log( "Downloading file from:", filePath );
      const fileData = await downloadFile( filePath );

      // 2. Extract a clean filename
      const fileName = document.getElementById( 'form.file_name' )?.value || ( filePath.split( '/' ).pop().split( '?' )[ 0 ] || "document.pdf" );

      // 3. Send file payload to Chrome Extension
      const EXTENSION_ID = 'hjjclicidmkobencbdmlbnicdnpbcooo';

      chrome.runtime.sendMessage(
        EXTENSION_ID,
        {
          action: "START_ORCHESTRATION",
          prompt: "Quarterly Financial Analysis",
          filePath: filePath,
          fileName: fileName,
          fileDataUrl: fileData.dataUrl, // Base64 encoded file string
          mimeType: fileData.mimeType,
          feedback1: summarizerBtn.dataset.questionLikeTheMost,
          feedback2: summarizerBtn.dataset.questionFutureCourses
        },
        ( response ) =>
        {
          if ( chrome.runtime.lastError )
          {
            console.error( "Extension communication error:", chrome.runtime.lastError.message );
            return;
          }
          console.log( "Extension response:", response );
        }
      );

    } catch ( err )
    {
      console.error( "Failed to download or pass file to extension:", err );
    }
  } );

  // Downloads file and reads it as Base64 Data URL
  async function downloadFile( url )
  {
    const response = await fetch( url );
    if ( !response.ok ) throw new Error( `HTTP error fetching file! status: ${response.status}` );

    const blob = await response.blob();
    const mimeType = blob.type || 'application/pdf';

    return new Promise( ( resolve, reject ) =>
    {
      const reader = new FileReader();
      reader.onloadend = () => resolve( { dataUrl: reader.result, mimeType } );
      reader.onerror = reject;
      reader.readAsDataURL( blob );
    } );
  }
} )();



function pasteRawMarkdown( markdownText )
{
  const editorEl = document.querySelector( '.tiptap.ProseMirror' );
  if ( !editorEl )
  {
    console.error( 'TipTap element not found.' );
    return;
  }

  editorEl.focus();

  // Create DataTransfer with ONLY text/plain (no text/html)
  const dataTransfer = new DataTransfer();
  dataTransfer.setData( 'text/plain', markdownText );

  const pasteEvent = new ClipboardEvent( 'paste', {
    clipboardData: dataTransfer,
    bubbles: true,
    cancelable: true,
    composed: true
  } );

  editorEl.dispatchEvent( pasteEvent );
}