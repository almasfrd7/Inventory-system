import { api, requireLogin } from './api';

const ids = {
    total_products: 'totalProducts',
    total_stock: 'totalStock',
    low_stock: 'lowStock',
    out_of_stock: 'outOfStock',
};

async function loadDashboard() {
    try {
        const { data } = await api('/products/stats');

        Object.entries(ids).forEach(([key, id]) => {
            document.getElementById(id).textContent = data[key];
        });
    } catch (error) {
        console.error(error);
        Object.values(ids).forEach((id) => {
            document.getElementById(id).textContent = '-';
        });
    }
}

if (requireLogin()) {
    loadDashboard();
}
