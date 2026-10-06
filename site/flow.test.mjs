import test from 'node:test';
import assert from 'node:assert/strict';
import { advance, stages, summarize } from './flow.mjs';

test('una prenda avanza por todas las etapas y no sale de entregada', () => {
  for (let index = 0; index < stages.length; index++) {
    assert.equal(advance(stages[index]), stages[Math.min(index + 1, stages.length - 1)]);
  }
  assert.throws(() => advance('desconocido'));
});

test('la orden solo está lista cuando todas sus prendas están listas', () => {
  assert.equal(summarize([{ stage: 'lista' }, { stage: 'en secado' }]), 'en proceso');
  assert.equal(summarize([{ stage: 'lista' }, { stage: 'lista' }]), 'lista para retirar');
});

test('la entrega parcial no marca la orden completa', () => {
  assert.equal(summarize([{ stage: 'entregada' }, { stage: 'lista' }]), 'entrega parcial');
  assert.equal(summarize([{ stage: 'entregada' }, { stage: 'entregada' }]), 'entregada');
  assert.equal(summarize([]), 'sin prendas');
  assert.throws(() => summarize([{ stage: 'inexistente' }]));
});
