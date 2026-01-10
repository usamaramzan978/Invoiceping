import "./bootstrap";
import "../assets/js/custom";
import { showSuccess, showError } from "./notifications";

document.addEventListener("DOMContentLoaded", () => {
    const successMessage = document.body.dataset.success;
    const errorMessage = document.body.dataset.error;

    if (successMessage) showSuccess(successMessage);
    if (errorMessage) showError(errorMessage);
});
