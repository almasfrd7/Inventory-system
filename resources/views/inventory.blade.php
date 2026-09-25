<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory System</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

    <div class="container py-5">

        <h1 class="mb-4">Inventory System</h1>

        <!-- Loading message -->
        <div id="loading" class="alert alert-info">
            Loading products...
        </div>

        <!-- Error message -->
        <div id="error" class="alert alert-danger"></div>

        <!-- ==============================
             PRODUCT TABLE
             ============================== -->

        <div class="card shadow-sm mb-4">

            <div class="card-header">
                <h2 class="h5 mb-0">Products</h2>
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

        <!-- ==============================
             ADD / EDIT PRODUCT FORM
             ============================== -->

        <div class="card shadow-sm">

            <div class="card-header">
                <h2 id="formTitle" class="h5 mb-0">Add Product</h2>
            </div>

            <div class="card-body">

                <form id="productForm">

                    <div class="mb-3">

                        <!-- Product name -->
                        <label for="name" class="form-label">
                            Name:
                        </label>

                        <input
                            type="text"
                            id="name"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <!-- Product code -->
                        <label for="code" class="form-label">
                            Code:
                        </label>

                        <input
                            type="text"
                            id="code"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <!-- Product price -->
                        <label for="price" class="form-label">
                            Price:
                        </label>

                        <input
                            type="number"
                            id="price"
                            class="form-control"
                            step="0.01"
                            min="0"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <!-- Product stock -->
                        <label for="stock" class="form-label">
                            Stock:
                        </label>

                        <input
                            type="number"
                            id="stock"
                            class="form-control"
                            min="0"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <!-- Product description -->
                        <label for="description" class="form-label">
                            Description:
                        </label>

                        <textarea
                            id="description"
                            class="form-control"
                            rows="4"
                        ></textarea>

                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        id="submitButton"
                        class="btn btn-primary me-2"
                    >
                        Add Product
                    </button>

                    <!-- Cancel edit button -->
                    <button
                        type="button"
                        id="cancelButton"
                        class="btn btn-secondary"
                        style="display: none;"
                    >
                        Cancel
                    </button>

                </form>

                <!-- Success / error message -->
                <p id="message" class="mt-3 mb-0"></p>

            </div>

        </div>

    </div>

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

            try {

                const response = await fetch('/api/products');


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


                    /*
                    Add product information
                    and action buttons.
                    */

                    row.innerHTML = `

                        <td>${product.id}</td>

                        <td>${product.name}</td>

                        <td>${product.code}</td>

                        <td>RM ${parseFloat(product.price).toFixed(2)}</td>

                        <td>${product.stock}</td>

                        <td>${product.description ?? ''}</td>

                        <td>

                            <button
                                class="btn btn-sm btn-warning me-1"
                                onclick="editProduct(${product.id})"
                            >
                                Edit
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


                // Hide loading message
                document.getElementById('loading').textContent = '';


            } 
            catch (error) {

                document.getElementById('loading').textContent = '';

                document.getElementById('error').textContent =
                    'Unable to load products.';

                console.error(error);

            }

        }


        /*
        ==========================================
        GET SINGLE PRODUCT
        ==========================================

        API endpoint:

        GET /api/products/{id}

        This function gets one product from Laravel
        when the user clicks Edit.
        */

        async function editProduct(id) {

            try {

                const response = await fetch(`/api/products/${id}`);


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

                document.getElementById('cancelButton').style.display =
                    'inline-block';


                // Scroll to form
                document.getElementById('productForm')
                    .scrollIntoView({
                        behavior: 'smooth'
                    });


            } catch (error) {

                document.getElementById('message').textContent =
                    error.message;

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


                try {

                    let response;


                    /*
                    ======================================
                    EDIT MODE
                    ======================================

                    If editingProductId contains an ID,
                    send PUT request.
                    */

                    if (editingProductId !== null) {

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
                    Show success message.
                    */

                    if (editingProductId !== null) {

                        document.getElementById('message')
                            .textContent =
                            'Product updated successfully!';

                    } else {

                        document.getElementById('message')
                            .textContent =
                            'Product added successfully!';

                    }


                    /*
                    Reset form.
                    */

                    resetForm();


                    /*
                    Reload products so the
                    table shows the latest data.
                    */

                    loadProducts();


                } catch (error) {

                    document.getElementById('message')
                        .textContent = error.message;

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

                document.getElementById('message')
                    .textContent =
                    'Product deleted successfully!';


                /*
                Reload table after deletion.
                */

                loadProducts();


            } catch (error) {

                document.getElementById('message')
                    .textContent =
                    error.message;

                console.error(error);

            }

        }


        /*
        ==========================================
        CANCEL EDIT / RESET FORM
        ==========================================

        Changes the form back to Add Product mode.
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


            // Hide cancel button
            document.getElementById('cancelButton').style.display =
                'none';

        }


        /*
        ==========================================
        CANCEL BUTTON
        ==========================================
        */

        document
            .getElementById('cancelButton')
            .addEventListener('click', function () {

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
