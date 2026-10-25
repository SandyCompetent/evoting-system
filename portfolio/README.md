# e-Voting System • Project Portfolio & Technical Showcase 2.0

Welcome to the **e-Voting System Portfolio & Technical Showcase**. This directory contains the complete visual documentation, interactive demo application, video demonstration, and high-resolution mockup catalog for the modernized e-Voting platform.

---

## 1. Interactive Demo Application

You can open the interactive portfolio web application directly in your browser:

```bash
open portfolio/index.html
```

### Key Features in `portfolio/index.html`:
- **Cinema Video Showcase & Interactive Chapter Tour**: Responsive video presentation with synchronized chapter navigation (Authentication, Organiser Controls, Voter Experience, Certified Tallies) and an autoplaying animated tour.
- **Interactive Ballot Simulator**: Test the candidate selection cards directly in the browser with active state styling, live sub-second countdown timer, and instant submission feedback.
- **Realistic macOS Window Chrome & Mobile Frames**: All 14 screenshots are framed in authentic macOS browser headers (with traffic light controls and URL pills) and mobile Safari viewports.
- **Lightbox 2.0 with Keyboard Navigation**: Fullscreen modal with left/right arrow key navigation (`←` / `→`) and index tracking across all 14 mockups.
- **Architecture & Code Inspector**: Interactive tabbed code viewer showcasing real production PHP code (`src/Domain/Election.php`, `src/Service/VoteService.php`, `includes/security.php`, `assets/css/style.css`).
- **Dark / Light Theme Switcher**: Dynamic theme toggle with smooth transitions and `localStorage` persistence.
- **100% WCAG AAA Color Contrast**: Perfect 100/100 Lighthouse Accessibility score with high-contrast text and semantic heading hierarchy.
- **1-Click Login Credentials**: Interactive copy buttons with modern toast notifications.

---

## 2. Screenshot & Mockup Catalog

All screenshots are captured directly from the project across desktop and mobile viewports:

| Mockup | File | Format | Description |
|---|---|---|---|
| **01. Portal Login** | <a href="images/01-portal-login.png" target="_blank">images/01-portal-login.png</a> | Desktop 1440x900 | Public sign-in card with floating labels and clean trust note. |
| **02. Host Requests** | <a href="images/02-admin-requests.png" target="_blank">images/02-admin-requests.png</a> | Desktop 1440x900 | Administrator queue for reviewing and approving election requests. |
| **03. Admin Dashboard** | <a href="images/03-admin-dashboard.png" target="_blank">images/03-admin-dashboard.png</a> | Desktop 1440x900 | Administrator overview with 4 KPI tiles, category list, and Google chart. |
| **04. Demo Generator** | <a href="images/04-admin-demo-generator.png" target="_blank">images/04-admin-demo-generator.png</a> | Desktop 1440x900 | 1-Click showcase data generator and credentials cheat-sheet. |
| **05. Category Management** | <a href="images/05-admin-categories.png" target="_blank">images/05-admin-categories.png</a> | Desktop 1440x900 | Category roster with RESTful edit links and cascade deletions. |
| **06. Organiser Dashboard** | <a href="images/06-organiser-dashboard.png" target="_blank">images/06-organiser-dashboard.png</a> | Desktop 1440x900 | Organiser dashboard with real-time turnout %, voting window editor, and charts. |
| **07. Candidate Roster** | <a href="images/07-organiser-candidates.png" target="_blank">images/07-organiser-candidates.png</a> | Desktop 1440x900 | Candidate management with photos, symbol badges, and active-voting safeguards. |
| **08. Voter Accounts** | <a href="images/08-organiser-voters.png" target="_blank">images/08-organiser-voters.png</a> | Desktop 1440x900 | Registered voter accounts with DOB and authoritative ballot status. |
| **09. Live Voter Ballot** | <a href="images/09-voter-live-ballot.png" target="_blank">images/09-voter-live-ballot.png</a> | Desktop 1440x900 | Responsive candidate card grid with selection highlights and countdown timer. |
| **10. Vote Confirmation** | <a href="images/10-voter-cast-confirmation.png" target="_blank">images/10-voter-cast-confirmation.png</a> | Desktop 1440x900 | Post-vote confirmation screen with glassmorphic countdown to polls closing. |
| **11. Concluded Results** | <a href="images/11-voter-concluded-results.png" target="_blank">images/11-voter-concluded-results.png</a> | Desktop 1440x900 | Certified election results with gold trophy winner banner and Google Bar chart. |
| **12. Mobile Ballot View** | <a href="images/12-mobile-voter-ballot.png" target="_blank">images/12-mobile-voter-ballot.png</a> | Mobile 390x844 | Touch-friendly candidate card selection on mobile iPhone viewport. |
| **13. Mobile Credentials** | <a href="images/13-mobile-credentials.png" target="_blank">images/13-mobile-credentials.png</a> | Mobile 390x844 | Responsive demonstration account roster and touch actions. |
| **14. Mobile Portal Hero** | <a href="images/14-mobile-hero-overview.png" target="_blank">images/14-mobile-hero-overview.png</a> | Mobile 390x844 | Stacked header and quick metrics on mobile handheld screens. |

---

## 3. Live Demonstration Guide

To run the live PHP application locally:

### Step 1: Start the Local Development Server
```bash
php -S localhost:8000
```
Open **<a href="https://evoting-system.freehosting.dev/?i=1" target="_blank">https://evoting-system.freehosting.dev/?i=1</a>** in your browser.

### Step 2: Showcase Logins (Password: `password123`)

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

A GitHub Actions workflow is provided at `.github/workflows/deploy-portfolio.yml`.

To publish the portfolio online:
1. In your GitHub repository, go to **Settings** > **Pages**.
2. Under **Build and deployment** > **Source**, select **GitHub Actions**.
3. Push your repository to GitHub:
   ```bash
   git add .
   git commit -m "Elevate portfolio with video showcase, mobile mockups, and dark mode"
   git push origin main
   ```
4. GitHub Actions will automatically deploy the contents of the `portfolio/` directory.
5. Your live portfolio will be available at:
   **<a href="https://evoting-system.freehosting.dev/?i=1" target="_blank">https://evoting-system.freehosting.dev/?i=1</a>**
