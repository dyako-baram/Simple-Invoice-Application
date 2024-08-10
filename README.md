# Invoice Application

This is a Laravel-based web application that manages products, suppliers, customers, invoices, and more. The application provides RESTful API endpoints for managing resources and includes features like authentication, data validation, file handling,log viewing and database interactions.

## Note
the `.env` file has been excluded from the `.gitignore` file for the sake of simplicity 

make sure to set the Global Enviroment variable of the web server `http://127.0.0.1:8000` as `BASE_URL` in postman 

## Features
- Log viewing using [Log viewer]('https://github.com/opcodesio/log-viewer')
- User Authentication: Secured routes with user-specific data access.
- Product Management: CRUD operations for products with supplier associations.
- Invoice Management: Create and manage invoices with detailed line items.
- Supplier Management: Manage suppliers linked to products.
- Customer Management: Handle customer records.
- File Uploads: Supports image uploads for products.
- API Rate Limiting: Protects against abuse by limiting the number of API requests.
- Security: Protection against XSS and other common vulnerabilities.

## Prerequisites
Before running this application, ensure you have the latest version of the following applications are installed:
-   composer, 
-   xammp including mysql (or similar tools)

Installation Guide
1. Clone the Repository

```bash
git clone https://github.com/dyako-baram/Simple-Invoice-Application.git
cd Simple-Invoice-Application
```
2. Install PHP Dependencies
```bash
composer install
```

3. Run Database Migrations:
if you dont have the database it will ask you to create the database

```bash
php artisan migrate
```

4. Run the Application
Start the local development server:

```bash
php artisan serve
```

## Usage:
the [postman config file]('/Invoice.postman_collection.json') has sample data for each request, make changes as needed
```
API Endpoints
Products: /api/products
Suppliers: /api/suppliers
Invoices: /api/invoices
Customers: /api/customers
For full API documentation, import the postman export file
```