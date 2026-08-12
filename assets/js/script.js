document.addEventListener("click", function (event) {
    const deleteButton = event.target.closest("[data-confirm]");

    if (deleteButton) {
        const message = deleteButton.getAttribute("data-confirm");

        if (!confirm(message)) {
            event.preventDefault();
        }
    }
});
