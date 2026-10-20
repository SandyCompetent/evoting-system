# E-Voting System

A secure and robust E-Voting System built with PHP and MySQL. This system supports multiple user roles, including Administrators, Organisers, and Voters, to manage and participate in elections seamlessly.

## Features

* **Role-Based Access Control:** Distinct dashboards and functionalities for `admin`, `organiser`, and `voter`.
* **Secure Authentication:** Session management and modern password hashing (`password_hash` & `password_verify`) for secure access.
* **Database Driven:** Powered by a MySQL database for storing election data, candidates, and votes securely.
* **Easy Setup:** Included SQL dump (`evoting_system.sql`) for quick initialization.

## Directory Structure

* `admin/`: Administrator dashboard and functionalities.
* `organiser/`: Election organiser dashboard.
* `voter/`: Voter interface for casting votes.
* `includes/`: Core PHP scripts, database initialization (`init.php`), and shared resources.
* `assets/`: CSS, JavaScript, and image assets.
* `upload/`: Directory for user-uploaded content (e.g., candidate photos).
* `Screenshots/`: Application screenshots.

## Installation & Setup

### Prerequisites (macOS/Homebrew)
If you are on macOS, you can easily install the required environment via terminal:
```bash
brew install php mysql
brew services start mysql
```

### 1. Set up the Database
Log into your local MySQL instance:
```bash
mysql -u root
```
Create the database and exit:
```sql
CREATE DATABASE evoting_system;
EXIT;
```
Import the provided `evoting_system.sql` file into your database to create the required tables:
```bash
mysql -u root evoting_system < evoting_system.sql
```

### 2. Configure the Application
* Ensure your database configuration in `includes/db.php` matches your local MySQL credentials. The default is `localhost`, user `root`, with no password.

### 3. Run the Application
You can host the project folder on a local server environment (like XAMPP, MAMP, WAMP), or simply use PHP's built-in development server. 

To use the built-in server, open your terminal, navigate to the project root directory, and run:
```bash
php -S localhost:8000
```
Open your browser and navigate to: **http://localhost:8000**

## Usage

* Navigate to the root URL (e.g. `http://localhost:8000/`) to access the login page.
* You will be automatically redirected to the appropriate dashboard (`/admin`, `/organiser`, or `/voter`) based on your assigned role.

## License

This project is licensed under the terms included in the `LICENSE` file.
