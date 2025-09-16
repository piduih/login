import { test } from 'node:test';
import assert from 'node:assert';
import { validateEmail, validatePassword } from '../src/utils/validation.js';

test('Email validation', () => {
  assert.strictEqual(validateEmail('test@example.com'), true);
  assert.strictEqual(validateEmail('invalid-email'), false);
  assert.strictEqual(validateEmail(''), false);
  assert.strictEqual(validateEmail('test@'), false);
  assert.strictEqual(validateEmail('@example.com'), false);
});

test('Password validation', () => {
  assert.strictEqual(validatePassword('password123'), true);
  assert.strictEqual(validatePassword('123456'), true);
  assert.strictEqual(validatePassword('12345'), false);
  assert.strictEqual(validatePassword(''), false);
  assert.strictEqual(validatePassword(null), false);
  assert.strictEqual(validatePassword(undefined), false);
});