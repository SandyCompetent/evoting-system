import os
import re

renames = {
    'edit_voter_details.php': 'edit-voter-details.php',
    'create_voter_account.php': 'create-voter-account.php',
    'edit_candidate_details.php': 'edit-candidate-details.php',
    'create_candidate_account.php': 'create-candidate-account.php',
    'update_candidate_details.php': 'update-candidate-details.php',
    'change_password.php': 'change-password.php',
    'update_voter_details.php': 'update-voter-details.php',
    'run_tests.php': 'run-tests.php',
    'view_messages_and_errors.php': 'view-messages-and-errors.php',
    'email_script.php': 'email-script.php',
    'generate_demo_data.php': 'generate-demo-data.php',
    'edit_account_details.php': 'edit-account-details.php',
    'request_to_register.php': 'request-to-register.php',
    'create_organiser_account.php': 'create-organiser-account.php',
    'update_election_category.php': 'update-election-category.php',
    'edit_election_category.php': 'edit-election-category.php',
    'checkingmail.php': 'checking-mail.php',
    'create_election_category.php': 'create-election-category.php',
    'update_account_details.php': 'update-account-details.php',
    'give_vote.php': 'give-vote.php',
    '12_mobile_voter_ballot.png': '12-mobile-voter-ballot.png',
    '08_organiser_voters.png': '08-organiser-voters.png',
    '07_organiser_candidates.png': '07-organiser-candidates.png',
    '03_admin_dashboard.png': '03-admin-dashboard.png',
    '10_voter_cast_confirmation.png': '10-voter-cast-confirmation.png',
    '02_admin_requests.png': '02-admin-requests.png',
    '09_voter_live_ballot.png': '09-voter-live-ballot.png',
    '14_mobile_hero_overview.png': '14-mobile-hero-overview.png',
    '11_voter_concluded_results.png': '11-voter-concluded-results.png',
    '13_mobile_credentials.png': '13-mobile-credentials.png',
    '05_admin_categories.png': '05-admin-categories.png',
    '06_organiser_dashboard.png': '06-organiser-dashboard.png',
    '01_portal_login.png': '01-portal-login.png',
    '04_admin_demo_generator.png': '04-admin-demo-generator.png',
    'site_icon.svg': 'site-icon.svg',
    'feature_graphic.svg': 'feature-graphic.svg',
    'migration_vote_model.sql': 'migration-vote-model.sql',
    'migration_2026-10-20_security.sql': 'migration-2026-10-20-security.sql',
    'seed_demo_data.php': 'seed-demo-data.php'
}

for root, dirs, files in os.walk('.'):
    if '.git' in root or '.idea' in root:
        continue
    for file in files:
        if file.endswith('.php') or file.endswith('.html') or file.endswith('.md') or file.endswith('.json'):
            path = os.path.join(root, file)
            with open(path, 'r') as f:
                content = f.read()
            
            new_content = content
            for old, new in renames.items():
                new_content = new_content.replace(old, new)
                
            if new_content != content:
                print(f"File needs update: {path}")

