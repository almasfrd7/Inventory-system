import {
    api, requireLogin, escapeHtml, ApiError,
    clearFieldErrors, showFieldErrors, setAlert,
} from './api';

requireLogin();

/* ---------- State ---------- */

const state = { search: '', status: '', sortBy: 'id', sortDir: 'asc', perPage: 10, page: 1 };

let editingId = null;
let adjustingId = null;
let latestRequestId = 0;
let searchTimer = null;
let messageTimer = null;

const $ = (id) => document.getElementById(id);

const productModal = new bootstrap.Modal($('productModal'));
const adjustModal = new bootstrap.Modal($('adjustStockModal'));
const productForm = $('productForm');
const adjustForm = $('adjustStockForm');

const STATUS_BADGES = {
    out_of_stock: ['danger', 'Out of stock'],
    low_stock: ['warning', 'Low stock'],
    in_stock: ['success', 'In stock'],
};

/* ---------- Page messages ---------- */

function showMessage(text) {
    const box = $('pageMessage');
    box.textContent = text;
    box.classList.remove('d-none');

    clearTimeout(messageTimer);
    messageTimer = setTimeout(() => box.classList.add('d-none'), 4000);
}

function showError(text) {
    setAlert($('error'), text);
}

/* ---------- List ---------- */

async function loadProducts() {
    const requestId = ++latestRequestId;

    setAlert($('loading'), 'Loading products...');
    setAlert($('error'), '');

    try {
        const result = await api('/products', {
            params: {
                page: state.page,
                per_page: state.perPage,
                sort_by: state.sortBy,
                sort_dir: state.sortDir,
                search: state.search,
                status: state.status,
            },
        });

        // A newer request started; ignore this old result.
        if (requestId !== latestRequestId) return;

        const products = result.data;
        const meta = result.meta;

        // Current page no longer exists (e.g. deleted the last row on the last page).
        if (products.length === 0 && meta.last_page > 0 && state.page > meta.last_page) {
            state.page = meta.last_page;
            return loadProducts();
        }

        renderRows(products);
        renderPagination(meta);
        updateSortIndicators();
        setAlert($('loading'), '');
    } catch (error) {
        if (requestId !== latestRequestId) return;

        setAlert($('loading'), '');
        showError(error.message || 'Unable to load products.');
        console.error(error);
    }
}

