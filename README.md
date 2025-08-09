# ITP4523M-PYTHON-PHP-PROJECT

This is a web-based system for a toy company, allowing both customers and employees to log in.  
It integrates PHP for backend logic, Python for real-time FX rate updates, and MySQL for data storage.

## 🔧 Installation Guide

To run the project locally:

1. Install and launch **XAMPP**.
2. Place the project folder (`ITP4523M-PYTHON-PHP-PROJECT`) inside the `htdocs` directory.
3. Start **Apache** and **MySQL** from the XAMPP control panel.
4. Open **phpMyAdmin** and import the provided `.sql` file to set up the database.
5. Open the project in **Visual Studio Code** for editing.
6. Access the site via your browser at:  
   `http://localhost/ITP4523M-PYTHON-PHP-PROJECT`

## Database Setup

- Use phpMyAdmin to import the SQL file.
- Make sure your database connection settings in `config.php` (or equivalent) match your local MySQL credentials.

## Python FX Rate Program

- A Python script is included to fetch **real-time foreign exchange rates**.
- This script **must be running** for FX features to function properly.
- Run the script using a Python interpreter before accessing the site.

## Test Login Account

- **Email:** XXX@example.com  
- **Password:** 123456

## Email OTP (Optional)

- The email OTP feature is currently **disabled**.
- To enable it:
  1. Open `email.php`.
  2. Enter your email address and **app-specific passcode** (e.g., from Gmail).
  3. Save the file and restart Apache.
