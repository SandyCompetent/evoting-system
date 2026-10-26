'use strict';
/**
 * Replaces inline onclick/onsubmit confirmations with one delegated listener.
 * Add data-confirm="..." to a form or its submit button.
 */
document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    var submitter = event.submitter;
    var message = (submitter && submitter.dataset && submitter.dataset.confirm)
        ? submitter.dataset.confirm
        : (form.dataset ? form.dataset.confirm : '');

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
