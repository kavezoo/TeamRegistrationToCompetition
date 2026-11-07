/**
 * Igen-nem kérdés a mentő űrlapon, a JeffAdmin SweetAlert ablakával.
 * A gombos kérdéseket a JeffAdmin confirm-delete.js kezeli.
 */
(function (window, document) {
  document.addEventListener(
    'submit',
    (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement) || form.dataset.swalConfirmed === '1') {
        return;
      }
      const text = form.getAttribute('data-swal-text');
      if (!text) {
        return;
      }
      event.preventDefault();
      event.stopPropagation();

      const ask = window.Swal
        ? window.Swal.fire({
            title: form.getAttribute('data-swal-title') || 'Biztosan?',
            text: text,
            icon: 'warning',
            iconColor: '#dc3545',
            showCancelButton: true,
            focusCancel: true,
            reverseButtons: true,
            confirmButtonText: form.getAttribute('data-swal-confirm') || 'Igen',
            cancelButtonText: form.getAttribute('data-swal-cancel') || 'Nem',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            customClass: {
              popup: 'swal2-jeffadmin-delete',
              confirmButton: 'swal2-jeffadmin-delete__confirm',
              cancelButton: 'swal2-jeffadmin-delete__cancel',
            },
          }).then((result) => result.isConfirmed === true)
        : Promise.resolve(window.confirm(text));

      ask.then((ok) => {
        if (!ok) {
          return;
        }
        form.dataset.swalConfirmed = '1';
        form.requestSubmit();
      });
    },
    true
  );
})(window, document);
