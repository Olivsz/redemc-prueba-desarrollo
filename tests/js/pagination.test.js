import { test } from 'node:test';
import assert from 'node:assert/strict';
import { visiblePages } from '../../public/assets/js/pagination.js';

test('shows every page when there are few of them', () => {
  assert.deepEqual(visiblePages(1, 3), [1, 2, 3]);
});

test('shows a single page when there is only one', () => {
  assert.deepEqual(visiblePages(1, 1), [1]);
});

test('shows at most five pages, starting at the first one', () => {
  assert.deepEqual(visiblePages(1, 10), [1, 2, 3, 4, 5]);
});

test('keeps the current page in the middle', () => {
  assert.deepEqual(visiblePages(6, 10), [4, 5, 6, 7, 8]);
});

test('ends at the last page', () => {
  assert.deepEqual(visiblePages(10, 10), [6, 7, 8, 9, 10]);
});
