export const stages = ['recibida', 'clasificada', 'en lavado', 'en secado', 'en acabado', 'lista', 'entregada'];

export function advance(stage) {
  const index = stages.indexOf(stage);
  if (index < 0) throw new Error('estado desconocido');
  return stages[Math.min(index + 1, stages.length - 1)];
}

export function summarize(pieces) {
  if (!pieces.length) return 'sin prendas';
  if (pieces.some(piece => !stages.includes(piece.stage))) throw new Error('estado desconocido');
  const delivered = pieces.filter(piece => piece.stage === 'entregada').length;
  if (delivered === pieces.length) return 'entregada';
  if (delivered) return 'entrega parcial';
  if (pieces.every(piece => piece.stage === 'lista')) return 'lista para retirar';
  if (pieces.every(piece => piece.stage === 'recibida')) return 'recibida';
  return 'en proceso';
}
