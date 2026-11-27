import { test } from 'node:test';
import assert from 'node:assert/strict';
import { trackLatestRequest } from '../../public/assets/js/latest-request.js';

test('runs the callbacks of the latest request with their arguments', () => {
  const latestRequest = trackLatestRequest();
  const calls = [];

  const onlyIfLatest = latestRequest.start();
  onlyIfLatest((first, second) => calls.push([first, second]))('page 2', 'ok');

  assert.deepEqual(calls, [['page 2', 'ok']]);
});

test('ignores the response of a request that a newer one replaced, even if it arrives last', () => {
  const latestRequest = trackLatestRequest();
  const calls = [];

  const pageTwo = latestRequest.start();
  const pageThree = latestRequest.start();
  pageThree(() => calls.push('page 3'))();
  pageTwo(() => calls.push('page 2'))();

  assert.deepEqual(calls, ['page 3']);
});
