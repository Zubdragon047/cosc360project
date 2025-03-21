document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const inputname = document.getElementById("login-username");
        const inputpassword = document.getElementById("login-password");
        const namemsg = document.getElementById("name-error-message");
        const passmsg = document.getElementById("password-error-message");
        if (inputname.value == null || inputname.value == "") {
            e.preventDefault();
            namemsg.textContent = "Must enter an email address";
        } else {
            namemsg.textContent = "";
        }
        if (inputpassword.value == null || inputpassword.value == "") {
            e.preventDefault();
            passmsg.textContent = "Must enter a password";
        } else {
            passmsg.textContent = "";
        }
    });
})