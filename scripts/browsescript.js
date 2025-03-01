document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("search-form");
    form.setAttribute("novalidate", true);
    form.addEventListener("submit", (e) => {
        const input = document.getElementById("search");
        const msg = document.getElementById("search-error-message");
        if (input.value == null || input.value == "") {
            e.preventDefault();
            msg.textContent = "Must enter search string";
        } else {
            passmsg.textContent = "";
        }
    });
})