import { test } from 'node:test';
import assert from 'node:assert/strict';
import { validateContact, validateEmail, validateName, validatePhone } from '../../public/assets/js/validation.js';

const INVALID_PHONE =
  'El teléfono debe ser un celular uruguayo de 9 dígitos que empiece con 09, sin espacios ni guiones (por ejemplo: 097680102).';

test('accepts names with letters, accents, ñ, ü and spaces', () => {
  for (const name of ['Ana', 'María José Núñez', 'ÁNGEL ÑANDÚ', 'Agüero', '  Ana   María ']) {
    assert.equal(validateName(name), null, name);
  }
});

test('treats unicode spaces, like the non-breaking space, as spaces, as the server does', () => {
  assert.equal(validateName(' Ana  María '), null);
  assert.equal(validateName('Ana María　'), null);
  assert.equal(validateName('  '), 'El nombre es obligatorio.');
  assert.equal(validateEmail(' ana@mail.com '), null);
  assert.equal(validatePhone('  '), null);
});

test('rejects an empty name, including one made only of spaces', () => {
  assert.equal(validateName(''), 'El nombre es obligatorio.');
  assert.equal(validateName('   '), 'El nombre es obligatorio.');
});

test('rejects names with characters other than letters and spaces', () => {
  for (const name of ['Ana3', "O'Connor", 'María-José', 'Ana.', '<script>']) {
    assert.equal(validateName(name), 'El nombre solo puede contener letras y espacios.', name);
  }
});

test('rejects names longer than 100 characters', () => {
  assert.equal(validateName('a'.repeat(100)), null);
  assert.equal(validateName('a'.repeat(101)), 'El nombre no puede superar los 100 caracteres.');
});

test('accepts well-formed emails', () => {
  for (const email of ['ana@mail.com', 'ana.perez+test@mail.com.uy', 'Ana@Mail.COM', '  ana@mail.com  ']) {
    assert.equal(validateEmail(email), null, email);
  }
});

test('limits the local part to 64 characters and each domain label to 63, as the server does', () => {
  assert.equal(validateEmail(`${'a'.repeat(64)}@mail.com`), null);
  assert.equal(validateEmail(`ana@${'b'.repeat(63)}.com`), null);
  assert.equal(validateEmail(`${'a'.repeat(65)}@mail.com`), 'El email no tiene un formato válido.');
  assert.equal(validateEmail(`ana@${'b'.repeat(64)}.com`), 'El email no tiene un formato válido.');
});

test('rejects an empty email', () => {
  assert.equal(validateEmail('  '), 'El email es obligatorio.');
});

test('rejects malformed emails', () => {
  const malformed = ['ana.mail.com', 'ana@mail', 'ana@@mail.com', 'ana..perez@mail.com', '.ana@mail.com', 'ana@-mail.com', 'ana@mail.c', 'maría@mail.com', `${'a'.repeat(246)}@mail.com`];
  for (const email of malformed) {
    assert.equal(validateEmail(email), 'El email no tiene un formato válido.', email);
  }
});

test('accepts an empty phone because it is optional', () => {
  assert.equal(validatePhone(''), null);
  assert.equal(validatePhone('   '), null);
});

test('accepts nine digits starting with 09', () => {
  assert.equal(validatePhone('097680102'), null);
});

test('rejects any other phone format', () => {
  for (const phone of ['97680102', '0976801020', '087680102', '097 680 102', '097-680-102', '+59897680102', ' 097680102']) {
    assert.equal(validatePhone(phone), INVALID_PHONE, phone);
  }
});

test('reports only the invalid fields of a contact', () => {
  assert.deepEqual(validateContact({ name: 'Ana', email: 'ana@', phone: '' }), { email: 'El email no tiene un formato válido.' });
  assert.deepEqual(validateContact({ name: 'Ana', email: 'ana@mail.com', phone: '' }), {});
});
