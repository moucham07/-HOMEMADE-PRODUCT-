# Homemade Marketplace

This is a PHP/MySQL web application for a homemade products marketplace.

## Prerequisites
- **XAMPP** (or any other local server like WAMP/MAMP) installed on your system.
- **PHP** 7.x or 8.x
- **MySQL/MariaDB**

## How to Run the Application Locally

Follow these steps to set up and run the project on your local machine using XAMPP:

### 1. Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Start the **Apache** and **MySQL** modules.

### 2. Database Setup
1. Open your web browser and go to `http://localhost/phpmyadmin/`.
2. Click on **New** in the left sidebar to create a new database.
3. Enter `homemade_marketplace` as the database name and click **Create**.
4. Once the database is created, select it from the left sidebar.
5. Click on the **Import** tab at the top.
6. Click **Choose File** and select the `db.sql` file located in the root folder of this project (`c:\xampp\htdocs\HOMEMADE PRODUCT\db.sql`).
7. Scroll down and click **Import** to execute the SQL script. This will create all the necessary tables and populate any initial data.

### 3. Verify Configuration (Optional)
The database connection settings are configured in `config/config.php`. By default, they are set up for XAMPP:
- **Host**: `localhost`
- **Database Name**: `homemade_marketplace`
- **Username**: `root`
- **Password**: ` ` (blank)

If your local MySQL uses a different username or password, you will need to update the `$username` and `$password` variables in `config/config.php`.

### 4. Run the Project
1. Open your web browser.
2. Go to the following URL:
   ```
   http://localhost/HOMEMADE%20PRODUCT/
   ```
3. The marketplace homepage should now load successfully!

## Project Structure
- `index.php`: The main landing page / homepage.
- `login.php` / `register.php`: User authentication.
- `products.php`: View all available homemade products.
- `product-detail.php`: View details of a specific product.
- `cart.php`: User's shopping cart.
- `checkout.php`: Checkout process for orders.
- `config/`: Configuration files including database connection setup.
- `admin/`: Admin panel files.
- `seller/`: Seller dashboard and management files.
- `assets/`: Static assets such as CSS, JS, and Images.
- `includes/`: Reusable components like headers, footers, etc.
- `db.sql`: Database schema and initial data.
