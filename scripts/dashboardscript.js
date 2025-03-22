document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("add-book-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const inputtitle = document.getElementById("book-title");
        const inputdesc = document.getElementById("book-description");
        const inputcategory = document.getElementById("book-category");
        const titlemsg = document.getElementById("title-error-message");
        const descmsg = document.getElementById("description-error-message");
        const categorymsg = document.getElementById("category-error-message");
        if (inputtitle.value == null || inputtitle.value == "") {
            e.preventDefault();
            titlemsg.textContent = "Must enter a book title";
        } else {
            titlemsg.textContent = "";
        }
        if (inputdesc.value == null || inputdesc.value == "") {
            e.preventDefault();
            descmsg.textContent = "Must enter a book description";
        } else {
            descmsg.textContent = "";
        }
        if (inputcategory.value == " ") {
            e.preventDefault();
            categorymsg.textContent = "Must choose a book category";
        } else {
            categorymsg.textContent = "";
        }
    });
})