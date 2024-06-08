//SHOW MORE HANLDING
const showMoreBtns = document.querySelectorAll(".show-more-btn");

showMoreBtns.forEach((btn) => {
  const descriptionDots = btn.parentElement.querySelector(".description-dots");
  const descriptionRest = btn.parentElement.querySelector(".description-rest");

  btn.addEventListener("click", () => {
    btn.textContent =
      btn.textContent === "Zobrazit více" ? "Zobrzit méně" : "Zobrazit více";
    descriptionDots.classList.toggle("hidden");
    descriptionRest.classList.toggle("hidden");
  });
});

//SELECTABLE SEARCH
const searchableSelects = document.querySelectorAll(".searchable-select");
//we add ability to search for options to base inputs
searchableSelects.forEach((select) => addSearchability(select));

/*
function that makes our custom select searchable
and sets up functions for handling interaction with it
*/
function addSearchability(select, onSelect, onBlur) {
  const optionList = select.querySelector(".search-options");
  const searchInput = select.querySelector(".search-input");
  const idInput = select.querySelector(".id-input");

  //variable to keep track if an option was chosen or the input has been left empty
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

  searchInput.addEventListener("blur", () => {
    select.classList.remove("searching");
    //blur event fires after mousedown se we can check if an option was selected
    if (!wasOptionSelected) {
      searchInput.value = "";
      idInput.value = "";
    }
    //back to default
    wasOptionSelected = false;

    onBlur && onBlur();
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
const cancelBtn = document.getElementById("cancel-btn");
let isEditing = false;

let changes = {};

const selectProductTypeTemplate = getSelectTemplate("product_type");
const selectManufacturerTemplate = getSelectTemplate("manufacturer");

//gets options to object which stores id to value and value to id mapping
const productTypes = getOptions(selectProductTypeTemplate);
const manufacturers = getOptions(selectManufacturerTemplate);

//we create a template of our custom select with search functionality
function getSelectTemplate(templateId) {
  const template = document.getElementById(templateId).cloneNode(true);

  //we remove label
  template.removeChild(template.firstElementChild);

  return template;
}

//we get the options from our custom select with search functionality
function getOptions(select) {
  const options = select.querySelector(".search-options");

  //for each option we create a mapping from id to value and value to id
  return Array.from(options.children).reduce((acc, option) => {
    const id = option.dataset.id;
    const value = option.textContent;
    acc[id] = value;
    acc[value] = id;
    return acc;
  }, {});
}

const tbody = document.querySelector("tbody");

cancelBtn.addEventListener("click", undoChanges);

editBtn.addEventListener("click", () => {
  if (!isEditing) {
    startEditing();
  } else {
    stopEditing();
    saveChanges();
  }
});

function startEditing() {
  isEditing = true;
  editBtn.textContent = "Uložit";
  cancelBtn.classList.remove("hidden");
}

function stopEditing() {
  isEditing = false;
  editBtn.textContent = "Upravit";
  cancelBtn.classList.add("hidden");
}

//function that decides if we should start editing a cell
tbody.addEventListener("click", (e) => {
  if (!isEditing) return;
  //we try to get some element that is td
  let target = e.target;
  if (target.tagName !== "TD") {
    target = target.parentElement;
  }
  if (target.tagName !== "TD") return;

  if (target.classList.contains(EDITED_CLASS)) return;

  changeForInput(target);
});

//function that changes td for some kind of input (input, textarea, our custom select)
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

//NOTE: we could use OOP as well

//function that creates new input element for editing a cell
function newInput(td, newTd, rowId, type) {
  const newInput = document.createElement("input");
  newInput.type = type;
  newInput.value = td.textContent;
  newTd.append(newInput);

  newInput.addEventListener("blur", (e) => {
    td.classList.remove("hidden");

    //if the value is different we save the change
    if (td.textContent !== e.target.value) {
      const numberValue = parseFloat(e.target.value);

      //this should never happen because we have type="number" but just in case
      if (type === "number" && isNaN(numberValue)) {
        showErrorModal("Vstup musí být číslo");
        newInput.focus();
        return;
      } else if (type === "number") {
        setChange(rowId, td.className, numberValue, parseFloat(td.textContent));

        updateInput(td, numberValue.toFixed(2));
      } else {
        setChange(rowId, td.className, e.target.value, td.textContent);

        updateInput(td, e.target.value);
      }
    }

    newTd.remove();
  });

  td.after(newTd);

  newInput.focus();

  td.classList.add("hidden");
}

//function that updates the value of a cell after it has been edited by input element
//NOTE: this function is just for consistency, because textarea and select have their own update functions
function updateInput(td, newValue) {
  td.textContent = newValue;
}

//function that creates our custom select for editing a cell
function newSelect(td, newTd, rowId, template, idValueBiMap) {
  const newSelect = template.cloneNode(true);

  newTd.append(newSelect);

  //variable to keep track if an option was chosen or the input has been left empty
  let wasOptionSelected = false;

  addSearchability(
    newSelect,
    (selectedOption) => {
      td.classList.remove("hidden");
      if (td.textContent !== selectedOption.textContent) {
        setChange(
          rowId,
          td.className,
          selectedOption.dataset.id,
          //we map textContent (value) to id of an option using bidirectional map
          idValueBiMap[td.textContent]
        );

        updateSelect(td, selectedOption.textContent);

        wasOptionSelected = true;
      }

      newTd.remove();
    },
    () => {
      //if no option was selected we remove the select and go back to initial state
      if (!wasOptionSelected) {
        td.classList.remove("hidden");
        newTd.remove();
      }
    }
  );

  const searchInput = newSelect.querySelector(".search-input");

  td.after(newTd);
  searchInput.focus();
  td.classList.add("hidden");
}

//this function is for consistency as well (see updateInput), because textarea has its own update function
function updateSelect(td, newValue) {
  td.textContent = newValue;
}

//function that creates textarea for editing a cell
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

    const description = e.target.value;
    if (textareaValue !== description) {
      updateTextarea(td, description, descriptionShort, descriptionRest);

      setChange(rowId, "description", description, textareaValue);
    }

    newTd.remove();
  });

  td.after(newTd);
  newTextarea.focus();
  td.classList.add("hidden");
}

