# Security Hardening — Refactor Log

Staged hardening of the e-Voting System. Each installment leaves the app
runnable. Originals of every modified file are backed up under
`_backup_pre_refactor/` (same relative paths).

## Installment 1 — Foundation + unauthenticated attack surface (2026-10-20)

### Added
- `.env` / `.env.example` — DB credentials moved out of source.
- `includes/config.php` — tiny `.env` loader (`env()`).
- `includes/security.php` — `e()` (output escaping), CSRF (`csrf_token`,
  `csrf_field`, `csrf_verify`), `redirect()`, `sanitize_csv_cell()`.
- `includes/bootstrap.php` — non-HTML init so pages can redirect before output.
- `db/migration_2026-10-20_security.sql` — InnoDB conversion, drop insecure
  password default, fix `adult_voter` default, unique email.
- `db/migration_vote_model.sql` — new one-row-per-vote schema (for installment 3).

### Changed
- `includes/db.php` — PDO built from `.env`; connection errors logged, never
  echoed; emulated prepares OFF; exposes `db()` plus a BC `$db` global.
- `includes/functions.php` — added parameterized API (`q`, `fetchAll`,
  `fetchOne`, `execute`). Legacy string-SQL helpers kept but marked DEPRECATED
  (removed in installment 2).
- `includes/init.php` — now `bootstrap.php` + `header.php`.
- `index.php` — login + registration rewritten:
  - Parameterized queries (fixes the auth-bypass SQL injection).
  - `session_regenerate_id(true)` on login (session fixation).
  - Safe strict SHA1 legacy check via `hash_equals`, with transparent upgrade.
  - CSRF tokens on both forms; output escaped with `e()`.
  - Real `header()` redirects instead of injected `<script>`.
  - Server-side email validation.
- `organiser/create_voter_account.php` — manual + CSV voter creation rewritten:
  - Parameterized inserts (fixes SQL injection, incl. the CSV loop).
  - CSV read from the temp upload; no longer moved into a web-served folder.
  - Per-voter random temporary passwords (removes the shared `12345678`).
  - CSV formula-injection neutralised; email validation; header-row skip;
    whole import wrapped in a transaction (all-or-nothing).

### Still open (tracked for later installments)
- Installment 2: convert remaining admin/organiser/voter pages (injection +
  CSRF + XSS); then delete the deprecated legacy helpers.
- Installment 3: vote-model rework (one row per vote, one-vote enforcement,
  transactions, correct winner query) across all pages + schema.
- Installment 4: refactor toward a minimal MVC structure + tests.


## Installment 2 - Authenticated pages: injection + CSRF + XSS (2026-10-20)

Converted every remaining admin / organiser / voter page to the parameterized
API, added CSRF tokens to all POST forms (verified in every handler), and
escaped all dynamic output with e(). Handlers now run via bootstrap.php BEFORE
any HTML, so they use real header() redirects (PRG) instead of injected
<script> navigation.

### Added to includes/security.php
- require_role($role) - top-of-page auth guard with header redirect.
- pressed_action($prefix) - safely resolves which per-row edit/delete button
  was pressed (integer id only).
- store_uploaded_image() - validates type (getimagesize) + size, writes a
  random basename into the upload dir (no attacker-controlled filenames).

### Changed - organiser/
- create_candidate_account.php - parameterized insert + vote_details seed in a
  transaction; hardened image upload.
- edit_candidate_details.php / edit_voter_details.php - top-of-page edit/delete
  handlers, parameterized + scoped to the organiser's own category; escaped rows.
- update_candidate_details.php / update_voter_details.php - parameterized
  update, optional password change (>=8 chars), escaped output, ownership check.
- change_password.php - shared hardened handler (hash_equals, parameterized).
- index.php - counts via COUNT queries; tallies via LEFT JOIN; winner from the
  ordered list; JSON-encoded chart data; parameterized voting-time update.

### Changed - admin/
- change_password.php, create_election_category.php, create_organiser_account.php
  (random one-time password), edit_account_details.php, edit_election_category.php,
  update_account_details.php (fixes the hard-coded category=2 bug),
  update_election_category.php - all parameterized + CSRF + escaped.
- request_to_register.php - approval rewritten: category + organiser created and
  the correct request row marked Approved, all in a transaction (fixes the
  legacy WHERE id='$cat' bug); random organiser password; reject parameterized.
- email_script.php - mail header-injection fixed: fixed configured From
  (MAIL_FROM), recipient/subject validated and CR/LF rejected, HTML body escaped.
- checkingmail.php - CSRF + escaping; From shown read-only from config.
- index.php - counts via COUNT; category delete is now a transactional cascade
  (fixes the $category_id vs $categoryID typo that orphaned vote rows).

### Changed - voter/
- give_vote.php - parameterized candidate list, escaped, CSRF field on the form.
- index.php - vote submit is parameterized + CSRF + transactional (PRG redirect).
  NOTE: still the per-candidate counter model; one-vote enforcement and the
  auditable one-row-per-vote model remain Installment 3.

### Changed - includes/
- view_messages_and_errors.php - messages/errors escaped with e().
- functions.php - legacy string-SQL helpers (fetchData, executeInsertQuery,
  executeUpdateQuery, executeDeleteQuery, getRowCount) REMOVED. Only the
  parameterized API (q/fetchAll/fetchOne/execute) remains.

### Env
- Added MAIL_FROM to .env / .env.example.

### Verified
- Project-wide scan: no legacy-helper calls, no concat-SQL, no script-redirects
  remain in active code (the three nav.php role-guard fallbacks are harmless and
  redundant - each page now guards via require_role before output).

