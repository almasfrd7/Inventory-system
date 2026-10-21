@extends('layouts.app')

@section('title', 'Inventory System - Dashboard')

@section('content')
<main class="container py-5">

    <div class="mb-4">
        <h1 class="fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Welcome to your Inventory Management System.</p>
    </div>

    <div class="row g-4 mb-5">

        @foreach ([
            ['totalProducts', 'Total Products'],
            ['totalStock', 'Total Stock'],
            ['lowStock', 'Low Stock'],
            ['outOfStock', 'Out of Stock'],
        ] as [$id, $label])
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-2">{{ $label }}</p>
                        <h2 id="{{ $id }}" class="fw-bold mb-0">0</h2>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center">

                <div class="col-md-8">
                    <h2 class="h4 fw-bold">Inventory Management</h2>
                    <p class="text-muted mb-md-0">
                        View, add, edit and delete products from your inventory.
                    </p>
                </div>

                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="/inventory" class="btn btn-primary px-4">Go to Inventory</a>
                </div>

            </div>
        </div>
    </div>

</main>
@endsection

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush
