# e-Voting System • Project Portfolio & Visual Showcase

Welcome to the **e-Voting System Portfolio**. This directory contains the complete visual documentation, interactive demo application, and high-resolution screenshot catalog for the modernized e-Voting platform.

---

## 1. Interactive Demo Application

You can open the interactive portfolio web application directly in your browser:

```bash
open portfolio/index.html
```

### Features included in `portfolio/index.html`:
- **Interactive Ballot Simulator**: Test the voter candidate selection cards directly in the browser with active state styling and instant submission feedback.
- **Visual Screenshot Gallery with Lightbox**: Filter screenshots by user flow (Admin, Organiser, Voter, Public) and click any card to inspect the full-resolution 1440x900 image.
- **Architecture Highlights**: Clear breakdown of the Domain/Service/Repository layer, Bootstrap 3-to-5 Compatibility Bridge, and security hardening.
- **1-Click Login Credentials**: Interactive copy buttons for test accounts across all roles.

---

## 2. Screenshot Catalog

All screenshots are captured at 1440x900 resolution directly from the running project with current, non-past dates:

| Screenshot | File | Description |
|---|---|---|
| **01. Portal Login** | [images/01_portal_login.png](images/01_portal_login.png) | Public sign-in card with floating labels and clean trust note. |
| **02. Host Requests** | [images/02_admin_requests.png](images/02_admin_requests.png) | Administrator queue for reviewing and approving election requests. |
| **03. Admin Dashboard** | [images/03_admin_dashboard.png](images/03_admin_dashboard.png) | Administrator overview with 4 KPI tiles, category list, and Google chart. |
| **04. Demo Generator** | [images/04_admin_demo_generator.png](images/04_admin_demo_generator.png) | 1-Click showcase data generator and credentials cheat-sheet. |
| **05. Category Management** | [images/05_admin_categories.png](images/05_admin_categories.png) | Category roster with RESTful edit links and cascade deletions. |
| **06. Organiser Dashboard** | [images/06_organiser_dashboard.png](images/06_organiser_dashboard.png) | Organiser dashboard with real-time turnout %, voting window editor, and charts. |
| **07. Candidate Roster** | [images/07_organiser_candidates.png](images/07_organiser_candidates.png) | Candidate management with photos, symbol badges, and active-voting safeguards. |
| **08. Voter Accounts** | [images/08_organiser_voters.png](images/08_organiser_voters.png) | Registered voter accounts with DOB and authoritative ballot status. |
| **09. Live Voter Ballot** | [images/09_voter_live_ballot.png](images/09_voter_live_ballot.png) | Responsive candidate card grid with selection highlights and countdown timer. |
| **10. Vote Confirmation** | [images/10_voter_cast_confirmation.png](images/10_voter_cast_confirmation.png) | Post-vote confirmation screen with glassmorphic countdown to polls closing. |
| **11. Concluded Results** | [images/11_voter_concluded_results.png](images/11_voter_concluded_results.png) | Certified election results with gold trophy winner banner and Google Bar chart. |

---

## 3. Live Demonstration Guide

To demo the live running application:

### Step 1: Start the Local Development Server
```bash
php -S localhost:8000
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

### Step 2: Use Showcase Logins (Password: `password123`)

| Role | Email Login | Password | Flow to Demonstrate |
|---|---|---|---|
| **Admin** | `admin@evoting.com` | `12345678` | System management, category creation, approve requests, Demo Data generator. |
| **Organiser** | `council.organiser@evoting.com` | `password123` | Live election dashboard, turnout % (64%), configure voting window. |
| **Voter (Ready)** | `voter17@evoting.com` | `password123` | **Live Ballot:** Log in to experience candidate card selection and cast a live vote! |
| **Voter (Done)** | `voter1@evoting.com` | `password123` | **Confirmation:** Shows "Thank You for Voting" confirmation and poll close countdown. |
| **Voter (Upcoming)** | `fvoter1@evoting.com` | `password123` | **Countdown:** Shows live timer counting down until polls open. |
| **Voter (Concluded)** | `alumni1@evoting.com` | `password123` | **Results:** Shows gold trophy winner banner and Google Bar chart distribution. |

---

## 4. Hosting the Portfolio on GitHub Pages

This portfolio is 100% static HTML/CSS/JS and is designed to be hosted directly on **GitHub Pages**:

### Automated GitHub Pages Deployment (Included)
A GitHub Actions workflow is provided at `.github/workflows/deploy-portfolio.yml`.

To publish the portfolio online:
1. In your GitHub repository, go to **Settings** > **Pages**.
2. Under **Build and deployment** > **Source**, select **GitHub Actions**.
3. Push your repository to GitHub:
   ```bash
   git add .
   git commit -m "Add GitHub Pages portfolio deployment workflow"
   git push origin main
   ```
4. GitHub Actions will automatically deploy the contents of the `portfolio/` directory.
5. Your live portfolio will be available at:
   **`http://evoting-system.freehosting.dev`**

