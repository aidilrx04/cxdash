import config from "./config";
import { ACTIONS, EXT_ID, STATUS } from "./util";

console.log('service worker init');


// Relay messages between tabs
chrome.runtime.onMessage.addListener(async (message, sender, sendResponse) => {
  console.log(message)
  if (message.action === ACTIONS.START) {
    await chrome.storage.local.set({
      id: message.id,
      originalSender: sender,
      status: STATUS.RUNNING,
      reportFile: message.reportFile,
      feedback1: message.feedback1,
      feedback2: message.feedback2,
      action: ACTIONS.NOTEBOOK_START,
    });

    // Step 1: Open NotebookLM
    chrome.tabs.create({ url: config.sites.notebook.link });

    sendResponse({ status: "Started" });

    return true;
  }

  // Step 2: NotebookLM completed -> Open DeepSeek
  if (message.action === ACTIONS.NOTEBOOK_COMPLETE) {
    chrome.storage.local.set({
      output: {
        notebook: message.data
      },
      action: ACTIONS.DEEPSEEK_START
    });

    // Close NotebookLM tab and open DeepSeek
    if (sender.tab?.id) chrome.tabs.remove(sender.tab.id);

    chrome.tabs.create({ url: "https://chat.deepseek.com/" });
  }

  // Step 3: DeepSeek completed -> Open PAPS Local
  if (message.action === ACTIONS.DEEPSEEK_COMPLETE) {
    const originalSender = (await chrome.storage.local.get(['originalSender'])).originalSender

    if (!originalSender) {
      console.error('Original sender is not set!')
      return;
    }
    await chrome.storage.local.set({
      action: ACTIONS.DEEPSEEK_COMPLETE
    })
    handleProcessComplete(originalSender);
    if (sender.tab?.id) {
      await chrome.tabs.remove(sender.tab.id);
    }
  }


  if (message.action === ACTIONS.PAPS_START) {
    await chrome.storage.local.set({
      id: message.id,
      originalSender: sender,
      action: message.action,
      executiveSummary: message.executiveSummary
    })

    const PAPS_LINK = 'https://pgtraining-admin.peopleaps.com/'
    await chrome.tabs.create({
      url: PAPS_LINK
    })

    sendResponse({ status: 'Started' })
    return true;
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

    await chrome.storage.local.remove([
      'id',
      'executiveSummary',
      'originalSender',
      'action',
    ])

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

  try {
    // --- tabs.switchTo( originalSender ) ---
    await chrome.tabs.update(tabId, { active: true });
    if (windowId) {
      await chrome.windows.update(windowId, { focused: true });
    }
    await chrome.tabs.sendMessage(tabId, {
      action: ACTIONS.FINISH,
      data: 'Process finished successfully'
    }, async ({ status, action }) => {
      if (action) {
        // complete
        await chrome.storage.local.remove([
          'notebookOutput',
          'deepseekOutput',
          'id',
          'reportFile',
          'originalSender',
          'feedback1',
          'feedback2',
          'action'
        ]);

        await chrome.storage.local.set({
          status: STATUS.IDLE
        })

        console.log('Process Completed');
      }
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