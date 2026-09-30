<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- ==============================
         NAVIGATION BAR
         ============================== -->

    <nav class="navbar navbar-dark bg-dark shadow-sm">

        <div class="container">

            <a class="navbar-brand fw-bold" href="/">
                Inventory System
            </a>

            <a href="/" class="btn btn-outline-light">
                Dashboard
            </a>

        </div>

    </nav>

    <div class="container py-5">

        <h1 class="mb-4">Inventory System</h1>

        <!-- Loading message -->
        <div id="loading" class="alert alert-info d-none" role="status">
            Loading products...
        </div>

        <!-- Error message -->
        <div id="error" class="alert alert-danger d-none" role="alert"></div>

        <!--
            Page-level success message.
            Success messages are shown here (not inside the modal)
            so they stay visible after the modal closes.
        -->
        <div id="pageMessage" class="alert alert-success d-none" role="alert"></div>

        <!-- ==============================
             PRODUCT TABLE
             ============================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Products</h2>

                <!-- Opens the Add Product Bootstrap modal -->
                <button type="button" class="btn btn-sm btn-primary" onclick="openAddProductModal()">
                    Add Product
                </button>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered align-middle mb-0">

                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="productTable">
                            <!-- Products will be inserted here using JavaScript -->
                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>
    <!-- End of main page container -->

    <!-- ==============================
         ADD / EDIT PRODUCT FORM
         ============================== -->

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="formTitle" aria-hidden="true">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">
                    <h2 id="formTitle" class="h5 mb-0">Add Product</h2>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form id="productForm">

                        <div class="mb-3">

                            <!-- Product name -->
                            <label for="name" class="form-label">
                                Name:
                            </label>

                            <input type="text" id="name" class="form-control" required>

                        </div>

                        <div class="mb-3">

                            <!-- Product code -->
                            <label for="code" class="form-label">
                                Code:
                            </label>

                            <input type="text" id="code" class="form-control" required>

                        </div>

                        <div class="mb-3">

                            <!-- Product price -->
                            <label for="price" class="form-label">
                                Price:
                            </label>

                            <input type="number" id="price" class="form-control" step="0.01" min="0" required>

                        </div>

                        <div class="mb-3">

                            <!-- Product stock -->
                            <label for="stock" class="form-label">
                                Stock:
                            </label>

                            <input type="number" id="stock" class="form-control" min="0" required>

                        </div>

                        <div class="mb-3">

                            <!-- Product description -->
                            <label for="description" class="form-label">
                                Description:
                            </label>

                            <textarea id="description" class="form-control" rows="4"></textarea>

                        </div>

                        <div class="modal-footer px-0 pb-0">

                            <!-- Cancel edit button -->
                            <button type="button" id="cancelButton" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <!-- Submit button -->
                            <button type="submit" id="submitButton" class="btn btn-primary">
                                Add Product
                            </button>

                        </div>

                    </form>

                    <!-- Validation / error message shown inside the modal -->
                    <p id="message" class="text-danger mt-3 mb-0"></p>

                </div>

            </div>

        </div>

    </div>

    <!-- ==============================
         ADJUST STOCK MODAL
         ============================== -->

    <div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalTitle"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="adjustStockForm">
                    <div class="modal-header">
                        <h2 id="adjustStockModalTitle" class="modal-title fs-5">Adjust Stock</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small">
                            Enter a positive number to add stock or a negative number to remove stock.
                        </p>

                        <!-- Stock adjustment quantity -->
                        <label for="adjustmentQuantity" class="form-label">Stock adjustment:</label>
                        <input type="number" id="adjustmentQuantity" class="form-control" step="1" required>

                        <!-- Validation errors for this modal -->
                        <div id="adjustStockError" class="alert alert-danger d-none mt-3 mb-0" role="alert"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Bootstrap JavaScript is required for the modal. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        /*
        ==========================================
        GLOBAL VARIABLE
        ==========================================

        Stores the ID of the product currently
        being edited.

        null = Add mode
        number = Edit mode
        */

        let editingProductId = null;


        // Stores the product whose stock is being adjusted in the modal.
        let adjustingProductId = null;


        /*
        Bootstrap controls opening and closing the
        Add / Edit Product modal.
        */

        const productModalElement = document.getElementById('productModal');

        const productModal = new bootstrap.Modal(productModalElement);


        // Bootstrap controls opening and closing the stock adjustment modal.
        const adjustStockModal = new bootstrap.Modal(
            document.getElementById('adjustStockModal')
        );


        /*
        ==========================================
        STOCK STATUS SETTINGS
        ==========================================

        A product with stock from 1 to this value
        is shown as low stock. Zero stock is always
        shown separately as out of stock.
        */

        const LOW_STOCK_THRESHOLD = 5;


        /*
        ==========================================
        PAGE MESSAGES
        ==========================================

        showPageMessage() shows a green success
        message at the top of the page and hides it
        automatically after 4 seconds.

        showPageError() shows a red error message
        at the top of the page.
        */

        let pageMessageTimer = null;

        function showPageMessage(text) {

            const box = document.getElementById('pageMessage');

            box.textContent = text;
            box.classList.remove('d-none');

            // Restart the timer if another message appears quickly.
            clearTimeout(pageMessageTimer);

            pageMessageTimer = setTimeout(() => {
                box.classList.add('d-none');
            }, 4000);

        }

        function showPageError(text) {

            const box = document.getElementById('error');

            box.textContent = text;
            box.classList.remove('d-none');

        }


        /*
        ==========================================
        GET ALL PRODUCTS
        ==========================================

        API endpoint:

        GET /api/products

        This function gets all products from Laravel
        and displays them in the table.
        */

        async function loadProducts() {

            /*
            Show loading only while the API request is
            running. Hide a previous error before retrying.
            */

            const loading = document.getElementById('loading');
            const errorMessage = document.getElementById('error');

            loading.textContent = 'Loading products...';
            loading.classList.remove('d-none');

            errorMessage.textContent = '';
            errorMessage.classList.add('d-none');

            try {

                const response = await fetch('/api/products', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });


                // Check if API request failed
                if (!response.ok) {
                    throw new Error('Failed to load products');
                }


                // Convert response to JSON
                const products = await response.json();


                // Get table body
                const table = document.getElementById('productTable');


                // Clear existing rows
                table.innerHTML = '';


                /*
                Loop through every product
                returned by the API.
                */

                products.forEach(product => {

                    // Create a new table row
                    const row = document.createElement('tr');


                    // Make sure stock is compared as a number
                    const stock = Number(product.stock);


                    /*
                    Show a clear stock status beside
                    the stock quantity in the table.
                    */

                    let stockStatus;

                    if (stock === 0) {

                        stockStatus =
                            '<span class="badge text-bg-danger ms-1">Out of stock</span>';

                    } else if (stock <= LOW_STOCK_THRESHOLD) {

                        stockStatus =
                            '<span class="badge text-bg-warning ms-1">Low stock</span>';

                    } else {

                        stockStatus =
                            '<span class="badge text-bg-success ms-1">In stock</span>';

                    }


                    /*
                    Add product information
                    and action buttons.
                    */

                    row.innerHTML = `

                        <td>${product.id}</td>

                        <td>${product.name}</td>

                        <td>${product.code}</td>

                        <td>RM ${parseFloat(product.price).toFixed(2)}</td>

                        <td>${stock}${stockStatus}</td>

                        <td>${product.description ?? ''}</td>

                        <td>

                            <button
                                class="btn btn-sm btn-warning me-1"
                                onclick="editProduct(${product.id})"
                            >
                                Edit
                            </button>

                            <button
                                class="btn btn-sm btn-info me-1"
                                onclick="adjustStock(${product.id})"
                            >
                                Adjust Stock
                            </button>

                            <button
                                class="btn btn-sm btn-danger"
                                onclick="deleteProduct(${product.id})"
                            >
                                Delete
                            </button>

                        </td>

                    `;


                    // Add row to table
                    table.appendChild(row);

                });


                // Hide loading message after products load successfully.
                document.getElementById('loading').textContent = '';
                document.getElementById('loading').classList.add('d-none');


            }
            catch (error) {

                // Hide loading and show the error only when the request fails.
                document.getElementById('loading').textContent = '';
                document.getElementById('loading').classList.add('d-none');

                showPageError('Unable to load products.');

                console.error(error);

            }

        }


        /*
        ==========================================
        OPEN ADD PRODUCT MODAL
        ==========================================

        Called by the Add Product button.

        Clears the form, makes sure it is in Add
        mode, then opens the Bootstrap modal.
        */

        function openAddProductModal() {

            resetForm();

            // Clear any old message inside the modal
            document.getElementById('message').textContent = '';

            productModal.show();

        }


        /*
        ==========================================
        GET SINGLE PRODUCT / OPEN EDIT MODAL
        ==========================================

        API endpoint:

        GET /api/products/{id}

        This function gets one product from Laravel
        when the user clicks Edit, fills the form
        and opens the modal.
        */

        async function editProduct(id) {

            try {

                const response = await fetch(`/api/products/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });


                if (!response.ok) {
                    throw new Error('Failed to load product');
                }


                // Convert API response to JSON
                const product = await response.json();


                /*
                Put the product data into
                the form fields.
                */

                document.getElementById('name').value =
                    product.name;

                document.getElementById('code').value =
                    product.code;

                document.getElementById('price').value =
                    product.price;

                document.getElementById('stock').value =
                    product.stock;

                document.getElementById('description').value =
                    product.description ?? '';


                /*
                Store the product ID.

                This tells the submit function
                that we are editing instead of
                creating a new product.
                */

                editingProductId = id;


                /*
                Change form UI from:

                Add Product

                to:

                Edit Product
                */

                document.getElementById('formTitle').textContent =
                    'Edit Product';

                document.getElementById('submitButton').textContent =
                    'Update Product';

                // Clear any old message inside the modal
                document.getElementById('message').textContent = '';


                // Open the modal
                productModal.show();


            } catch (error) {

                showPageError(error.message);

                console.error(error);

            }

        }


        /*
        ==========================================
        CREATE / UPDATE PRODUCT
        ==========================================

        ADD:

        POST /api/products

        UPDATE:

        PUT /api/products/{id}

        The endpoint depends on whether
        editingProductId is null.
        */

        document
            .getElementById('productForm')
            .addEventListener('submit', async function (event) {

                // Prevent normal HTML form submission
                event.preventDefault();


                /*
                Collect data from the form.
                */

                const product = {

                    name: document
                        .getElementById('name')
                        .value,

                    code: document
                        .getElementById('code')
                        .value,

                    price: parseFloat(
                        document
                            .getElementById('price')
                            .value
                    ),

                    stock: parseInt(
                        document
                            .getElementById('stock')
                            .value
                    ),

                    description: document
                        .getElementById('description')
                        .value

                };


                // Remember the mode before the form is reset.
                const isEditing = editingProductId !== null;


                try {

                    let response;


                    /*
                    ======================================
                    EDIT MODE
                    ======================================

                    If editingProductId contains an ID,
                    send PUT request.
                    */

                    if (isEditing) {

                        response = await fetch(
                            `/api/products/${editingProductId}`,
                            {
                                method: 'PUT',

                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },

                                body: JSON.stringify(product)
                            }
                        );

                    }


                    /*
                    ======================================
                    ADD MODE
                    ======================================

                    If editingProductId is null,
                    send POST request.
                    */

                    else {

                        response = await fetch(
                            '/api/products',
                            {
                                method: 'POST',

                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },

                                body: JSON.stringify(product)
                            }
                        );

                    }


                    /*
                    Convert Laravel response
                    into JSON.
                    */

                    const data = await response.json();


                    /*
                    Laravel validation errors
                    or other API errors.
                    */

                    if (!response.ok) {

                        /*
                        Laravel validation errors
                        usually look like:

                        {
                            "message": "...",
                            "errors": {
                                "name": [...]
                            }
                        }
                        */

                        if (data.errors) {

                            const errors = Object.values(data.errors)
                                .flat()
                                .join(' ');

                            throw new Error(errors);

                        }

                        throw new Error(
                            data.message ||
                            'Request failed'
                        );

                    }


                    /*
                    Show success message on the page
                    (not inside the modal, because the
                    modal is about to close).
                    */

                    if (isEditing) {

                        showPageMessage(
                            'Product updated successfully!'
                        );

                    } else {

                        showPageMessage(
                            'Product added successfully!'
                        );

                    }


                    /*
                    Close the modal.

                    The form is reset automatically by the
                    hidden.bs.modal event listener below.
                    */

                    productModal.hide();


                    /*
                    Reload products so the
                    table shows the latest data.
                    */

                    loadProducts();


                } catch (error) {

                    /*
                    Keep the modal open and show the
                    error inside it so the user can
                    fix the problem and try again.
                    */

                    document.getElementById('message')
                        .textContent = error.message;

                    console.error(error);

                }

            });


        /*
        ==========================================
        ADJUST PRODUCT STOCK
        ==========================================

        Opens a Bootstrap modal instead of using
        the browser's default prompt popup.
        */

        function adjustStock(id) {

            // Remember which product should receive the adjustment.
            adjustingProductId = id;


            // Reset the modal so it is clean every time it opens.
            document.getElementById('adjustStockForm').reset();

            document.getElementById('adjustStockError').textContent = '';
            document.getElementById('adjustStockError').classList.add('d-none');

            adjustStockModal.show();

        }


        /*
        Submit the stock adjustment entered in
        the Bootstrap modal.
        */

        document
            .getElementById('adjustStockForm')
            .addEventListener('submit', async function (event) {

                event.preventDefault();


                const quantity = Number(
                    document.getElementById('adjustmentQuantity').value
                );


                // Only whole, non-zero stock changes are valid.
                if (!Number.isInteger(quantity) || quantity === 0) {

                    document.getElementById('adjustStockError').textContent =
                        'Enter a whole number other than zero.';

                    document.getElementById('adjustStockError').classList.remove('d-none');

                    return;

                }


                try {

                    const response = await fetch(
                        `/api/products/${adjustingProductId}/adjust-stock`,
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },

                            body: JSON.stringify({ quantity })
                        }
                    );


                    const data = await response.json();


                    if (!response.ok) {

                        if (data.errors) {

                            const errors = Object.values(data.errors)
                                .flat()
                                .join(' ');

                            throw new Error(errors);

                        }

                        throw new Error(
                            data.message ||
                            'Failed to adjust stock'
                        );

                    }


                    // Show the success message on the page.
                    showPageMessage(data.message);


                    // Close the modal and reload the table with the new stock.
                    adjustStockModal.hide();
                    loadProducts();


                } catch (error) {

                    document.getElementById('adjustStockError').textContent =
                        error.message;

                    document.getElementById('adjustStockError').classList.remove('d-none');

                    console.error(error);

                }

            });


        /*
        ==========================================
        DELETE PRODUCT
        ==========================================

        API endpoint:

        DELETE /api/products/{id}
        */

        async function deleteProduct(id) {

            /*
            Ask user for confirmation before
            deleting the product.
            */

            const confirmed = confirm(
                'Are you sure you want to delete this product?'
            );


            // Stop if user clicks Cancel
            if (!confirmed) {
                return;
            }


            try {

                /*
                Send DELETE request to Laravel.
                */

                const response = await fetch(
                    `/api/products/${id}`,
                    {
                        method: 'DELETE',

                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );


                /*
                Convert response to JSON.

                Laravel returns:

                {
                    "message":
                    "Product deleted successfully"
                }
                */

                const data = await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.message ||
                        'Failed to delete product'
                    );

                }


                /*
                Show success message.
                */

                showPageMessage(
                    'Product deleted successfully!'
                );


                /*
                Reload table after deletion.
                */

                loadProducts();


            } catch (error) {

                showPageError(error.message);

                console.error(error);

            }

        }


        /*
        ==========================================
        RESET FORM
        ==========================================

        Changes the form back to Add Product mode.

        The Cancel button lives inside the modal and
        is closed by Bootstrap (data-bs-dismiss), so
        it must NOT be hidden here.
        */

        function resetForm() {

            // Clear all input fields
            document.getElementById('productForm').reset();


            // Clear editing ID
            editingProductId = null;


            // Change title back
            document.getElementById('formTitle').textContent =
                'Add Product';


            // Change button back
            document.getElementById('submitButton').textContent =
                'Add Product';

        }


        /*
        ==========================================
        MODAL CLOSED
        ==========================================

        Runs whenever the Add / Edit modal closes:
        Cancel button, X button, clicking outside
        the modal, or after a successful save.

        Resets the form so the next time it opens
        it is clean.
        */

        productModalElement.addEventListener('hidden.bs.modal', function () {

            resetForm();

            document.getElementById('message')
                .textContent = '';

        });


        /*
        ==========================================
        LOAD PRODUCTS WHEN PAGE OPENS
        ==========================================
        */

        loadProducts();

    </script>

</body>

</html>