import { test } from 'node:test';
import assert from 'node:assert/strict';
import { formatCreatedAt, initialsOf } from '../../public/assets/js/format.js';

test('shows the creation date in Uruguay time', () => {
  assert.equal(formatCreatedAt('2026-11-27T20:15:00+00:00'), '27/11/2026 17:15');
});

test('shows the date of the previous day when it is still that day in Uruguay', () => {
  assert.equal(formatCreatedAt('2026-11-28T01:30:00+00:00'), '27/11/2026 22:30');
});

test('takes the initials of the first two words of a name', () => {
  assert.equal(initialsOf('Ana María Núñez'), 'AM');
  assert.equal(initialsOf('ángel'), 'Á');
});
