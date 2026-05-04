(function () {
  function populateLgaOptions(stateSelectId, lgaSelectId, lgaMap, selectedLga) {
    const stateSelect = document.getElementById(stateSelectId);
    const lgaSelect = document.getElementById(lgaSelectId);

    if (!stateSelect || !lgaSelect || !lgaMap) {
      return;
    }

    const renderLgaList = () => {
      const state = stateSelect.value;
      const lgas = lgaMap[state] || [];

      lgaSelect.innerHTML = '<option value="">Select LGA</option>';

      lgas.forEach((lga) => {
        const option = document.createElement("option");
        option.value = lga;
        option.textContent = lga;
        if (selectedLga && selectedLga === lga) {
          option.selected = true;
        }
        lgaSelect.appendChild(option);
      });

      if (!lgas.includes(selectedLga || "")) {
        lgaSelect.value = "";
      }

      selectedLga = "";
    };

    stateSelect.addEventListener("change", renderLgaList);
    renderLgaList();
  }

  window.CYPopulateLgaOptions = populateLgaOptions;
})();
