(async function() {
  const store = await chrome.storage.local.get(["notebookOutput", "deepseekOutput"]);
  if (!store.deepseekOutput) return;

  // 1. Inject combined text into target ASPX text fields
  const notebookField = document.querySelector("#txtNotebookSummary");
  const deepseekField = document.querySelector("#txtExecutiveSummary");

  if (notebookField) notebookField.value = store.notebookOutput;
  if (deepseekField) deepseekField.value = store.deepseekOutput;

  // 2. Click the ASP.NET Generate Report Button
  const generateBtn = document.querySelector("#btnGenerateReport");
  if (generateBtn) {
    generateBtn.click(); // Triggers PDF download response stream
  }
})();