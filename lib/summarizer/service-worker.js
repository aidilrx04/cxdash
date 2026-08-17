console.log('service worker init')

const NOTEBOOK_LINK =  'https://notebook.google.com/notebook/a70e73b2-bab4-4226-8b5c-a55b00fc2548'


let originalSender;
let originalSendResponse;


// Relay messages between tabs
chrome.runtime.onMessage.addListener(async (message, sender, sendResponse) => {
  console.log('hello2')
  console.log(message, sender, sendResponse)

  if (message.action === "START_ORCHESTRATION") {
    originalSendResponse = sendResponse;
    originalSender = sender;
    // Save prompt context to storage
    await chrome.storage.local.set({ 
      userPrompt: message.prompt,
      filePath: message.filePath,
      status: "RUNNING",
      pendingFile: {
        name: message.fileName,
        dataUrl: message.fileDataUrl,
        mimeType: message.mimeType,
        timestamp: Date.now()
      }
    });
    
    // Step 1: Open NotebookLM
    chrome.tabs.create({ url: NOTEBOOK_LINK }, (tab) => {
      chrome.storage.local.set({ notebookTabId: tab.id });
    });
    
    sendResponse({ status: "Orchestration Started" });


    return true;
  }

  // Step 2: NotebookLM completed -> Open DeepSeek
  if (message.action === "NOTEBOOK_COMPLETE") {
    chrome.storage.local.set({ notebookOutput: message.data });
    
    // Close NotebookLM tab and open DeepSeek
    if (sender.tab?.id) chrome.tabs.remove(sender.tab.id);
    
    chrome.tabs.create({ url: "https://chat.deepseek.com/" });
  }

  // Step 3: DeepSeek completed -> Open PAPS Local
  if (message.action === "DEEPSEEK_COMPLETE") {
    chrome.storage.local.set({ deepseekOutput: message.data });
    
    if (sender.tab?.id) chrome.tabs.remove(sender.tab.id);
    
    chrome.tabs.create({ url: "http://paps.local:5123/create_report.aspx" });
  }
});

function clearStorage() {
  chrome.storage.local.clear();
}