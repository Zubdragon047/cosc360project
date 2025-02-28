document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const inputemail = document.getElementById("login-email");
        const inputpassword = document.getElementById("login-password");
        const emailmsg = document.getElementById("email-error-message");
        const passmsg = document.getElementById("password-error-message");
        if (inputemail.value == null || inputemail.value == "" || !inputemail.value.includes('@')) {
            e.preventDefault();
            emailmsg.textContent = "Must enter an email address";
        } else {
            emailmsg.textContent = "";
        }
        if (inputpassword.value == null || inputpassword.value == "") {
            e.preventDefault();
            passmsg.textContent = "Must enter a password";
        } else {
            passmsg.textContent = "";
        }
    });
})