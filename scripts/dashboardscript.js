document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("add-book-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const inputemail = document.getElementById("book-title");
        const inputpassword = document.getElementById("book-description");
        const emailmsg = document.getElementById("title-error-message");
        const passmsg = document.getElementById("description-error-message");
        if (inputemail.value == null || inputemail.value == "" || !inputemail.value.includes('@')) {
            e.preventDefault();
            emailmsg.textContent = "Must enter a book title";
        } else {
            emailmsg.textContent = "";
        }
        if (inputpassword.value == null || inputpassword.value == "") {
            e.preventDefault();
            passmsg.textContent = "Must enter a book description";
        } else {
            passmsg.textContent = "";
        }
    });
})