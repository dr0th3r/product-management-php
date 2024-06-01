const showMoreBtns = document.querySelectorAll(".show-more-btn");

showMoreBtns.forEach((btn) => {
  const descriptionDots = btn.parentElement.querySelector(".description-dots");
  const descriptionRest = btn.parentElement.querySelector(".description-rest");

  btn.addEventListener("click", () => {
    btn.textContent =
      btn.textContent === "Show More" ? "Show Less" : "Show More";
    descriptionDots.classList.toggle("visible");
    descriptionRest.classList.toggle("visible");
    content.classList.toggle("show-content");
  });
});
