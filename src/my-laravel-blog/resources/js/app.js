import "./bootstrap";
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteModal");
    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const url = button.getAttribute("data-delete-url");
        document.getElementById("delete-form").action = url;
    });
});
