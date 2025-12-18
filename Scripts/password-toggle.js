/**
 * Password visibility toggles.
 * Usage:
 * - Add a button with class "password-toggle" inside the same ".input-group" as the password input.
 * - Keep the <label> immediately after the <input> to preserve floating-label CSS selectors.
 */

(function initPasswordToggles() {
  const toggles = document.querySelectorAll('.password-toggle');
  if (!toggles.length) return;

  toggles.forEach((btn) => {
    const group = btn.closest('.input-group');
    const input =
      (group && group.querySelector('input[type="password"], input[data-password-field="true"]')) ||
      document.querySelector(btn.getAttribute('data-target') || '');

    if (!input) return;

    const setState = (isVisible) => {
      input.type = isVisible ? 'text' : 'password';
      btn.classList.toggle('is-visible', isVisible);
      btn.setAttribute('aria-pressed', String(isVisible));
      btn.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
      btn.title = isVisible ? 'Hide password' : 'Show password';
    };

    // initial
    setState(false);

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const isVisible = input.type === 'password';
      setState(isVisible);
      input.focus({ preventScroll: true });
      // Keep cursor at end for better UX
      try {
        const len = input.value.length;
        input.setSelectionRange(len, len);
      } catch {
        // ignore
      }
    });
  });
})();


