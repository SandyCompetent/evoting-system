# e-Voting System

<a href="https://github.com/SandyCompetent/evoting-system/actions/workflows/ci-tests.yml" target="_blank" rel="noopener noreferrer"><img src="https://github.com/SandyCompetent/evoting-system/actions/workflows/ci-tests.yml/badge.svg?branch=main" alt="Tests"></a>
<a href="https://php.net/" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/php-%3E%3D8.0-blue.svg" alt="PHP Version"></a>
<a href="https://opensource.org/licenses/MIT" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License: MIT"></a>

> **Disclaimer:** This project is intended exclusively for **Portfolio and Demo** purposes. It is **not for commercial use**.

🚀 **Live Demo:** <a href="https://evoting-system.freehosting.dev" target="_blank" rel="noopener noreferrer">https://evoting-system.freehosting.dev</a>

A secure, modern online voting web application built with **PHP** and **MySQL**. Designed for student councils, academic departments, clubs, and organizations to conduct transparent elections with role-based dashboards and automated ballot auditing.

---

## Architecture & Modernization Highlights

* **Layered Architecture:** Refactored into Domain Entities (`Candidate`, `Turnout`, `User`, `Election`), Service Layer (`VoteService`, `AuthService`, `DemoDataSeeder`), and Repositories (`CandidateRepository`, `CategoryRepository`, `UserRepository`).
* **Double-Voting Prevention:** Database-enforced `UNIQUE(voter_id, category_id)` constraint prevents multiple ballots from being cast per voter, even under concurrent requests.
* **Modern Interface:** Powered by **Bootstrap 5.3.3**, **Bootswatch Zephyr**, and **Bootstrap Icons**, featuring responsive candidate card grids, native HTML5 date/time pickers, floating form labels, and synchronized live countdown timers.
* **Security & Authentication:** Session fixation safeguards via `session_regenerate_id()`, Bcrypt (`PASSWORD_BCRYPT`) password hashing with automatic legacy upgrade verification, CSRF token validation, and PDO parameterized queries.
* **Automated Unit Testing:** Includes a zero-dependency CLI test suite runner covering the domain core with 53 unit tests and 134 assertions (`php tests/run-tests.php`).

---

## Directory Structure

```
├── .github/workflows/    # CI/CD workflows, including GitHub Pages deployment
├── admin/                # Administrator dashboard, election categories, registration review
├── organiser/            # Election organiser portal, candidate rosters, voter lists
├── voter/                # Voter interface, interactive ballot card grid, results showcase
├── src/                  # Layered PHP application core (Domain, Service, Repository, Exception)
├── includes/             # Shared bootstrap, security helpers, layout header/footer
├── assets/               # CSS styles (with Bootstrap 3-to-5 bridge) and JavaScript
├── db/                   # Database migrations and CLI demo data seeding tools
├── tests/                # Domain test runner, unit test suites, and in-memory test fakes
├── upload/               # Upload directory for candidate photos and party symbols
├── portfolio/            # Standalone showcase application & 11 high-res screenshots
└── evoting-system.sql    # Clean database schema & initial demo dataset
```

---

## Quick Setup & Running Locally

### 1. Prerequisites

Ensure you have **PHP 8.0+** and **MySQL** installed. 

**macOS (Homebrew):**
```bash
brew install php mysql
brew services start mysql
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php-cli php-mysql mysql-server
sudo systemctl start mysql
```

**Windows:**
We recommend using <a href="https://www.apachefriends.org/index.html" target="_blank" rel="noopener noreferrer">XAMPP</a> or <a href="https://laragon.org/" target="_blank" rel="noopener noreferrer">Laragon</a> which bundle PHP and MySQL together. Start the Apache and MySQL services from their respective control panels.


### 2. Database Initialization
Create the database and import the latest schema:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS evoting_system;"
mysql -u root evoting_system < evoting-system.sql
```

### 3. Application Configuration
Copy the sample environment file and configure your database credentials if different from the defaults:
```bash
cp .env.example .env
```
*(Default settings: host `localhost`, user `root`, empty password, database `evoting_system`).*

### 4. Start the Application
Run PHP's built-in development server:
```bash
php -S localhost:8000
```
Open **<a href="http://localhost:8000" target="_blank" rel="noopener noreferrer">http://localhost:8000</a>** in your browser.

Alternatively, execute the automated runner script:
```bash
./run.bash
```

---

## Automated Tests

Run the domain unit test suite directly from your terminal:
```bash
php tests/run-tests.php
```
All 53 unit tests run in memory using SQLite PDO and in-memory repository fakes, executing deterministically without mutating your local MySQL database.

---

## Showcase & Test Accounts

The default dataset includes sample accounts across active, upcoming, and concluded elections:

| Role | Email Login | Password | Flow to Explore |
|---|---|---|---|
| **Administrator** | `admin@evoting.com` | `12345678` | System management, category approvals, Demo Data generator. |
| **Organiser (Live)** | `council.organiser@evoting.com` | `password123` | Active turnout metrics (64%), voting window editor, candidate roster. |
| **Voter (Ready to Vote)** | `voter17@evoting.com` | `password123` | **Live Ballot:** Interactive candidate card grid with real-time submission. |
| **Voter (Already Voted)** | `voter1@evoting.com` | `password123` | **Confirmation State:** "Thank You for Voting" card & poll closing countdown. |
| **Voter (Upcoming)** | `fvoter1@evoting.com` | `password123` | **Opening Soon:** Countdown timer counting down to voting window start. |
| **Voter (Concluded)** | `alumni1@evoting.com` | `password123` | **Results Showcase:** Gold trophy winner banner & Google Bar chart. |

### Generating Additional Demo Data
* **From the Admin UI:** Navigate to **Demo Data** in the Admin navigation bar (`/admin/generate-demo-data.php`) for 1-click showcase setup, voter generation, and vote simulations.
* **From the Command Line:**
  ```bash
  php db/seed-demo-data.php --fresh
  ```

---

## License

This project is intended exclusively for **Portfolio and Demo** purposes. It is **not for commercial use**. Please review the <a href="LICENSE" target="_blank" rel="noopener noreferrer">`LICENSE`</a> file for further details.
