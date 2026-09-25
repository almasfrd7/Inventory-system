<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 20px;
        }

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

        #loading {
            margin-bottom: 15px;
        }

        #error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <h1>Inventory System</h1>

    <div id="loading">Loading products...</div>

    <div id="error"></div>

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

        <tbody id="productTable">
        </tbody>
    </table>

    <script>
        async function loadProducts() {
            try {
                const response = await fetch('/api/products');

                if (!response.ok) {
                    throw new Error('Failed to load products');
                }

                const products = await response.json();

                const table = document.getElementById('productTable');

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

                    table.appendChild(row);
                });

                document.getElementById('loading').textContent = '';

            } catch (error) {
                document.getElementById('loading').textContent = '';

                document.getElementById('error').textContent =
                    'Unable to load products.';
                
                console.error(error);
            }
        }

        loadProducts();
    </script>

</body>
</html>