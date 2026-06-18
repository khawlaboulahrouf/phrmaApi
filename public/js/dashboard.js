

const API_BASE = 'index.php?route=api/v1/';

let currentCriteria = 'all';


async function loadSummary() {
    const { ok, data } = await apiFetch(API_BASE + 'dashboard/summary');
    if (!ok || !data || !data.success) return;

    document.getElementById('count-ok').textContent = data.data.ok_count;
    document.getElementById('count-warning').textContent = data.data.warning_count;
    document.getElementById('count-critical').textContent = data.data.critical_count;
    document.getElementById('count-expiring').textContent = data.data.expiring_next_month;
}


async function loadBatches(criteria = 'all') {
    currentCriteria = criteria;
    const tbody = document.getElementById('batches-tbody');
    tbody.innerHTML = '<tr><td colspan="6">Chargement…</td></tr>';

    const { ok, data } = await apiFetch(API_BASE + 'batches?criteria=' + encodeURIComponent(criteria));

    if (!ok || !data || !data.success) {
        tbody.innerHTML = '<tr><td colspan="6">Impossible de charger les lots.</td></tr>';
        return;
    }

    renderBatches(data.data);
}

function renderBatches(batches) {
    const tbody = document.getElementById('batches-tbody');

    if (batches.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">Aucun lot pour ce filtre.</td></tr>';
        return;
    }

    tbody.innerHTML = '';

    batches.forEach(batch => {
        tbody.appendChild(buildBatchRow(batch));
    });
}


function buildBatchRow(batch) {
    const tr = document.createElement('tr');
    tr.id = 'batch-row-' + batch.id;
    tr.className = 'row-' + batch.criticality.toLowerCase();

    tr.innerHTML = `
        <td>${escapeHtml(batch.lot_number)}</td>
        <td>${escapeHtml(batch.product_name || '')}</td>
        <td class="qty-cell">${batch.quantity}</td>
        <td>${formatDate(batch.expiry_date)}</td>
        <td>${batch.days_to_expiry} j</td>
        <td><span class="badge badge-${batch.criticality.toLowerCase()}">${batch.criticality}</span></td>
    `;

    return tr;
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


async function checkoutOneUnit(productId, rowId) {
    const { ok, data } = await apiFetch(API_BASE + 'batches/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId }),
    });

    if (!ok || !data || !data.success) return;

    const batch = data.data;
    const row = document.getElementById(rowId);
    if (!row) return;

    if (batch.quantity <= 0) {
        row.classList.add('row-faded');
        setTimeout(() => row.remove(), 400);
    } else {
        const qtyCell = row.querySelector('.qty-cell');
        if (qtyCell) qtyCell.textContent = batch.quantity;
    }
}


async function declareExpired(batchId) {
    const { ok, data } = await apiFetch(API_BASE + 'batches/' + batchId + '/expire', {
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

document.addEventListener('DOMContentLoaded', () => {
    loadSummary();
    loadBatches('all');

    document.querySelectorAll('#filter-bar .filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#filter-bar .filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadBatches(btn.dataset.criteria);
        });
    });
});
