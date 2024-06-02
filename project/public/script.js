const showMoreBtns = document.querySelectorAll(".show-more-btn");

showMoreBtns.forEach((btn) => {
  const descriptionDots = btn.parentElement.querySelector(".description-dots");
  const descriptionRest = btn.parentElement.querySelector(".description-rest");

  btn.addEventListener("click", () => {
    btn.textContent =
      btn.textContent === "Show More" ? "Show Less" : "Show More";
    descriptionDots.classList.toggle("visible");
    descriptionRest.classList.toggle("visible");
  });
});

const EDITED_CLASS = "edited";
//for some reason there is 92 instead of 100
const DESCRIPTION_SHORT_LENGTH = 92;

const editBtn = document.getElementById("edit-btn");
let isEditing = false;

let changes = {};

const selectProductTypeTemplate = getSelectElTemplate("product-type");
const selectManufacturerTemplate = getSelectElTemplate("manufacturer");

const productTypes = getTextValueFromOptionIds(selectProductTypeTemplate);
const manufacturers = getTextValueFromOptionIds(selectManufacturerTemplate);

const tbody = document.querySelector("tbody");

editBtn.addEventListener("click", () => {
  if (!isEditing) {
    isEditing = true;
    editBtn.textContent = "Save";
  } else {
    isEditing = false;
    editBtn.textContent = "Edit";
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
  //remove option to select all
  template.removeChild(template.firstElementChild);

  return template;
}

function getTextValueFromOptionIds(select) {
  return Array.from(select.children).map((option) => option.textContent.trim());
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
    case "product-type":
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
  newTd.appendChild(newInput);

  newInput.addEventListener("focusout", (e) => {
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

function newSelect(td, newTd, rowId, template, idToValue) {
  const newSelect = template.cloneNode(true);
  for (const option of newSelect.children) {
    if (option.textContent.trim() === td.textContent) {
      option.selected = true;
    }
  }

  newTd.appendChild(newSelect);

  newSelect.addEventListener("focusout", (e) => {
    td.classList.remove("hidden");

    const id = e.target.value - 1;
    const textValue = idToValue[id];
    if (td.textContent !== textValue) {
      setChange(rowId, td.className, textValue, td.textContent);

      td.textContent = textValue;
    }

    newTd.remove();
  });

  td.after(newTd);
  newSelect.focus();
  td.classList.add("hidden");
}

function newTextarea(td, newTd, rowId) {
  const newTextarea = document.createElement("textarea");
  const descriptionShort = td.querySelector(".description-short");
  const descriptionRest = td.querySelector(".description-rest");

  const textareaValue =
    descriptionShort.textContent + descriptionRest.textContent;

  newTextarea.value = textareaValue;

  newTd.appendChild(newTextarea);

  newTextarea.addEventListener("focusout", (e) => {
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
  if (!changes) return;

  console.log(changes);

  const newValues = Object.keys(changes).reduce((acc, key) => {
    acc[key] = Object.keys(changes[key]).reduce((acc, rowId) => {
      acc[rowId] = changes[key][rowId].newValue;
      return acc;
    }, {});
    return acc;
  }, {});

  console.log(newValues);

  try {
    const response = await fetch("edit.php", {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newValues),
    });

    if (!response.ok) {
      throw new Error("Failed to save changes");
    }

    const data = await response.json();
    console.log(data?.msg);
    console.log(data?.data);

    changes = {};
  } catch (error) {
    console.error(error);
  }
}
