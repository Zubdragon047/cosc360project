document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("post-discussion-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const input = document.getElementById("comment");
        const msg = document.getElementById("comment-error-message");
        if (input.value == null || input.value == "") {
            e.preventDefault();
            msg.textContent = "Must enter a comment";
        } else {
            msg.textContent = "";
        }
    });
})