### Still open
- Installment 3: vote-model rework (one row per vote, one-vote-per-election
  enforcement, atomic tally, correct winner) across voter/organiser pages +
  db/migration_vote_model.sql.
- Installment 4: refactor toward a minimal MVC structure + tests.


## Installment 3 - Vote integrity: auditable one-row-per-vote model (2026-10-20)

Replaced the per-candidate vote COUNTER with an auditable ballot model. The
result of an election is now recountable and one-vote-per-voter is enforced by
the database, not merely by the UI. I2 versions of every touched file are
snapshotted in _backup_pre_installment3/.

### Added
- db/migration_vote_model.sql (now APPLY-NOW): creates `vote`
  (id, voter_id, category_id, candidate_id, created_at) with
  UNIQUE(voter_id, category_id) enforcing one vote per election, an index for
  tallying, and ON DELETE CASCADE foreign keys to system_users / candidate /
  election_category. Legacy `vote_details` is left in place but unused
  (drop optional).

### Changed - voter/
- give_vote.php - ballot is now RADIO buttons (single selection, required)
  instead of checkboxes; added a confirm dialog and a "one candidate, final"
  notice.
- index.php - cast handler fully reworked:
  - One `candidate_id` read from the radio input.
  - Server-side VOTING WINDOW check (rejects before-start / after-end) - the
    legacy only hid the form client-side.
  - Candidate must belong to the voter's OWN category.
  - One-vote enforcement: pre-check + INSERT guarded by the UNIQUE key, with
    SQLSTATE 23000 caught and reported as "already voted".
  - Insert + voted-flag update wrapped in a transaction.
  - "Already voted" gate is now derived from the vote table (authoritative),
    not the denormalised system_users.voted flag.
  - Tally + winner derived via COUNT/GROUP BY ... ORDER BY votes DESC, with
    explicit tie and zero-vote handling (no more bubble sort).

### Changed - organiser/
- index.php - tally, leading candidate (with tie handling) and turnout
  (received vs pending) all derived from the vote table; received =
  COUNT(DISTINCT voter_id).
- create_candidate_account.php - no longer seeds a vote_details counter row
  (tally is derived on read); plain insert.
- edit_candidate_details.php - candidate delete now clears rows from `vote`.

### Changed - admin/
- index.php - category delete cascade now clears `vote` (was vote_details, via
  the already-fixed variable bug).

### Verified
- No vote_details references remain in active code.
- give_vote uses radio (no checkbox / votes[] array).
- One-vote pre-check, 23000 catch, window check and category check all present.
- CSRF + no-raw-query structural checks still pass across all pages.

### Still open
- Installment 4: refactor toward a minimal MVC structure + a few integration
  tests for the voting path.


## Installment 4 - Layered architecture + tested domain core (2026-10-20)

Scope decision: a full front-controller/router rewrite of all pages was
deliberately NOT done. It carries high risk with zero runtime verification
available in this environment (no PHP CLI / lint / server reachable) and brings
no behaviour or security benefit. Instead, the election-critical logic was
extracted into a clean, dependency-injected, unit-tested domain layer, and the
vote path was wired onto it as the reference controller. Remaining pages keep
their existing entry points and continue to work unchanged.

### Added - src/ (PSR-4 App\, autoloaded)
- Support/Clock.php, SystemClock.php, FixedClock.php - injectable "now" so the
  voting-window logic is deterministic in tests.
- Domain/Election.php - voting-window value object (hasStarted/hasEnded/isOpen,
  inclusive boundaries, null-window safe).
- Domain/Standing.php, Domain/Tally.php - derived result with ordering and
  winner/tie/no-votes/no-candidates logic in one place.
- Exception/ - VoteException base + NotEligible / VotingClosed /
  InvalidCandidate / AlreadyVoted domain exceptions (user-safe messages).
- Repository/ interfaces: CategoryRepository, CandidateRepository,
  VoteRepository.
- Repository/Pdo/ implementations - parameterized SQL; PdoVoteRepository maps
  SQLSTATE 23000 to AlreadyVotedException.
- Service/VoteService.php - castVote() enforces eligibility -> window ->
  candidate-in-category -> one-vote (pre-check + race), plus tally()/turnout().
- Service/VoteServiceFactory.php - wires PDO repos + SystemClock in one call.

### Added - tooling & tests
- composer.json (PSR-4 autoload App\ -> src/, App\Tests\ -> tests/; PHPUnit ^10
  dev dep; `composer test`).
- phpunit.xml.
- tests/Fake/ - in-memory repositories (no DB needed).
- tests/Unit/ElectionTest.php, TallyTest.php, VoteServiceTest.php - 18 cases
  covering window boundaries, tally/winner/tie, and every castVote rule incl.
  the concurrent-race path.
- tests/README.md.

### Changed
- includes/bootstrap.php - prefers vendor/autoload.php, else registers a
  minimal PSR-4 loader for App\ so the app runs with NO composer install.
- voter/index.php - now a thin controller: casts via VoteService, maps domain
  exceptions to user messages, reads tally/winner from the Tally object. No
  inline vote SQL remains.

### Not run here
- PHP CLI / PHPUnit are not reachable from this environment, so the suite was
  verified structurally (PSR-4 mapping, brace/paren balance, wiring) but NOT
  executed. Run locally: `composer install && composer test`.

### Still open (optional, mechanical)
- Migrate the remaining pages (admin/organiser dashboards + CRUD) onto the
  repository/service layer in the same thin-controller style. Pattern is
  established by voter/index.php; best done with the app runnable so each page
  can be clicked through.
