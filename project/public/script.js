//SHOW MORE HANLDING
const showMoreBtns = document.querySelectorAll(".show-more-btn");

showMoreBtns.forEach((btn) => {
  const descriptionDots = btn.parentElement.querySelector(".description-dots");
  const descriptionRest = btn.parentElement.querySelector(".description-rest");

  btn.addEventListener("click", () => {
    btn.textContent =
      btn.textContent === "Zobrazit více" ? "Zobrzit méně" : "Zobrazit více";
    descriptionDots.classList.toggle("visible");
    descriptionRest.classList.toggle("visible");
  });
});

//SELECTABLE SEARCH
const searchableSelects = document.querySelectorAll(".searchable-select");
//we add ability to search for options to base inputs
searchableSelects.forEach((select) => addSearchability(select));

function addSearchability(select, onSelect) {
  const optionList = select.querySelector(".search-options");
  const searchInput = select.querySelector(".search-input");
  const idInput = select.querySelector(".id-input");

  let wasOptionSelected = false;

  //we have to use mousedown because unlike click it fires before blur
  optionList.addEventListener("mousedown", (e) => {
    idInput.value = e.target.dataset.id;
    searchInput.value = e.target.textContent;
    wasOptionSelected = true;
    onSelect && onSelect(e.target);
  });

  searchInput.addEventListener("focus", () => {
    select.classList.add("searching");
    searchInput.value = "";
    filterOptions(optionList.children, "");
  });

  searchInput.addEventListener("blur", (e) => {
    select.classList.remove("searching");
    if (!wasOptionSelected) {
      searchInput.value = "";
      idInput.value = "";
    }
    //back to default
    wasOptionSelected = false;
  });

  searchInput.addEventListener("input", () =>
    filterOptions(optionList.children, searchInput.value.toLowerCase())
  );
}

function filterOptions(options, searchValue) {
  for (const option of options) {
    const optionText = option.textContent.toLowerCase();
    if (optionText.includes(searchValue)) {
      option.classList.remove("hidden");
    } else {
      option.classList.add("hidden");
    }
  }
}

//table editing
const EDITED_CLASS = "edited";
//for some reason there is 92 instead of 100
const DESCRIPTION_SHORT_LENGTH = 92;

const editBtn = document.getElementById("edit-btn");
let isEditing = false;

let changes = {};

const selectProductTypeTemplate = getSelectElTemplate("product_type");
const selectManufacturerTemplate = getSelectElTemplate("manufacturer");

//gets options to object where key is textContent and value is id
const productTypes = getOptions(selectProductTypeTemplate);
const manufacturers = getOptions(selectManufacturerTemplate);

const tbody = document.querySelector("tbody");

editBtn.addEventListener("click", () => {
  if (!isEditing) {
    isEditing = true;
    editBtn.textContent = "Uložit";
  } else {
    isEditing = false;
    editBtn.textContent = "Upravit";
    saveChanges();
  }
});

tbody.addEventListener("click", (e) => {
  if (!isEditing) return;
  let target = e.target;
  if (target.tagName !== "TD") {
    target = target.parentElement;
  }

  if (target.tagName !== "TD") return;

  if (target.classList.contains(EDITED_CLASS)) return;

  changeForInput(target);
});

function getSelectElTemplate(templateId) {
  const template = document.getElementById(templateId).cloneNode(true);

  //we remove label
  template.removeChild(template.firstElementChild);

  return template;
}

function getOptions(select) {
  const options = select.querySelector(".search-options");

  return Array.from(options.children).reduce((acc, option) => {
    acc[option.textContent] = option.dataset.id;
    return acc;
  }, {});
}