function renderRows(products) {
    const table = $('productTable');

    if (products.length === 0) {
        const filtered = state.search !== '' || state.status !== '';

        table.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    ${filtered
                        ? 'No products match your search or filter.'
                        : 'No products yet. Click "Add Product" to create one.'}
                </td>
            </tr>`;
        return;
    }

    table.innerHTML = products.map((product) => {
        const [color, label] = STATUS_BADGES[product.stock_status] ?? STATUS_BADGES.in_stock;

        return `
            <tr>
                <td>${product.id}</td>
                <td>${escapeHtml(product.name)}</td>
                <td>${escapeHtml(product.code)}</td>
                <td>RM ${Number(product.price).toFixed(2)}</td>
                <td>${product.stock}<span class="badge text-bg-${color} ms-1">${label}</span></td>
                <td>${escapeHtml(product.description)}</td>
                <td class="text-nowrap">
                    <button class="btn btn-sm btn-warning me-1" data-action="edit" data-id="${product.id}">Edit</button>
                    <button class="btn btn-sm btn-info me-1" data-action="adjust" data-id="${product.id}">Adjust Stock</button>
                    <button class="btn btn-sm btn-danger" data-action="delete" data-id="${product.id}">Delete</button>
                </td>
            </tr>`;
    }).join('');
}

/* Row buttons: one listener instead of inline onclick handlers. */
$('productTable').addEventListener('click', (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    const id = Number(button.dataset.id);
    const actions = { edit: editProduct, adjust: openAdjustModal, delete: deleteProduct };

    actions[button.dataset.action]?.(id);
});

/* ---------- Pagination ---------- */

function getPageNumbers(current, last) {
    const pages = [];

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || Math.abs(i - current) <= 2) {
            pages.push(i);
        } else if (pages[pages.length - 1] !== '...') {
            pages.push('...');
        }
    }

    return pages;
}

function renderPagination(meta) {
    const list = $('pagination');
    list.innerHTML = '';

    $('paginationInfo').textContent = meta.total === 0
        ? 'No products found'
        : `Showing ${meta.from}-${meta.to} of ${meta.total} products`;

    if (meta.last_page <= 1) return;

    const addButton = (label, page, disabled = false, active = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'page-link';
        button.textContent = label;

        if (!disabled && page !== null) button.dataset.page = page;

        li.appendChild(button);
        list.appendChild(li);
    };

    addButton('Previous', meta.current_page - 1, meta.current_page === 1);

    getPageNumbers(meta.current_page, meta.last_page).forEach((page) => {
        if (page === '...') addButton('...', null, true);
        else addButton(page, page, false, page === meta.current_page);
    });

    addButton('Next', meta.current_page + 1, meta.current_page === meta.last_page);
}

$('pagination').addEventListener('click', (event) => {
    const button = event.target.closest('button[data-page]');
    if (!button) return;

    state.page = Number(button.dataset.page);
    loadProducts();
});

$('perPage').addEventListener('change', function () {
    state.perPage = Number(this.value);
    state.page = 1;
    loadProducts();
});

/* ---------- Search / filter / sort ---------- */

$('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        state.search = this.value.trim();
        state.page = 1;
        loadProducts();
    }, 300);
});

$('statusFilter').addEventListener('change', function () {
    state.status = this.value;
    state.page = 1;
    loadProducts();
});

$('clearFiltersButton').addEventListener('click', () => {
    clearTimeout(searchTimer);

    $('searchInput').value = '';
    $('statusFilter').value = '';

    state.search = '';
    state.status = '';
    state.page = 1;

    loadProducts();
});

document.querySelectorAll('th.sortable').forEach((heading) => {
    heading.addEventListener('click', () => {
        const column = heading.dataset.sort;

        if (state.sortBy === column) {
            state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            state.sortBy = column;
            state.sortDir = 'asc';
        }

        state.page = 1;
        loadProducts();
    });
});

function updateSortIndicators() {
    document.querySelectorAll('th.sortable').forEach((heading) => {
        const indicator = heading.querySelector('.sort-indicator');

        indicator.textContent = heading.dataset.sort === state.sortBy
            ? (state.sortDir === 'asc' ? '▲' : '▼')
            : '';
    });
}

/* ---------- Add / edit product ---------- */

function resetProductForm() {
    productForm.reset();
    clearFieldErrors(productForm);
    setAlert($('formError'), '');

    editingId = null;
    $('formTitle').textContent = 'Add Product';
    $('submitButton').textContent = 'Add Product';
}

$('addProductButton').addEventListener('click', () => {
    resetProductForm();
    productModal.show();
});

async function editProduct(id) {
    try {
        const { data: product } = await api(`/products/${id}`);

        resetProductForm();

        productForm.querySelector('[name="name"]').value = product.name;
        productForm.querySelector('[name="code"]').value = product.code;
        productForm.querySelector('[name="price"]').value = product.price;
        productForm.querySelector('[name="stock"]').value = product.stock;
        productForm.querySelector('[name="description"]').value = product.description ?? '';

        editingId = id;
        $('formTitle').textContent = 'Edit Product';
        $('submitButton').textContent = 'Update Product';

        productModal.show();
    } catch (error) {
        showError(error.message);
    }
}

productForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    clearFieldErrors(productForm);
    setAlert($('formError'), '');

    const data = new FormData(productForm);
    const toNumber = (value) => (value === '' ? null : Number(value));

    const payload = {
        name: data.get('name').trim(),
        code: data.get('code').trim(),
        price: toNumber(data.get('price')),
        stock: toNumber(data.get('stock')),
        description: data.get('description').trim() || null,
    };

    const isEditing = editingId !== null;
    const submitButton = $('submitButton');
    submitButton.disabled = true;

    try {
        await api(isEditing ? `/products/${editingId}` : '/products', {
            method: isEditing ? 'PUT' : 'POST',
            body: payload,
        });

        showMessage(isEditing ? 'Product updated successfully!' : 'Product added successfully!');
        productModal.hide();
        loadProducts();
    } catch (error) {
        // Show each validation error under its own field.
        if (error instanceof ApiError && Object.keys(error.errors).length) {
            const unmatched = showFieldErrors(productForm, error.errors);
            if (unmatched.length) setAlert($('formError'), unmatched.join(' '));
        } else {
            setAlert($('formError'), error.message);
        }
    } finally {
        submitButton.disabled = false;
    }
});

$('productModal').addEventListener('hidden.bs.modal', resetProductForm);

/* ---------- Adjust stock ---------- */

function resetAdjustForm() {
    adjustForm.reset();
    clearFieldErrors(adjustForm);
    setAlert($('adjustStockError'), '');
}

function openAdjustModal(id) {
    adjustingId = id;
    resetAdjustForm();
    adjustModal.show();
}

adjustForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    clearFieldErrors(adjustForm);
    setAlert($('adjustStockError'), '');

    const data = new FormData(adjustForm);
    const raw = data.get('quantity');

    const payload = {
        quantity: raw === '' ? null : Number(raw),
        reason: data.get('reason').trim() || null,
    };

    const button = adjustForm.querySelector('button[type="submit"]');
    button.disabled = true;

    try {
        const result = await api(`/products/${adjustingId}/adjust-stock`, {
            method: 'POST',
            body: payload,
        });

        showMessage(result.message);
        adjustModal.hide();
        loadProducts();
    } catch (error) {
        if (error instanceof ApiError && Object.keys(error.errors).length) {
            const unmatched = showFieldErrors(adjustForm, error.errors);
            if (unmatched.length) setAlert($('adjustStockError'), unmatched.join(' '));
        } else {
            setAlert($('adjustStockError'), error.message);
        }
    } finally {
        button.disabled = false;
    }
});

$('adjustStockModal').addEventListener('hidden.bs.modal', resetAdjustForm);

/* ---------- Delete ---------- */

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;

    try {
        await api(`/products/${id}`, { method: 'DELETE' });

        showMessage('Product deleted successfully!');
        loadProducts();
    } catch (error) {
        showError(error.message);
    }
}

/* ---------- Start ---------- */

loadProducts();
