import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
window.Notyf = Notyf;

window.notyf = new Notyf({
    duration: 4000,
    dismissible: true,
    position: { x: 'right', y: 'top' },
});

/**
 * Display a success notification
 * @param {string} message
 */
export const showSuccess = (message) => {
    notyf.success(message);
};

/**
 * Display an error notification
 * @param {string} message
 */
export const showError = (message) => {
    notyf.error(message);
};

/**
 * Display an info notification (custom)
 * @param {string} message
 */
export const showInfo = (message) => {
    notyf.open({ type: 'info', message });
};
