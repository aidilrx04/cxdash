import config from "./config";
import { ACTIONS, EXT_ID, STATUS } from "./util";

console.log('service worker init');


// Relay messages between tabs
chrome.runtime.onMessage.addListener(async (message, sender, sendResponse) => {
  await chrome.storage.local.clear()

  console.log(message)
  if (message.action === ACTIONS.START) {
    await chrome.storage.local.set({
      id: message.id,
      originalSender: sender,
      status: STATUS.RUNNING,
      reportFile: message.reportFile,
      feedback1: message.feedback1,
      feedback2: message.feedback2,
    });

    // Step 1: Open NotebookLM
    chrome.tabs.create({ url: config.sites.notebook.link });

    sendResponse({ status: "Started" });

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
    const originalSender = (await chrome.storage.local.get(['originalSender'])).originalSender
    if (!originalSender) {
      console.error('Original sender is not set!')
      return;
    }

    handleProcessComplete(originalSender);
    if (sender.tab?.id) {
      await chrome.tabs.remove(sender.tab.id);
    }
  }


  if (message.action === ACTIONS.PAPS_START) {
    await chrome.storage.local.set({
      originalSender: sender,
      action: message.action,
      executiveSummary: message.executiveSummary
    })

    const PAPS_LINK = 'https://pgtraining-admin.peopleaps.com/'
    await chrome.tabs.create({
      url: PAPS_LINK
    })
  }

  if (message.action === ACTIONS.PAPS_COMPLETE) {
    if (sender.tab?.id) chrome.tabs.remove(sender.tab?.id)
    const originalSender = (await chrome.storage.local.get(['originalSender'])).originalSender as chrome.runtime.MessageSender
    if (!originalSender) {
      console.error('Original sender is not set!')
      return;
    }

    if (!originalSender.tab?.id) {
      console.warn('sender is not a tab')
      return;
    }

    await chrome.tabs.update(originalSender.tab?.id, {
      active: true
    })

    await chrome.tabs.sendMessage(originalSender.tab.id, {
      action: message.action,
    })
  }
});

function clearStorage() {
  chrome.storage.local.clear();
}

async function handleProcessComplete(originalSender: chrome.runtime.MessageSender) {
  // 1. Ensure the message actually came from a tab (content script)
  if (!originalSender?.tab?.id) {
    console.error('Cannot switch or message: Sender is not a tab.');
    return;
  }

  const tabId = originalSender.tab.id;
  const windowId = originalSender.tab.windowId;

  console.log(tabId, windowId);

  try {
    // --- tabs.switchTo( originalSender ) ---
    await chrome.tabs.update(tabId, { active: true });
    if (windowId) {
      console.log(2);
      await chrome.windows.update(windowId, { focused: true });
      console.log(3);
    }
    await chrome.tabs.sendMessage(tabId, {
      action: 'process_complete',
      data: 'Process finished successfully'
    });

  } catch (error) {
    console.error('Error switching tab or sending message:', error);
  }
}

// async function test() {
//   const tabs = await chrome.tabs.query({ url: 'http://localhost:8000/admin/training-reports/17/edit' });

//   console.log(tabs);
//   await chrome.tabs.sendMessage(
//     tabs[0].id,
//     'balss'
//   );
// }