//function that updates the value of a cell after it has been edited by textarea element
function updateTextarea(td, newValue, descriptionShort, descriptionRest) {
  if (!descriptionShort) {
    descriptionShort = td.querySelector(".description-short");
  }
  if (!descriptionRest) {
    descriptionRest = td.querySelector(".description-rest");
  }
  //descriptionDots and showMoreBtn are not used in any function calling this one, so having it as parameter would be useless
  const descriptionDots = td.querySelector(".description-dots");
  const showMoreBtn = td.querySelector(".show-more-btn");

  if (newValue.length <= DESCRIPTION_SHORT_LENGTH) {
    descriptionShort.textContent = newValue;
    descriptionDots.classList.add("hidden");
    showMoreBtn.classList.add("hidden");
    descriptionRest.textContent = "";
    return;
  }

  //if descriptionDots is hidden, showMoreBtn is hidden as well
  if (descriptionDots.classList.contains("hidden")) {
    descriptionDots.classList.remove("hidden");
    showMoreBtn.classList.remove("hidden");
  }

  descriptionShort.textContent = newValue.slice(0, DESCRIPTION_SHORT_LENGTH);
  descriptionRest.textContent = newValue.slice(DESCRIPTION_SHORT_LENGTH);
}

//function that sets the change to the changes object
function setChange(rowId, className, newValue, oldValue) {
  if (!changes[className]) changes[className] = {};
  changes[className][rowId] = { newValue, oldValue };
}

//function that sends the changes to the server and handles possible errors
async function saveChanges() {
  if (Object.keys(changes).length === 0) return;

  const newValues = Object.keys(changes).reduce((acc, key) => {
    acc[key] = Object.keys(changes[key]).reduce((acc, rowId) => {
      acc[rowId] = changes[key][rowId].newValue;
      return acc;
    }, {});
    return acc;
  }, {});

  try {
    const response = await fetch("/products/edit", {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newValues),
    });

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data?.error || "Neočekávaná chyba při ukládání změn.");
    }

    changes = {};
  } catch (error) {
    undoChanges();
    showErrorModal(error);
  }
}

//function that undoes the changes made by the user
function undoChanges() {
  Object.entries(changes).forEach(([column, rows]) => {
    Object.entries(rows).forEach(([rowId, value]) => {
      //we have to use [] because id starts with a number
      const td = document.querySelector(`[id='${rowId}'] .${column}`);

      switch (column) {
        case "description":
          updateTextarea(td, value.oldValue);
          break;
        case "product_type":
          //we have to map id to value
          updateSelect(td, productTypes[value.oldValue]);
          break;
        case "manufacturer":
          //we have to map id to value
          updateSelect(td, manufacturers[value.oldValue]);
          break;
        default:
          updateInput(td, value.oldValue);
      }
    });
  });

  changes = {};

  stopEditing();
}

//function that shows modal with error message
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
