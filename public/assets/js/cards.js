document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('toggleCards');
  const viewTable = document.getElementById('viewTable');
  const viewCards = document.getElementById('viewCards');

  if (!btn || !viewTable || !viewCards) return;

  const editTpl = btn.dataset.editTpl || ''; // ex: /admin/catalog/products/___ID___/edit
  let loaded = false;
  let cardsVisible = false;

  // Bootstrap modal
  const modalEl = document.getElementById('productDetailsModal');
  const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

  const $ = (id) => document.getElementById(id);

  const setText = (id, v) => {
    const el = $(id);
    if (el) el.textContent = (v ?? '').toString();
  };

  const money = (v) => {
    const n = Number(v ?? 0);
    return Number.isFinite(n) ? n.toFixed(2).replace('.', ',') : '0,00';
  };

  const escapeHtml = (str) =>
    String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');

  const openDetails = (p) => {
    if (!modal) return;

    $('productDetailsTitle').textContent = p.label || 'Détails produit';
    setText('productDetailsSku', p.sku || '-');

    //Catégorie: nom si possible, sinon id
    setText('productDetailsCategory',p.categoryName || '-');
    setText('productDetailsUnit', p.unit || '-');
    setText('productDetailsHt', money(p.price_ht));
    setText('productDetailsTtc', money(p.price_ttc));

    const desc = $('productDetailsDesc');
    if (desc) desc.textContent = p.description || '-';

    // attrs
    const attrsWrap = $('productDetailsAttrs');
    if (attrsWrap) {
      const attrs = p.attributes || {};
      const keys = Object.keys(attrs);
      if (!keys.length) {
        attrsWrap.innerHTML = '<span class="text-muted">Aucune</span>';
      } else {
        attrsWrap.innerHTML = keys
          .map(
            (k) =>
              `<div><code>${escapeHtml(k)}</code> : ${escapeHtml(String(attrs[k]))}</div>`
          )
          .join('');
      }
    }

    // image principale
    const img = $('productDetailsImg');
    const noImg = $('productDetailsNoImg');
    const images = Array.isArray(p.images) ? p.images : [];

    if (images.length && img) {
      img.src = images[0];
      img.alt = p.label || '';
      img.classList.remove('d-none');
      if (noImg) noImg.classList.add('d-none');
    } else {
      if (img) img.classList.add('d-none');
      if (noImg) noImg.classList.remove('d-none');
    }

    // gallery
    const gal = $('productDetailsGallery');
    if (gal) {
      gal.innerHTML = images.slice(0, 12).map((url) => `
        <a href="${url}" target="_blank" rel="noreferrer">
          <img src="${url}" style="width:64px;height:64px;object-fit:cover" class="rounded border"/>
        </a>
      `).join('');
    }

    //lien edit
    const editBtn = $('productDetailsEditBtn');
    if (editBtn && editTpl && p.id) {
      editBtn.href = editTpl.replace('___ID___', p.id);
      editBtn.classList.remove('disabled');
      editBtn.removeAttribute('aria-disabled');
    } else if (editBtn) {
      editBtn.href = '#';
      editBtn.classList.add('disabled');
      editBtn.setAttribute('aria-disabled', 'true');
    }

    modal.show();
  };

  const wireDetailsButtons = (root) => {
    root.querySelectorAll('.btn-details').forEach((b) => {
      b.addEventListener('click', (e) => {
        const cardOrRow = e.currentTarget.closest('[data-product]');
        if (!cardOrRow) return;

        try {
          const data = JSON.parse(cardOrRow.getAttribute('data-product'));
          openDetails(data);
        } catch (err) {
          console.error('Invalid data-product JSON', err);
        }
      });
    });
  };

  // table déjà chargé
  wireDetailsButtons(document);

  btn.addEventListener('click', async () => {
    cardsVisible = !cardsVisible;

    viewTable.classList.toggle('d-none', cardsVisible);
    viewCards.classList.toggle('d-none', !cardsVisible);

    btn.innerHTML = cardsVisible
      ? '<i class="fa fa-list me-1"></i>Vue tableau'
      : '<i class="fa fa-th-large me-1"></i>Vue cartes';

    if (cardsVisible && !loaded) {
      const url = viewCards.dataset.url;
      try {
        const res = await fetch(url);
        viewCards.innerHTML = await res.text();
        wireDetailsButtons(viewCards);
        loaded = true;
      } catch (e) {
        viewCards.innerHTML =
          '<div class="text-danger text-center py-5">Erreur de chargement</div>';
      }
    }
  });
});
