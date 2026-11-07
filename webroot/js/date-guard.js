/**
 * Hiányos dátumból a naptár ne csináljon január 1-jét.
 * A flatpickr év-only szövegből az aktuális év január 1-jét írja a mezőbe.
 */
(function (window, document) {
  const complete = /^\d{4}[.\-/]\d{1,2}[.\-/]\d{1,2}\.?(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?$/;

  const patch = () => {
    document.querySelectorAll('[data-fp]').forEach((el) => {
      const fp = el._flatpickr;
      if (!fp || fp._dateGuard) {
        return;
      }
      fp._dateGuard = true;
      const original = fp.setDate.bind(fp);
      fp.setDate = function (date, trigger, format) {
        if (typeof date === 'string') {
          const text = date.trim();
          if (text !== '' && !complete.test(text)) {
            fp.input.value = '';
            if (fp.altInput) {
              fp.altInput.value = text;
            }
            return;
          }
        }
        return original(date, trigger, format);
      };
    });
  };

  const arm = () => {
    const api = window.JeffAdminDateTime;
    if (api && !api._dateGuard) {
      const originalInit = api.init.bind(api);
      api.init = function (root) {
        const result = originalInit(root);
        patch();
        return result;
      };
      api._dateGuard = true;
    }
    patch();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', arm);
  } else {
    arm();
  }
})(window, document);