function changeForInput(td) {
  const rowId = td.parentElement.id;

  const newTd = document.createElement("td");
  newTd.className = td.className;
  newTd.classList.add(EDITED_CLASS);

  switch (td.className) {
    case "code":
      newInput(td, newTd, rowId, "text");
      break;
    case "price":
      newInput(td, newTd, rowId, "number");
      break;
    case "product_type":
      newSelect(td, newTd, rowId, selectProductTypeTemplate, productTypes);
      break;
    case "manufacturer":
      newSelect(td, newTd, rowId, selectManufacturerTemplate, manufacturers);
      break;
    case "description":
      newTextarea(td, newTd, rowId);
  }
}

function newInput(td, newTd, rowId, type) {
  const newInput = document.createElement("input");
  newInput.type = type;
  newInput.value = td.textContent;
  newTd.append(newInput);

  newInput.addEventListener("blur", (e) => {
    td.classList.remove("hidden");

    if (td.textContent !== e.target.value) {
      const numberValue = parseFloat(e.target.value);

      if (isNaN(numberValue)) {
        setChange(rowId, td.className, e.target.value, td.textContent);

        td.textContent = e.target.value;
      } else {
        setChange(rowId, td.className, numberValue, td.textContent);

        td.textContent = numberValue.toFixed(2);
      }
    }

    newTd.remove();
  });

  td.after(newTd);

  newInput.focus();

  td.classList.add("hidden");
}

function newSelect(td, newTd, rowId, template, textContentToId) {
  const newSelect = template.cloneNode(true);

  newTd.append(newSelect);

  addSearchability(newSelect, (selectedOption) => {
    td.classList.remove("hidden");
    if (td.textContent !== selectedOption.textContent) {
      setChange(
        rowId,
        td.className,
        selectedOption.dataset.id,
        textContentToId[td.textContent]
      );

      td.textContent = selectedOption.textContent;
    }

    newTd.remove();
  });

  const searchInput = newSelect.querySelector(".search-input");

  td.after(newTd);
  searchInput.focus();
  td.classList.add("hidden");
}

function newTextarea(td, newTd, rowId) {
  const newTextarea = document.createElement("textarea");
  const descriptionShort = td.querySelector(".description-short");
  const descriptionRest = td.querySelector(".description-rest");

  const textareaValue =
    descriptionShort.textContent + descriptionRest.textContent;

  newTextarea.value = textareaValue;

  newTd.append(newTextarea);

  newTextarea.addEventListener("blur", (e) => {
    td.classList.remove("hidden");

    if (textareaValue !== e.target.value) {
      const description = e.target.value;
      const short = description.slice(0, DESCRIPTION_SHORT_LENGTH);
      const rest = description.slice(DESCRIPTION_SHORT_LENGTH);

      descriptionShort.textContent = short;
      descriptionRest.textContent = rest;

      setChange(rowId, "description", description, textareaValue);
    }

    newTd.remove();
  });

  td.after(newTd);
  newTextarea.focus();
  td.classList.add("hidden");
}

function setChange(rowId, className, newValue, oldValue) {
  if (!changes[className]) changes[className] = {};
  changes[className][rowId] = { newValue, oldValue };
}

async function saveChanges() {
  if (Object.keys(changes).length === 0) return;

  console.log(changes);

  const newValues = Object.keys(changes).reduce((acc, key) => {
    acc[key] = Object.keys(changes[key]).reduce((acc, rowId) => {
      acc[rowId] = changes[key][rowId].newValue;
      return acc;
    }, {});
    return acc;
  }, {});

  try {
    const response = await fetch("edit.php", {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newValues),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data?.error || "Neočekávaná chyba při ukládání změn.");
    }

    changes = {};
  } catch (error) {
    console.error(error);
    showErrorModal(error);
  }
}

function showErrorModal(errorMsg) {
  const modal = document.createElement("div");

  modal.classList.add("modal");

  modal.innerHTML = `
      <h2>Chyba</h2>
      <p>${errorMsg}</p>
  `;

  const closeBtn = document.createElement("button");
  closeBtn.className = "close-modal-btn";
  closeBtn.textContent = "Zavřít";

  closeBtn.addEventListener("click", () => {
    modal.remove();
  });

  modal.append(closeBtn);

  document.body.append(modal);
}
