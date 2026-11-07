/**
 * Soronkénti emlékeztető. Az oldal nem töltődik újra.
 */
(function (window, document) {
  const root = document.getElementById('member-remind');
  if (!root) {
    return;
  }
  const csrf = root.getAttribute('data-csrf') || '';

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-member-remind');
    if (!(button instanceof HTMLButtonElement)) {
      return;
    }
    event.preventDefault();
    const text = 'Elküldöd az emlékeztetőt, hogy még nem jelentkezett a versenyre?';
    const ask = window.Swal
      ? window.Swal.fire({
          title: 'E-mail küldése',
          text: text,
          icon: 'warning',
          iconColor: '#dc3545',
          showCancelButton: true,
          focusCancel: true,
          reverseButtons: true,
          confirmButtonText: 'Igen',
          cancelButtonText: 'Nem',
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          customClass: {
            popup: 'swal2-jeffadmin-delete',
            confirmButton: 'swal2-jeffadmin-delete__confirm',
            cancelButton: 'swal2-jeffadmin-delete__cancel',
          },
        }).then((result) => result.isConfirmed === true)
      : Promise.resolve(window.confirm(text));

    ask.then((confirmed) => {
      if (!confirmed || button.disabled) {
        return;
      }
      send(button);
    });
  });

  function send(button) {
    button.disabled = true;
    const body = new URLSearchParams();
    const competitionId = button.getAttribute('data-competition') || '';
    if (competitionId !== '') {
      body.set('competition_id', competitionId);
    }

    fetch(button.getAttribute('data-url'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': csrf,
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      },
      body: body.toString(),
      credentials: 'same-origin',
    })
      .then((response) => response.json())
      .then((data) => {
        const row = button.closest('tr');
        if (data && data.ok) {
          markRow(row);
          prependStatus(row, data.message, data.sentAt, data.body);
        } else {
          showError(row, (data && data.message) || 'Az emlékeztető nem ment ki.');
        }
      })
      .catch(() => {
        showError(button.closest('tr'), 'Az emlékeztető nem ment ki.');
      })
      .finally(() => {
        button.disabled = false;
      });
  }

  function markRow(row) {
    if (!row) {
      return;
    }
    const table = row.closest('table');
    if (table) {
      table.querySelectorAll('tr.is-last-touched').forEach((item) => {
        item.classList.remove('is-last-touched');
      });
    }
    row.classList.add('is-last-touched');
  }

  function prependStatus(row, message, sentAt, body) {
    const cell = row ? row.querySelector('.js-remind-log') : null;
    if (!cell) {
      return;
    }
    const empty = cell.querySelector('.js-remind-empty');
    if (empty) {
      empty.remove();
    }
    const previousError = cell.querySelector('.js-remind-error');
    if (previousError) {
      previousError.remove();
    }
    const block = document.createElement('div');
    block.className = 'mb-2';
    const title = document.createElement('strong');
    title.textContent = message || 'Emlékeztető e-mail kiküldve';
    const when = document.createElement('div');
    when.textContent = sentAt || '';
    block.append(title, when);
    if (body) {
      const text = document.createElement('div');
      text.className = 'small';
      text.style.whiteSpace = 'pre-wrap';
      text.textContent = body;
      block.append(text);
    }
    cell.prepend(block);
  }

  function showError(row, message) {
    const cell = row ? row.querySelector('.js-remind-log') : null;
    if (!cell) {
      return;
    }
    let error = cell.querySelector('.js-remind-error');
    if (!error) {
      error = document.createElement('div');
      error.className = 'js-remind-error text-danger mb-2';
      cell.prepend(error);
    }
    error.textContent = message;
  }
})(window, document);
