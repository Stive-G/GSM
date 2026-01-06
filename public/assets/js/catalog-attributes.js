document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('attributes-container');
  const addBtn = document.getElementById('add-attribute');

  if (!container || !addBtn) return; // page qui n'a pas ce form => on sort

  addBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 attribute-row';
    row.innerHTML = `
      <div class="col-5">
        <input type="text" name="attribute_keys[]" class="form-control" placeholder="Nom (ex: longueur_mm)">
      </div>
      <div class="col-6">
        <input type="text" name="attribute_values[]" class="form-control" placeholder="Valeur (ex: 500)">
      </div>
      <div class="col-1 d-flex align-items-center">
        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-attribute">
          <i class="fa fa-times"></i>
        </button>
      </div>
    `;
    container.appendChild(row);
  });

  container.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-remove-attribute');
    if (!btn) return;
    btn.closest('.attribute-row')?.remove();
  });
});
