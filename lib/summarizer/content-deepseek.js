(async function() {
  const store = await chrome.storage.local.get(["notebookOutput"]);
  if (!store.notebookOutput) return;

  const waitForElement = (selector) => new Promise(resolve => {
    const interval = setInterval(() => {
      const el = document.querySelector(selector);
      if (el) { clearInterval(interval); resolve(el); }
    }, 500);
  });

  // 1. Target chat input box
  const inputEl = await waitForElement("#chat-input"); // Adjust to actual DeepSeek selector
  inputEl.value = `Format this notebook summary into an executive report:\n\n${store.notebookOutput}`;
  inputEl.dispatchEvent(new Event('input', { bubbles: true }));

  // 2. Click submit
  const submitBtn = document.querySelector("button[type='submit']");
  if (submitBtn) submitBtn.click();

  // 3. Extract output once done
  setTimeout(async () => {
    const outputs = document.querySelectorAll(".ds-markdown");
    const finalReport = outputs[outputs.length - 1]?.innerText || "";

    chrome.runtime.sendMessage({
      action: "DEEPSEEK_COMPLETE",
      data: finalReport
    });
  }, 15000);
})();