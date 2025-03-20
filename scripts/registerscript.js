document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("register-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const inputname = document.getElementById("register-username");
        const inputpassword = document.getElementById("register-password");
        const inputemail = document.getElementById("register-email");
        const namemsg = document.getElementById("username-error-message");
        const passmsg = document.getElementById("password-error-message");
        const emailmsg = document.getElementById("email-error-message");
        if (inputname.value == null || inputname.value == "") {
            e.preventDefault();
            namemsg.textContent = "Must enter a user name.";
        } else {
            namemsg.textContent = "";
        }
        if (inputpassword.value == null || inputpassword.value == "") {
            e.preventDefault();
            passmsg.textContent = "Must enter a password.";
        } else {
            passmsg.textContent = "";
        }
        if (inputemail.value == null || inputemail.value == "" || !inputemail.value.includes('@')) {
            e.preventDefault();
            emailmsg.textContent = "Must enter an email address.";
        } else {
            emailmsg.textContent = "";
        }
    });
})