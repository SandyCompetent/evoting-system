# Contributing to e-Voting System

First off, thank you for considering contributing to the e-Voting System! This document provides guidelines and instructions for contributing to this repository.

> **Note:** This project is primarily built as a portfolio and demonstration piece. While it is not intended for commercial production, bug fixes, UI improvements, and test coverage expansions are always welcome.

## Development Workflow

### 1. Fork & Clone
1. Fork the repository on GitHub.
2. Clone your forked repository to your local machine.

### 2. Local Setup
Ensure you have PHP 8.0+ and MySQL installed.
```bash
# Create the database and import schema
mysql -u root -e "CREATE DATABASE IF NOT EXISTS evoting_system;"
mysql -u root evoting_system < evoting-system.sql

# Start the local development server
php -S localhost:8000
```

### 3. Making Changes
* Create a new branch for your feature or bugfix (`git checkout -b feature/your-feature-name`).
* Follow the existing layered architecture (Domain, Service, Repository).
* Ensure your code adheres to standard PHP PSR-12 coding conventions.

### 4. Running Tests
This project relies on a zero-dependency CLI test suite using SQLite in-memory databases. **Do not submit a PR if the tests are failing.**
```bash
# Run the domain unit test suite
php tests/run-tests.php
```
Ensure all 53+ unit tests pass successfully.

### 5. Submitting a Pull Request
1. Commit your changes with clear, descriptive commit messages.
2. Push your branch to your forked repository.
3. Open a Pull Request against the `main` branch of this repository.
4. Provide a clear description of what your PR solves or adds.

## Code of Conduct
Please ensure your interactions in issues and pull requests remain professional, respectful, and constructive.

Thank you for helping improve the e-Voting System!
