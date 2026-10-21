@extends('layouts.app')

@section('title', 'Inventory System')

@push('head')
<style>
    th.sortable { cursor: pointer; user-select: none; white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="container py-5">

    <h1 class="mb-4">Inventory</h1>

    <div id="loading" class="alert alert-info d-none" role="status"></div>
    <div id="error" class="alert alert-danger d-none" role="alert"></div>
    <div id="pageMessage" class="alert alert-success d-none" role="alert"></div>

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Products</h2>
            <button type="button" id="addProductButton" class="btn btn-sm btn-primary">Add Product</button>
        </div>

        <div class="card-body">

            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="search" id="searchInput" class="form-control"
                           placeholder="Search name, code or description..." aria-label="Search products">
                </div>

                <div class="col-md-4">
                    <select id="statusFilter" class="form-select" aria-label="Filter by stock status">
                        <option value="">All stock levels</option>
                        <option value="in_stock">In stock</option>
                        <option value="low_stock">Low stock</option>
                        <option value="out_of_stock">Out of stock</option>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <button type="button" id="clearFiltersButton" class="btn btn-outline-secondary">Clear filters</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="sortable" data-sort="id">ID <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="name">Name <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="code">Code <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="price">Price <span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="stock">Stock <span class="sort-indicator"></span></th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTable"></tbody>
                </table>
            </div>

        </div>

        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <label for="perPage" class="mb-0 small text-muted">Rows per page:</label>
                <select id="perPage" class="form-select form-select-sm w-auto">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="50">50</option>
                </select>
                <span id="paginationInfo" class="small text-muted"></span>
            </div>

            <nav aria-label="Product pages">
                <ul id="pagination" class="pagination pagination-sm mb-0"></ul>
            </nav>
        </div>

    </div>
</div>

{{-- ADD / EDIT PRODUCT --}}
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="formTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h2 id="formTitle" class="h5 mb-0">Add Product</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                {{-- novalidate: let the server validate so errors appear under each field --}}
                <form id="productForm" novalidate>

                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" id="name" name="name" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Code:</label>
                        <input type="text" id="code" name="code" class="form-control">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price (RM):</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock:</label>
                        <input type="number" id="stock" name="stock" class="form-control" step="1" min="0">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div id="formError" class="alert alert-danger d-none" role="alert"></div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitButton" class="btn btn-primary">Add Product</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

{{-- ADJUST STOCK --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="adjustStockForm" novalidate>

                <div class="modal-header">
                    <h2 id="adjustStockModalTitle" class="modal-title fs-5">Adjust Stock</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small">
                        Enter a positive number to add stock or a negative number to remove stock.
                    </p>

                    <div class="mb-3">
                        <label for="adjustmentQuantity" class="form-label">Stock adjustment:</label>
                        <input type="number" id="adjustmentQuantity" name="quantity" class="form-control" step="1">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="adjustmentReason" class="form-label">Reason (optional):</label>
                        <input type="text" id="adjustmentReason" name="reason" class="form-control"
                               maxlength="255" placeholder="e.g. Supplier delivery, damaged goods">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div id="adjustStockError" class="alert alert-danger d-none mb-0" role="alert"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Adjustment</button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/inventory.js')
@endpush
