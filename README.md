# Inventory System

A web-based Inventory Management System built with **Laravel** and **MySQL**, featuring a **RESTful API** for managing inventory data.

## About

This project is developed to practice building a web application with a RESTful API using Laravel.

The system allows inventory data such as products, stock quantities, pricing, and product details to be managed through API endpoints.

## Features

* Product management
* Product CRUD operations
* Inventory and stock management
* RESTful API
* Request validation
* MySQL database
* Laravel Eloquent ORM

## REST API

The system provides RESTful endpoints for managing products:

| Method   | Endpoint             | Description                |
| -------- | -------------------- | -------------------------- |
| `GET`    | `/api/products`      | Get all products           |
| `GET`    | `/api/products/{id}` | Get a specific product     |
| `POST`   | `/api/products`      | Create a new product       |
| `PUT`    | `/api/products/{id}` | Update a product           |
| `PATCH`  | `/api/products/{id}` | Partially update a product |
| `DELETE` | `/api/products/{id}` | Delete a product           |

## Product Data

Each product contains:

* `id`
* `name`
* `code`
* `price`
* `stock`
* `description`
* `created_at`
* `updated_at`

## Technologies

* **Laravel**
* **PHP**
* **MySQL**
* **REST API**
* **Eloquent ORM**
* **HTML / CSS / JavaScript**

## Local Setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd inventory-system
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Update the database configuration in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_system
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Start the development server

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

## Project Structure

```text
inventory-system/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── database/
│   └── migrations/
├── routes/
│   ├── api.php
│   └── web.php
├── resources/
├── public/
└── .env
```

## Purpose

This project is part of my learning and portfolio work, focusing on:

* Understanding RESTful API development
* Building CRUD operations with Laravel
* Working with relational databases
* Connecting a web frontend to a backend API
* Understanding HTTP methods and JSON responses
* Structuring a Laravel application

## License

This project is open-sourced under the MIT License.