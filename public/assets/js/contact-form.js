import { createContact } from './contacts-api.js';
import { validateContact, validateEmail, validateName, validatePhone } from './validation.js';

const FIELD_VALIDATORS = { name: validateName, email: validateEmail, phone: validatePhone };
const UNEXPECTED_ERROR = 'No pudimos guardar el contacto. Revisá tu conexión e intentá de nuevo.';
// Al salir de un campo solo se valida si el usuario lo editó, y un error del servidor
// (por ejemplo, un email repetido) queda marcado hasta que el usuario vuelve a editarlo.
const EDITED = 'edited';
const SERVER_ERROR = 'server-error';

export function initContactForm({ onContactCreated }) {
  const $form = $('#contact-form');
  const $inputs = $form.find('input');

  $form.on('submit', event => {
    event.preventDefault();
    submit($form, onContactCreated);
  });
  $inputs.on('blur', event => validateIfEdited($(event.currentTarget)));
  $inputs.on('input', event => registerEdit($(event.currentTarget)));
}

function registerEdit($input) {
  $input.data(EDITED, true).removeData(SERVER_ERROR);
  revalidateIfInvalid($input);
}

function submit($form, onContactCreated) {
  hideFormAlert();

  const contact = readContact($form);
  const errors = validateContact(contact);

  if (hasErrors(errors)) {
    showFieldErrors($form, errors);
    return;
  }

  save($form, contact, onContactCreated);
}

function save($form, contact, onContactCreated) {
  setSaving($form, true);

  createContact(contact)
    .done(response => {
      resetForm($form);
      onContactCreated(response.data);
    })
    .fail(request => showServerErrors($form, request.responseJSON))
    .always(() => setSaving($form, false));
}

function showServerErrors($form, body) {
  if (body?.errors) {
    showFieldErrorsFromServer($form, body.errors);
    return;
  }

  showFormAlert(body?.error ?? UNEXPECTED_ERROR);
}

function showFieldErrorsFromServer($form, errors) {
  Object.keys(errors).forEach(field => inputOf($form, field).data(SERVER_ERROR, true));
  showFieldErrors($form, errors);
}

function readContact($form) {
  return {
    name: inputOf($form, 'name').val(),
    email: inputOf($form, 'email').val(),
    phone: inputOf($form, 'phone').val(),
  };
}

function validateIfEdited($input) {
  if (isEditedWithoutServerError($input)) {
    validateField($input);
  }
}

function isEditedWithoutServerError($input) {
  return $input.data(EDITED) === true && $input.data(SERVER_ERROR) !== true;
}

function validateField($input) {
  const message = FIELD_VALIDATORS[$input.attr('name')]($input.val());

  if (message === null) {
    markAsValid($input);
    return;
  }

  markAsInvalid($input, message);
}

function revalidateIfInvalid($input) {
  if ($input.hasClass('is-invalid')) {
    validateField($input);
  }
}

function showFieldErrors($form, errors) {
  Object.entries(errors).forEach(([field, message]) => markAsInvalid(inputOf($form, field), message));
  $form.find('.is-invalid').first().trigger('focus');
}

function inputOf($form, field) {
  return $form.find(`[name="${field}"]`);
}

function markAsInvalid($input, message) {
  $input.removeClass('is-valid').addClass('is-invalid').attr('aria-invalid', 'true');
  errorElementOf($input).text(message);
}

function markAsValid($input) {
  $input.removeClass('is-invalid').removeAttr('aria-invalid').toggleClass('is-valid', $input.val().trim() !== '');
  errorElementOf($input).text('');
}

function errorElementOf($input) {
  return $(`#${$input.attr('name')}-error`);
}

function resetForm($form) {
  $form[0].reset();
  $form.find('input').removeClass('is-valid is-invalid').removeAttr('aria-invalid').removeData([EDITED, SERVER_ERROR]);
  $form.find('.invalid-feedback').text('');
  inputOf($form, 'name').trigger('focus');
}

function setSaving($form, isSaving) {
  const $button = $form.find('[type="submit"]');

  $button.prop('disabled', isSaving).attr('aria-busy', String(isSaving));
  $button.find('.spinner-border').toggleClass('d-none', !isSaving);
  $button.find('.button-label').text(isSaving ? 'Guardando…' : 'Guardar contacto');
}

function showFormAlert(message) {
  $('#form-alert').text(message).removeClass('d-none');
}

function hideFormAlert() {
  $('#form-alert').text('').addClass('d-none');
}

function hasErrors(errors) {
  return Object.keys(errors).length > 0;
}
