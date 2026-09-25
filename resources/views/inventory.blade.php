<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory System</title>

    <style>
        /* Basic page styling */
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

        /* Product table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #eee;
        }

        /* Loading and error messages */
        #loading {
            margin-bottom: 15px;
        }

        #error {
            color: red;
            margin-bottom: 15px;
        }

        /* Add Product form */
        form {
            margin-top: 20px;
            padding: 20px;
            background: white;
        }

        form div {
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input,
        textarea {
            width: 100%;
            max-width: 400px;
            padding: 8px;
        }

        button {
            padding: 8px 16px;
            cursor: pointer;
        }

        #message {
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <h1>Inventory System</h1>

    <!-- Displays a loading message while products are being retrieved -->
    <div id="loading">Loading products...</div>

    <!-- Displays errors when the API request fails -->
    <div id="error"></div>

    <!-- Product list -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Description</th>
            </tr>
        </thead>

        <!-- JavaScript will insert product rows here -->
        <tbody id="productTable">
        </tbody>
    </table>

    <!-- Add Product section -->
    <h2>Add Product</h2>

    <form id="productForm">

        <div>
            <label for="name">Name</label>
            <input type="text" id="name" required>
        </div>

        <div>
            <label for="code">Code</label>
            <input type="text" id="code" required>
        </div>

        <div>
            <label for="price">Price</label>
            <input type="number" id="price" step="0.01" min="0" required>
        </div>

        <div>
            <label for="stock">Stock</label>
            <input type="number" id="stock" min="0" required>
        </div>

        <div>
            <label for="description">Description</label>
            <textarea id="description"></textarea>
        </div>

        <button type="submit">Add Product</button>

    </form>

    <!-- Displays success or error messages after adding a product -->
    <p id="message"></p>


    <script>

        /*
         * GET /api/products
         *
         * Retrieves all products from the Laravel REST API
         * and displays them in the product table.
         */
        async function loadProducts() {

            try {

                // Send GET request to the Laravel API
                const response = await fetch('/api/products');

                // Check if the API request was unsuccessful
                if (!response.ok) {
                    throw new Error('Failed to load products');
                }

                // Convert the API response from JSON into JavaScript data
                const products = await response.json();

                // Get the table body element
                const table = document.getElementById('productTable');

                /*
                 * Clear the existing table rows.
                 *
                 * This is important because loadProducts() is also called
                 * after creating a new product. Without this, the existing
                 * products would appear twice.
                 */
                table.innerHTML = '';

                // Create a table row for each product
                products.forEach(product => {

                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td>${product.name}</td>
                        <td>${product.code}</td>
                        <td>RM ${product.price}</td>
                        <td>${product.stock}</td>
                        <td>${product.description ?? ''}</td>
                    `;

                    // Add the row to the product table
                    table.appendChild(row);
                });

                // Remove the loading message after products are loaded
                document.getElementById('loading').textContent = '';

            } catch (error) {

                // Remove loading message
                document.getElementById('loading').textContent = '';

                // Display an error message to the user
                document.getElementById('error').textContent =
                    'Unable to load products.';

                // Display the actual error in the browser console
                console.error(error);
            }
        }


        /*
         * Load products when the webpage first opens.
         */
        loadProducts();


        /*
         * POST /api/products
         *
         * Handles the Add Product form.
         */
        document.getElementById('productForm').addEventListener(
            'submit',
            async function (event) {

                /*
                 * Prevent the browser from refreshing the page
                 * when the form is submitted.
                 */
                event.preventDefault();


                /*
                 * Collect the values entered by the user.
                 *
                 * parseFloat() converts the price from a string
                 * into a decimal number.
                 *
                 * parseInt() converts the stock from a string
                 * into an integer.
                 */
                const product = {
                    name: document.getElementById('name').value,
                    code: document.getElementById('code').value,
                    price: parseFloat(
                        document.getElementById('price').value
                    ),
                    stock: parseInt(
                        document.getElementById('stock').value
                    ),
                    description: document.getElementById('description').value
                };


                try {

                    /*
                     * Send the product data to Laravel.
                     *
                     * This is a POST request:
                     *
                     * Browser
                     *     ↓
                     * POST /api/products
                     *     ↓
                     * Laravel
                     *     ↓
                     * MySQL
                     */
                    const response = await fetch('/api/products', {

                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },

                        // Convert JavaScript object into JSON
                        body: JSON.stringify(product)
                    });


                    // Convert Laravel's response into JavaScript data
                    const data = await response.json();


                    /*
                     * Check whether Laravel returned an error.
                     */
                    if (!response.ok) {
                        throw new Error(
                            data.message || 'Failed to create product'
                        );
                    }


                    /*
                     * Product was successfully created.
                     */
                    document.getElementById('message').textContent =
                        'Product added successfully!';


                    // Clear the form
                    document.getElementById('productForm').reset();


                    /*
                     * Reload the product list so the newly created
                     * product appears in the table.
                     */
                    loadProducts();

                } catch (error) {

                    /*
                     * Display the error to the user.
                     */
                    document.getElementById('message').textContent =
                        error.message;

                    // Display detailed error in browser console
                    console.error(error);
                }
            }
        );

    </script>

</body>

</html>