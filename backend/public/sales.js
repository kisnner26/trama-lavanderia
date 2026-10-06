const lines = document.querySelector('#sale-lines');
let nextLine = lines?.children.length ?? 0;
document.querySelector('#add-line')?.addEventListener('click', () => {
    if (lines.children.length >= 20) return;
    const row = lines.firstElementChild.cloneNode(true);
    row.querySelector('legend').textContent = `servicio ${nextLine + 1}`;
    for (const field of row.querySelectorAll('input, select')) {
        const oldId = field.id;
        field.id = oldId.replace(/-\d+$/, `-${nextLine}`);
        row.querySelector(`label[for="${oldId}"]`).htmlFor = field.id;
        field.name = field.name.replace(/\[\d+\]/, `[${nextLine}]`);
        field.value = field.tagName === 'INPUT' ? '1' : '';
    }
    nextLine++;
    lines.append(row);
    row.querySelector('select').focus();
});
lines?.addEventListener('click', (event) => {
    if (!event.target.closest('.remove-line') || lines.children.length === 1) return;
    event.target.closest('.sale-line').remove();
    document.querySelector('#add-line').focus();
});
document.querySelector('#print-receipt')?.addEventListener('click', () => window.print());
document.querySelector('#receipt-size')?.addEventListener('change', (event) => {
    document.body.classList.toggle('thermal-receipt', event.target.value === 'thermal');
});
