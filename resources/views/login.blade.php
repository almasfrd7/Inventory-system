@extends('layouts.app')

@section('title', 'Login - Inventory System')

@section('content')
<main class="container py-5" style="max-width: 440px;">

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h1 class="h4 fw-bold mb-4">Log in</h1>

            <form id="loginForm" novalidate>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" autocomplete="username">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" autocomplete="current-password">
                    <div class="invalid-feedback"></div>
                </div>

                <div id="formError" class="alert alert-danger d-none" role="alert"></div>

                <div class="d-grid">
                    <button type="submit" id="loginButton" class="btn btn-primary">Log in</button>
                </div>

            </form>

        </div>
    </div>

</main>
@endsection

@push('scripts')
    @vite('resources/js/login.js')
@endpush
