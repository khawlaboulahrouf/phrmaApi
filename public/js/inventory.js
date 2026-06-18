

const API_BASE = 'index.php?route=api/v1/';

async function loadInventory() {
    const tbody = document.getElementById('inventory-tbody');
    const { ok, data } = await apiFetch(API_BASE + 'batches?criteria=all');

    if (!ok || !data || !data.success) {
        tbody.innerHTML = '<tr><td colspan="7">Impossible de charger l\'inventaire.</td></tr>';
        return;
    }

    renderInventory(data.data);
}

function renderInventory(batches) {
    const tbody = document.getElementById('inventory-tbody');

    if (batches.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">Aucun lot en stock.</td></tr>';
        return;
    }

    tbody.innerHTML = '';
    batches.forEach(batch => tbody.appendChild(buildRow(batch)));
}

function buildRow(batch) {
    const tr = document.createElement('tr');
    tr.id = 'batch-row-' + batch.id;
    tr.className = 'row-' + batch.criticality.toLowerCase();

    const actionHtml = buildActionCell(batch);

    tr.innerHTML = `
        <td>${escapeHtml(batch.lot_number)}</td>
        <td>${escapeHtml(batch.product_name || '')}</td>
        <td>${batch.quantity}</td>
        <td>${formatDate(batch.expiry_date)}</td>
        <td>${batch.days_to_expiry} j</td>
        <td><span class="badge badge-${batch.criticality.toLowerCase()}">${batch.criticality}</span></td>
        <td>${actionHtml}</td>
    `;

    const btn = tr.querySelector('.btn-action');
    if (btn) {
        btn.addEventListener('click', () => handleAction(btn.dataset.action, batch.id));
    }

    return tr;
}

function buildActionCell(batch) {
    if (batch.criticality === 'EXPIRED') {
        return `<button type="button" class="btn-action" data-action="declare-expired">Déclarer périmé</button>`;
    }
    if (batch.criticality === 'WARNING' || batch.criticality === 'CRITICAL') {
        return `<button type="button" class="btn-action" data-action="return-supplier">Retour fournisseur</button>`;
    }
    return '<em>—</em>';
}


async function handleAction(action, batchId) {
    const route = action === 'declare-expired'
        ? `batches/${batchId}/expire`
        : `batches/${batchId}/return-supplier`;

    const { ok, data } = await apiFetch(API_BASE + route, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ batch_id: batchId }),
    });

    if (!ok || !data || !data.success) return;

    const row = document.getElementById('batch-row-' + batchId);
    if (row) {
        row.classList.add('row-faded');
        setTimeout(() => row.remove(), 400);
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

document.addEventListener('DOMContentLoaded', loadInventory);
