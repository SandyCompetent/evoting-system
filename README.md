# E-Voting System

A secure and robust E-Voting System built with PHP and MySQL. This system supports multiple user roles, including Administrators, Organisers, and Voters, to manage and participate in elections seamlessly.

## Features

* **Role-Based Access Control:** Distinct dashboards and functionalities for `admin`, `organiser`, and `voter`.
* **Secure Authentication:** Session management and hashed passwords for secure access.
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

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   ```

2. **Set up the Database:**
   * Create a new MySQL database (e.g., `evoting_system`).
   * Import the provided `evoting_system.sql` file into your database to create the required tables.

3. **Configure the Application:**
   * Ensure your database configuration in the `includes/` directory (e.g., `init.php` or `db.php`) matches your local MySQL credentials.

4. **Run the Application:**
   * Host the project folder on a local server environment (like XAMPP, MAMP, WAMP, or Laravel Valet).
   * Open your browser and navigate to the project directory (e.g., `http://localhost/evoting_system`).

## Usage

* Navigate to `index.php` to access the login page.
* You will be automatically redirected to the appropriate dashboard (`/admin`, `/organiser`, or `/voter`) based on your assigned role.

## License

This project is licensed under the terms included in the `LICENSE` file.
