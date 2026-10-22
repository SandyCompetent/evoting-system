# E-Voting System

An E-Voting System built with PHP and MySQL, used as a web-security hardening case study. NOTE: this is a learning project and must not be used to run a real election. This system supports multiple user roles, including Administrators, Organisers, and Voters, to manage and participate in elections seamlessly.

## Features

* **Role-Based Access Control:** Distinct dashboards and functionalities for `admin`, `organiser`, and `voter`.
* **Authentication:** Session management with `password_hash`/`password_verify`, session regeneration on login, and parameterized queries. Security hardening is in progress — see `REFACTOR_LOG.md`.
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
* Copy `.env.example` to `.env` and set your MySQL credentials there (no longer hard-coded in `includes/db.php`). Defaults: host `localhost`, user `root`, empty password.\n* Run the DB migration: `mysql -u root evoting_system < db/migration_2026-10-20_security.sql`.

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

### Default Test Accounts
If you imported the provided `evoting_system.sql` database, you can log in using the following test credentials:

**Administrator:**
* **Email:** `admin@evoting.com`
* **Password:** `admin`

**Organiser:**
* **Email:** `sandy@evoting.com` *(or `ivaan@evoting.com`)*
* **Password:** `12345678`

**Voter:**
* **Email:** `sudhan@evoting.com` *(or `john@evoting.com`)*
* **Password:** `12345678`

## License

This project is licensed under the terms included in the `LICENSE` file.
