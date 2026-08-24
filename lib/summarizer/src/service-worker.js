console.log( 'service worker init' );

const NOTEBOOK_LINK = 'https://notebook.google.com/notebook/a70e73b2-bab4-4226-8b5c-a55b00fc2548';

// console.log( ( chrome ) );


let originalSender;
let originalSendResponse;
let originalTabId = null;

// Relay messages between tabs
chrome.runtime.onMessage.addListener( async ( message, sender, sendResponse ) =>
{
  console.log( 'hello2' );
  console.log( message, sender, sendResponse );
  console.log( originalSender );

  if ( message.action === "START_ORCHESTRATION" )
  {
    originalTabId = sender.tab.id;
    originalSendResponse = sendResponse;
    originalSender = sender;
    chrome.storage.local.set( { originalSender: sender } );
    // Save prompt context to storage
    await chrome.storage.local.set( {
      userPrompt: message.prompt,
      filePath: message.filePath,
      status: "RUNNING",
      pendingFile: {
        name: message.fileName,
        dataUrl: message.fileDataUrl,
        mimeType: message.mimeType,
        timestamp: Date.now()
      },
      feedback1: message.feedback1,
      feedback2: message.feedback2,
    } );

    // Step 1: Open NotebookLM
    chrome.tabs.create( { url: NOTEBOOK_LINK }, ( tab ) =>
    {
      chrome.storage.local.set( { notebookTabId: tab.id } );
    } );

    sendResponse( { status: "Orchestration Started" } );


    return true;
  }

  // Step 2: NotebookLM completed -> Open DeepSeek
  if ( message.action === "NOTEBOOK_COMPLETE" )
  {
    chrome.storage.local.set( { notebookOutput: message.data } );

    // Close NotebookLM tab and open DeepSeek
    if ( sender.tab?.id ) chrome.tabs.remove( sender.tab.id );

    chrome.tabs.create( { url: "https://chat.deepseek.com/" } );
  }

  // Step 3: DeepSeek completed -> Open PAPS Local
  if ( message.action === "DEEPSEEK_COMPLETE" )
  {
    handleProcessComplete( ( await chrome.storage.local.get( [ 'originalSender' ] ) ).originalSender );
    await chrome.tabs.remove( sender.tab.id );
  }
} );

function clearStorage()
{
  chrome.storage.local.clear();
}

async function handleProcessComplete( originalSender )
{
  // 1. Ensure the message actually came from a tab (content script)
  if ( !originalSender?.tab?.id )
  {
    console.error( 'Cannot switch or message: Sender is not a tab.' );
    return;
  }

  const tabId = originalSender.tab.id;
  const windowId = originalSender.tab.windowId;

  console.log( tabId, windowId );

  try
  {
    // --- tabs.switchTo( originalSender ) ---
    await chrome.tabs.update( tabId, { active: true } );
    if ( windowId )
    {
      console.log( 2 );
      await chrome.windows.update( windowId, { focused: true } );
      console.log( 3 );
    }
    await chrome.tabs.sendMessage( tabId, {
      action: 'process_complete',
      data: 'Process finished successfully'
    } );

  } catch ( error )
  {
    console.error( 'Error switching tab or sending message:', error );
  }
}

async function test()
{
  const tabs = await chrome.tabs.query( { url: 'http://localhost:8000/admin/training-reports/17/edit' } );

  console.log( tabs );
  await chrome.tabs.sendMessage(
    tabs[ 0 ].id,
    'balss'
  );
}