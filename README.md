# e-Voting System

A secure, modern online voting web application built with **PHP** and **MySQL**. Designed for student councils, academic departments, clubs, and organizations to conduct transparent elections with role-based dashboards and automated ballot auditing.

---

## Architecture & Modernization Highlights

* **Layered Architecture:** Refactored into Domain Entities (`Candidate`, `Turnout`, `User`, `Election`), Service Layer (`VoteService`, `AuthService`, `DemoDataSeeder`), and Repositories (`CandidateRepository`, `CategoryRepository`, `UserRepository`).
* **Double-Voting Prevention:** Database-enforced `UNIQUE(voter_id, category_id)` constraint prevents multiple ballots from being cast per voter, even under concurrent requests.
* **Modern Interface:** Powered by **Bootstrap 5.3.3**, **Bootswatch Zephyr**, and **Bootstrap Icons**, featuring responsive candidate card grids, native HTML5 date/time pickers, floating form labels, and synchronized live countdown timers.
* **Security & Authentication:** Session fixation safeguards via `session_regenerate_id()`, Bcrypt (`PASSWORD_BCRYPT`) password hashing with automatic legacy upgrade verification, CSRF token validation, and PDO parameterized queries.
* **Automated Unit Testing:** Includes a zero-dependency CLI test suite runner covering the domain core with 53 unit tests and 134 assertions (`php tests/run_tests.php`).

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
└── evoting_system.sql    # Clean database schema & initial demo dataset
```

---

## Quick Setup & Running Locally

### 1. Prerequisites (macOS with Homebrew)
```bash
brew install php mysql
brew services start mysql
```

### 2. Database Initialization
Create the database and import the latest schema:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS evoting_system;"
mysql -u root evoting_system < evoting_system.sql
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
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

Alternatively, execute the automated runner script:
```bash
./run.bash
```

---

## Automated Tests

Run the domain unit test suite directly from your terminal:
```bash
php tests/run_tests.php
```
All 53 unit tests run in memory using SQLite PDO and in-memory repository fakes, executing deterministically without mutating your local MySQL database.

---

## Showcase & Test Accounts

The included dataset is populated with current non-past dates:

| Role | Email Login | Password | Flow to Explore |
|---|---|---|---|
| **Administrator** | `admin@evoting.com` | `12345678` | System management, category approvals, Demo Data generator. |
| **Organiser (Live)** | `council.organiser@evoting.com` | `password123` | Active turnout metrics (64%), voting window editor, candidate roster. |
| **Voter (Ready to Vote)** | `voter17@evoting.com` | `password123` | **Live Ballot:** Interactive candidate card grid with real-time submission. |
| **Voter (Already Voted)** | `voter1@evoting.com` | `password123` | **Confirmation State:** "Thank You for Voting" card & poll closing countdown. |
| **Voter (Upcoming)** | `fvoter1@evoting.com` | `password123` | **Opening Soon:** Countdown timer counting down to voting window start. |
| **Voter (Concluded)** | `alumni1@evoting.com` | `password123` | **Results Showcase:** Gold trophy winner banner & Google Bar chart. |

### Generating Additional Demo Data
* **From the Admin UI:** Navigate to **Demo Data** in the Admin navigation bar (`/admin/generate_demo_data.php`) for 1-click showcase setup, voter generation, and vote simulations.
* **From the Command Line:**
  ```bash
  php db/seed_demo_data.php --fresh
  ```

---

## Standalone Portfolio & GitHub Pages

This project includes a dedicated showcase portfolio package located in [`portfolio/`](portfolio):

* **Open locally:**
  ```bash
  open portfolio/index.html
  ```
* **Host on GitHub Pages:**
  The repository includes an automated GitHub Actions deployment workflow at [`.github/workflows/deploy-portfolio.yml`](.github/workflows/deploy-portfolio.yml).
  1. In your GitHub repository, navigate to **Settings** > **Pages**.
  2. Under **Build and deployment** > **Source**, select **GitHub Actions**.
  3. Push to `main`, and the portfolio will be live at:
     `https://sandycompetent.github.io/evoting_system/`

---

## License

This project is licensed under the terms included in the [`LICENSE`](LICENSE) file.
