import { advance, stages, summarize } from './flow.mjs';

const pieces = [
  { name: 'camisa de lino', stage: 'recibida', instruction: 'revisar la etiqueta y confirmar el tratamiento antes de lavar.' },
  { name: 'pantalón de algodón', stage: 'recibida', instruction: 'registrar y revisar la mancha señalada en el bolsillo antes del tratamiento.' },
];
let selected = 0;
const buttons = [...document.querySelectorAll('[data-piece]')];
const nextButton = document.querySelector('#advance');
const feedback = document.querySelector('#feedback');

function render() {
  const current = pieces[selected];
  buttons.forEach((button, index) => {
    button.setAttribute('aria-pressed', String(index === selected));
    button.querySelector('.piece-state').textContent = pieces[index].stage;
  });
  document.querySelector('#piece-title').textContent = current.name;
  document.querySelector('#piece-id').textContent = `tr-024 / 0${selected + 1}`;
  document.querySelector('#piece-instruction').textContent = `instrucción registrada: ${current.instruction}`;
  document.querySelector('#order-status').textContent = summarize(pieces);
  const list = document.querySelector('#stages');
  list.replaceChildren(...stages.map((stage, index) => {
    const item = document.createElement('li');
    item.textContent = stage;
    if (stage === current.stage) item.setAttribute('aria-current', 'step');
    if (index < stages.indexOf(current.stage)) item.className = 'completed';
    return item;
  }));
  nextButton.disabled = current.stage === 'entregada';
  nextButton.textContent = nextButton.disabled ? 'prenda entregada' : `pasar a ${advance(current.stage)}`;
}

buttons.forEach((button, index) => button.addEventListener('click', () => {
  selected = index;
  render();
  feedback.textContent = `${pieces[selected].name}: ${pieces[selected].stage}.`;
}));
nextButton.addEventListener('click', () => {
  pieces[selected].stage = advance(pieces[selected].stage);
  render();
  feedback.textContent = `${pieces[selected].name}: ${pieces[selected].stage}. orden ${summarize(pieces)}.`;
});
document.querySelector('#reset').addEventListener('click', () => {
  pieces.forEach(piece => { piece.stage = 'recibida'; });
  selected = 0;
  render();
  feedback.textContent = 'ejemplo reiniciado. las dos prendas están recibidas.';
});
render();
