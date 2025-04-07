document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("edit-user-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const editname = document.getElementById("edit-username");
        const oldpassword = document.getElementById("edit-old-password");
        const newpassword = document.getElementById("edit-new-password");
        const confirmpassword = document.getElementById("confirm-new-password");
        const editemail = document.getElementById("edit-email");
        const editnamemsg = document.getElementById("edit-username-error-message");
        const oldpassmsg = document.getElementById("edit-password-error-message");
        const newpassmsg = document.getElementById("edit-new-password-error-message");
        const confirmpassmsg = document.getElementById("edit-confirm-password-error-message");
        const editemailmsg = document.getElementById("edit-email-error-message");
        if (editname.value == null || editname.value == "") {
            e.preventDefault();
            editnamemsg.textContent = "Must enter a user name.";
        } else {
            editnamemsg.textContent = "";
        }
        if (oldpassword.value == null || oldpassword.value == "") {
            e.preventDefault();
            oldpassmsg.textContent = "Must enter a password.";
        } else {
            oldpassmsg.textContent = "";
        }
        if (newpassword.value != null && newpassword.value != "") {
            if (newpassword.value != confirmpassword.value) {
                e.preventDefault();
                confirmpassmsg.textContent = "New password must match confirmed password.";
            } else {
                confirmpassmsg.textContent = "";
            }
        } else {
            confirmpassmsg.textContent = "";
        }
        if (editemail.value == null || editemail.value == "" || !editemail.value.includes('@')) {
            e.preventDefault();
            editemailmsg.textContent = "Must enter an email address.";
        } else {
            editemailmsg.textContent = "";
        }
    });
})