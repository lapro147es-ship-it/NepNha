'use strict';
document.querySelector('.public-menu-toggle')?.addEventListener('click', (event) => {
  const open = document.querySelector('#public-nav').classList.toggle('open');
  event.currentTarget.setAttribute('aria-expanded', String(open));
});
document
  .querySelector('[data-print], .print-button')
  ?.addEventListener('click', () => window.print());
document.querySelector('.mobile-toggle')?.addEventListener('click', (event) => {
  const open = document.querySelector('#sidebar').classList.toggle('open');
  event.currentTarget.setAttribute('aria-expanded', String(open));
});
document
  .querySelectorAll('.dismiss')
  .forEach((button) => button.addEventListener('click', () => button.parentElement.remove()));
document.querySelectorAll('form[data-confirm]').forEach((form) =>
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  }),
);
const lines = document.querySelector('#quote-lines');
function calculateQuote() {
  if (!lines) return;
  let total = 0;
  lines.querySelectorAll('.quote-line').forEach((row) => {
    const quantity = Number(row.querySelector('[name="quantity[]"]')?.value || 0);
    const price = Number(row.querySelector('[name="unit_price[]"]')?.value || 0);
    total += Math.round(quantity * price);
  });
  document.querySelector('#quote-total').textContent = new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    maximumFractionDigits: 0,
  }).format(total);
}
document.querySelector('#add-line')?.addEventListener('click', () => {
  if (lines.children.length >= 30) return;
  const row = document.querySelector('#quote-line-template').content.cloneNode(true);
  lines.appendChild(row);
  calculateQuote();
});
lines?.addEventListener('input', calculateQuote);
lines?.addEventListener('click', (event) => {
  if (event.target.matches('.remove-line') && lines.children.length > 1) {
    event.target.closest('.quote-line').remove();
    calculateQuote();
  }
});
calculateQuote();
document.querySelectorAll('input[type="file"]').forEach((input) =>
  input.addEventListener('change', () => {
    if (input.files[0]?.size > 10 * 1024 * 1024) {
      input.value = '';
      input.setCustomValidity('Tệp tối đa 10 MB.');
      input.reportValidity();
    } else input.setCustomValidity('');
  }),
);
document.querySelectorAll('form').forEach((form) =>
  form.addEventListener('submit', () => {
    if (!form.checkValidity()) return;
    // Keep submit button values available for the request; disable only after browser serialization.
    window.setTimeout(
      () =>
        form.querySelectorAll('button[type="submit"]').forEach((b) => {
          b.disabled = true;
        }),
      0,
    );
  }),
);

document.querySelectorAll('[data-quote-feedback]').forEach((button) => {
  button.addEventListener('click', () => {
    const field = document.querySelector('textarea[name="feedback"]');
    if (!field) return;
    field.value = field.value.trim()
      ? field.value.trim() + '\n' + button.dataset.quoteFeedback
      : button.dataset.quoteFeedback;
    field.focus();
  });
});
const quoteFile = document.querySelector('.quote-editor input[type="file"]');
quoteFile?.addEventListener('change', () => {
  const description = document.querySelector('[name="upload_description"]');
  if (description) description.required = quoteFile.files.length > 0;
});
