// public/js/dispatch.js
// US 3.1 (Part 2) - "Délivrer 1 boîte" : décrémente instantanément en tâche de fond
// le lot FEFO adéquat et met à jour l'affichage sans recharger le navigateur.
// Dépend de apiFetch() défini dans app.js.

const API_BASE = 'index.php?route=api/v1/';

async function loadDispatchTable() {
    const tbody = document.getElementById('dispatch-tbody');
    const { ok, data } = await apiFetch(API_BASE + 'batches?criteria=all');

    if (!ok || !data || !data.success) {
        tbody.innerHTML = '<tr><td colspan="5">Impossible de charger le stock.</td></tr>';
        return;
    }

    renderProducts(groupByProductFefo(data.data));
}

/**
 * Regroupe les lots par produit et garde, pour chaque produit, le lot FEFO
 * (DLU la plus courte) ainsi que la quantité totale disponible.
 */
function groupByProductFefo(batches) {
    const byProduct = new Map();

    // Les lots sont déjà triés par expiry_date ASC côté API (ordre FEFO).
    batches.forEach(batch => {
        if (batch.quantity <= 0) return;

        if (!byProduct.has(batch.product_id)) {
            byProduct.set(batch.product_id, {
                productId: batch.product_id,
                productName: batch.product_name,
                fefoBatch: batch, // premier lot rencontré = DLU la plus courte
                totalQuantity: 0,
            });
        }

        byProduct.get(batch.product_id).totalQuantity += batch.quantity;
    });

    return Array.from(byProduct.values());
}

function renderProducts(products) {
    const tbody = document.getElementById('dispatch-tbody');

    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Aucun produit en stock.</td></tr>';
        return;
    }

    tbody.innerHTML = '';
    products.forEach(p => tbody.appendChild(buildRow(p)));
}

function buildRow(product) {
    const tr = document.createElement('tr');
    tr.id = 'product-row-' + product.productId;

    tr.innerHTML = `
        <td>${escapeHtml(product.productName || '')}</td>
        <td>${escapeHtml(product.fefoBatch.lot_number)}</td>
        <td class="qty-cell">${product.totalQuantity}</td>
        <td>${formatDate(product.fefoBatch.expiry_date)}</td>
        <td><button type="button" class="btn-action" data-product-id="${product.productId}">Délivrer 1 boîte</button></td>
    `;

    const btn = tr.querySelector('.btn-action');
    btn.addEventListener('click', () => deliverOneBox(product.productId, btn));

    return tr;
}

/**
 * Envoie la requête asynchrone POST vers /api/v1/batches/checkout.
 * Si la quantité totale tombe à 0, la ligne s'efface d'elle-même.
 */
async function deliverOneBox(productId, btn) {
    btn.disabled = true;

    const { ok, data } = await apiFetch(API_BASE + 'batches/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId }),
    });

    btn.disabled = false;

    if (!ok || !data || !data.success) return;

    const row = document.getElementById('product-row-' + productId);
    if (!row) return;

    const qtyCell = row.querySelector('.qty-cell');
    const newTotal = parseInt(qtyCell.textContent, 10) - 1;

    if (newTotal <= 0) {
        row.classList.add('row-faded');
        setTimeout(() => row.remove(), 400);
    } else {
        qtyCell.textContent = newTotal;
    }
}

function formatDate(isoDate) {
    const [y, m, d] = isoDate.split('-');
    return `${d}/${m}/${y}`;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', loadDispatchTable);
