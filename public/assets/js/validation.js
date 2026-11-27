// Mismas reglas y mensajes que los Value Objects del servidor (src/Domain/Contact).
// El navegador avisa antes de enviar; la validación que manda es la del servidor.

const NAME_MAX_LENGTH = 100;
const EMAIL_MAX_LENGTH = 254;
const LETTERS_AND_SINGLE_SPACES = /^[A-Za-zÁÉÍÓÚáéíóúÑñÜü]+( [A-Za-zÁÉÍÓÚáéíóúÑñÜü]+)*$/;
const EMAIL_FORMAT = /^[A-Za-z0-9_%+-]+(\.[A-Za-z0-9_%+-]+)*@([A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z]{2,}$/;
const NINE_DIGITS_STARTING_WITH_09 = /^09[0-9]{7}$/;
// Como en el servidor, cualquier espacio Unicode (por ejemplo, el espacio duro) cuenta como un espacio.
const RUNS_OF_SPACES = /\p{Zs}+/gu;

const NAME_RULES = [
  { isBroken: name => name === '', message: 'El nombre es obligatorio.' },
  { isBroken: name => name.length > NAME_MAX_LENGTH, message: `El nombre no puede superar los ${NAME_MAX_LENGTH} caracteres.` },
  { isBroken: name => !LETTERS_AND_SINGLE_SPACES.test(name), message: 'El nombre solo puede contener letras y espacios.' },
];

const EMAIL_RULES = [
  { isBroken: email => email === '', message: 'El email es obligatorio.' },
  { isBroken: email => !isWellFormedEmail(email), message: 'El email no tiene un formato válido.' },
];

const PHONE_RULES = [
  {
    isBroken: phone => !isBlank(phone) && !NINE_DIGITS_STARTING_WITH_09.test(phone),
    message: 'El teléfono debe ser un celular uruguayo de 9 dígitos que empiece con 09, sin espacios ni guiones (por ejemplo: 097680102).',
  },
];

export function validateName(name) {
  return firstBrokenRule(NAME_RULES, normalizeSpaces(name));
}

export function validateEmail(email) {
  return firstBrokenRule(EMAIL_RULES, email.trim());
}

export function validatePhone(phone) {
  return firstBrokenRule(PHONE_RULES, phone);
}

export function validateContact({ name, email, phone }) {
  const errors = { name: validateName(name), email: validateEmail(email), phone: validatePhone(phone) };

  return Object.fromEntries(Object.entries(errors).filter(([, message]) => message !== null));
}

function firstBrokenRule(rules, value) {
  return rules.find(rule => rule.isBroken(value))?.message ?? null;
}

function normalizeSpaces(text) {
  return text.replace(RUNS_OF_SPACES, ' ').trim();
}

function isWellFormedEmail(email) {
  return email.length <= EMAIL_MAX_LENGTH && EMAIL_FORMAT.test(email);
}

function isBlank(text) {
  return text.trim() === '';
}
