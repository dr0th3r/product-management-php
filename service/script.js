const showMoreBtns = document.querySelectorAll(".show-more-btn");

showMoreBtns.forEach((btn) => {
  btn.addEventListener("click", () => {
    btn.textContent =
      btn.textContent === "Show More" ? "Show Less" : "Show More";
    const content = btn.parentElement;
    console.log(content);
    content.classList.toggle("show-content");
  });
});
