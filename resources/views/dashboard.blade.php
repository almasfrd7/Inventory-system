<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory System - Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- ==============================
         NAVIGATION BAR
         ============================== -->

    <nav class="navbar navbar-dark bg-dark shadow-sm">

        <div class="container">

            <a class="navbar-brand fw-bold" href="/">
                Inventory System
            </a>

            <a href="/inventory" class="btn btn-outline-light">
                Inventory
            </a>

        </div>

    </nav>


    <!-- ==============================
         DASHBOARD
         ============================== -->

    <main class="container py-5">

        <!-- Dashboard heading -->
        <div class="mb-4">

            <h1 class="fw-bold">Dashboard</h1>

            <p class="text-muted mb-0">
                Welcome to your Inventory Management System.
            </p>

        </div>


        <!-- ==============================
             STATISTICS
             ============================== -->

        <div class="row g-4 mb-5">

            <!-- Total Products -->
            <div class="col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <p class="text-muted mb-2">
                            Total Products
                        </p>

                        <h2 class="fw-bold mb-0">
                            0
                        </h2>

                    </div>

                </div>

            </div>


            <!-- Total Stock -->
            <div class="col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <p class="text-muted mb-2">
                            Total Stock
                        </p>

                        <h2 class="fw-bold mb-0">
                            0
                        </h2>

                    </div>

                </div>

            </div>


            <!-- Low Stock -->
            <div class="col-md-6 col-xl-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <p class="text-muted mb-2">
                            Low Stock
                        </p>

                        <h2 class="fw-bold mb-0">
                            0
                        </h2>

                    </div>

                </div>

            </div>

        </div>


        <!-- ==============================
             INVENTORY DIRECTORY
             ============================== -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <div class="row align-items-center">

                    <div class="col-md-8">

                        <h2 class="h4 fw-bold">
                            Inventory Management
                        </h2>

                        <p class="text-muted mb-md-0">
                            View, add, edit and delete products from your inventory.
                        </p>

                    </div>

                    <div class="col-md-4 text-md-end mt-3 mt-md-0">

                        <a href="/inventory" class="btn btn-primary px-4">
                            Go to Inventory
                        </a>

                    </div>

                </div>

            </div>

        </div>


        <!-- ==============================
             QUICK ACCESS
             ============================== -->

        <div class="mt-5">

            <h2 class="h5 fw-bold mb-3">
                Quick Access
            </h2>

            <div class="row g-3">

                <div class="col-md-4">

                    <a href="/inventory" class="text-decoration-none">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body">

                                <h3 class="h6 fw-bold text-dark">
                                    Product Inventory
                                </h3>

                                <p class="text-muted small mb-0">
                                    Manage all products and stock information.
                                </p>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="/inventory" class="text-decoration-none">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body">

                                <h3 class="h6 fw-bold text-dark">
                                    Add Product
                                </h3>

                                <p class="text-muted small mb-0">
                                    Add a new product to the inventory.
                                </p>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-4">

                    <a href="/inventory" class="text-decoration-none">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body">

                                <h3 class="h6 fw-bold text-dark">
                                    Manage Stock
                                </h3>

                                <p class="text-muted small mb-0">
                                    View and update product stock quantities.
                                </p>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

    </main>


    <!-- ==============================
         FOOTER
         ============================== -->

    <footer class="py-4 mt-5 border-top bg-white">

        <div class="container text-center">

            <p class="text-muted small mb-0">
                Inventory System
            </p>

        </div>

    </footer>

</body>

</html>