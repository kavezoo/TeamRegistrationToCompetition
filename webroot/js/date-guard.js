/**
 * A magyar dátum (2026. 11. 07.) ne legyen január 1.
 * A flatpickr ebből csak az évet olvassa, és az év január 1-jét írja a mezőbe.
 * A mezőt Y-m-d alakra hozzuk, mielőtt a naptár elindul.
 */
(function (window, document) {
  const hungarian = /^(\d{4})\.\s*(\d{1,2})\.\s*(\d{1,2})\.?(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/;

  const pad = (value) => String(value).padStart(2, '0');

  const toIso = (text) => {
    const match = String(text).trim().match(hungarian);
    if (!match) {
      return null;
    }
    const date = match[1] + '-' + pad(match[2]) + '-' + pad(match[3]);
    if (!match[4]) {
      return date;
    }

    return date + ' ' + pad(match[4]) + ':' + pad(match[5]) + ':' + pad(match[6] || '0');
  };

  const normalize = (el) => {
    if (!el || typeof el.value !== 'string') {
      return;
    }
    const iso = toIso(el.value);
    if (iso) {
      el.value = iso;
    }
  };

  document.querySelectorAll('[data-fp]').forEach(normalize);

  if (typeof window.flatpickr === 'function' && !window.flatpickr._dateGuard) {
    const original = window.flatpickr;
    const wrapped = function (element, config) {
      if (element && element.nodeType === 1) {
        normalize(element);
      }
      return original.call(this, element, config);
    };
    ['l10ns', 'defaultConfig', 'localize', 'setDefaults', 'parseDate', 'formatDate', 'compareDates'].forEach((key) => {
      if (key in original) {
        wrapped[key] = original[key];
      }
    });
    wrapped._dateGuard = true;
    window.flatpickr = wrapped;
  }

  const patch = () => {
    document.querySelectorAll('[data-fp]').forEach((el) => {
      const fp = el._flatpickr;
      if (!fp || fp._dateGuard) {
        return;
      }
      fp._dateGuard = true;
      const originalSet = fp.setDate.bind(fp);
      fp.setDate = function (date, trigger, format) {
        if (typeof date === 'string') {
          const iso = toIso(date);
          if (iso) {
            return originalSet(iso, trigger, iso.length > 10 ? 'Y-m-d H:i:S' : 'Y-m-d');
          }
          const text = date.trim();
          if (text !== '' && !/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}(?::\d{2})?)?$/.test(text)) {
            return;
          }
        }
        return originalSet(date, trigger, format);
      };
    });
  };

  const arm = () => {
    const api = window.JeffAdminDateTime;
    if (api && !api._dateGuard && typeof api.init === 'function') {
      const originalInit = api.init.bind(api);
      api.init = function (root) {
        (root || document).querySelectorAll('[data-fp]').forEach(normalize);
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
