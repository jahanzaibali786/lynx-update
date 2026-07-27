# AGENTS.md

# Laravel ERP Engineering Guide

This file defines the repository-wide rules for AI coding agents and human contributors working on this ERP.

Every agent must read this file before inspecting, changing, generating, or deleting code.

Directory-specific `AGENTS.md` files may add stricter rules for a module. The closest applicable file wins for module-specific implementation details, but no child instruction may weaken the data-integrity, accounting, security, authorization, or production-safety rules in this file.

---

## 1. Mission and Priority Order

Maintain a stable, secure, scalable, production-ready ERP while making the smallest complete change required by the task.

Use this priority order when requirements conflict:

1. Data integrity
2. Accounting accuracy
3. Authorization and tenant isolation
4. Backward compatibility
5. Security
6. Correct business behavior
7. Performance
8. Readability and maintainability
9. Developer experience

Never trade accounting correctness, authorization, branch isolation, or historical traceability for implementation convenience.

---

## 2. Actual Project Stack

Treat `composer.lock`, the existing source code, and runtime configuration as authoritative. `composer.json` defines allowed versions, not necessarily the exact installed versions.

Current backend constraints from `composer.json`:

* PHP: `^8.0.2`
* Laravel Framework: `^9.11`
* Laravel Sanctum: `^2.13`
* Laravel UI: `^4.1`
* Laravel Modules: `nwidart/laravel-modules ^9`
* Spatie Laravel Permission: `^5.5`
* Yajra DataTables: `^10.11`
* Laravel Excel: `^3.1`
* Intervention Image: `^3.11`
* DomPDF, mPDF, FPDF, and FPDI are available
* AWS S3 Flysystem adapter is available
* Browsershot is available
* Chatify is available
* Google Calendar integration is available
* Multiple payment gateway SDKs are installed

Current frontend conventions supplied for this project:

* Blade
* Bootstrap
* jQuery
* Ajax
* Select2
* DataTables
* ApexCharts or Chart.js where already used

Do not assume Laravel 12, PHP 8.3, Livewire, Vue, React, Inertia, Alpine, Tailwind, Redis, or Spatie Media Library unless the repository proves they are installed and in active use.

Before using a package API, inspect the installed version:

```bash
composer show vendor/package
```

## 3. Sources of Truth

Use this order when determining existing behavior:

1. Current task requirements
2. This `AGENTS.md` and applicable module-level `AGENTS.md`
3. Automated tests
4. Database schema and migrations
5. Configuration files
6. Existing comments and old documentation

Do not trust comments, copied snippets, or stale documentation when they conflict with tested runtime behavior.

When business rules are unclear, inspect the existing implementation and preserve current behavior. State any necessary assumption in the handoff.

---

## 4. Non-Negotiable Safety Rules

Never:

* Delete production data.
* Run destructive database commands such as `migrate:fresh`, `db:wipe`, `DROP`, or unscoped bulk deletes.
* Edit or remove old migration files that may already have run.
* Modify production schema manually.
* Rename or remove existing columns without an explicit migration and compatibility plan.
* Delete posted journals, vouchers, receipts, payments, stock movements, payroll postings, or audit history.
* Change immutable voucher numbers after posting.
* Remove existing business logic without understanding its consumers.
* Break existing routes, APIs, jobs, scheduled commands, exports, or integrations.
* Bypass authorization, tenant scopes, school scopes, cluster scopes, or branch restrictions.
* Hardcode account IDs, role IDs, permission IDs, school IDs, branch IDs, user IDs, status IDs, or environment-specific paths.
* Introduce a new framework, package, architectural layer, or abstraction without a clear need.
* Rewrite a working file merely to improve style.
* Modify unrelated files.
* Commit secrets, credentials, tokens, generated files, vendor files, or environment files.

Always:

* Keep changes minimal and task-scoped.
* Preserve backward compatibility unless a breaking change is explicitly approved.
* Reuse existing services, helpers, traits, scopes, components, and conventions.
* Use migrations for schema changes.
* Use transactions for operations that must succeed or fail together.
* Warn clearly before any risky, irreversible, or high-impact operation.
* Preserve financial and operational history.
* Make adjustments traceable and auditable.

---

## 5. Required Agent Workflow

For every task:

1. Restate the requirement internally in concrete technical terms.
2. Inspect the repository structure and applicable `AGENTS.md` files.
3. Search for the existing implementation before creating anything new.
4. Read the complete execution path, including routes, requests, controllers, services, repositories, models, scopes, policies, jobs, events, listeners, views, JavaScript, migrations, tests, and configuration as applicable.
5. Identify the root cause or exact extension point.
6. Check accounting, authorization, data-isolation, inventory, and backward-compatibility impact.
7. Form a minimal implementation plan.
8. Implement the smallest complete fix.
9. Add or update tests where practical.
10. Run focused validation first, then the broader relevant test suite.
11. Review the final diff for unrelated or accidental changes.
12. Report changed files, behavior, tests run, risks, assumptions, and any unverified items.

Do not begin by generating new files before checking whether an equivalent implementation already exists.

Do not rewrite large files unless explicitly requested or unavoidable for correctness.

---

## 6. Architecture and Project Structure

Controllers must remain thin.

Preferred flow:

```text
Route
  -> Form Request
  -> Controller
  -> Service or Action
  -> Repository or Query Object when already used or genuinely needed
  -> Model / Database
  -> Resource, View Model, or Blade View
```

Business rules belong in services, actions, domain classes, models, policies, jobs, or dedicated query objects—not in controllers, routes, Blade templates, or JavaScript.

Use the architecture already established in the affected module. Do not introduce repositories merely to satisfy a pattern when the project does not use them there.

Prefer:

* Dependency injection
* Route model binding
* Form Requests
* Policies and Gates
* Services or single-purpose Actions
* Jobs and queues
* Events and listeners
* Observers where their side effects are explicit and testable
* Enums when compatible with the project and database behavior
* Casts
* Query scopes
* Accessors and mutators
* API Resources for existing APIs
* Database transactions

Avoid:

* Massive controllers
* God services
* Hidden global state
* Duplicate queries
* Duplicate business rules
* Business logic in Blade
* Business logic in JavaScript
* Raw SQL without a measured or demonstrated need
* Static helper sprawl
* New abstractions with only one trivial caller

---

## 7. Modular Architecture

This project uses `nwidart/laravel-modules`.

Before changing a feature, check both the root application and the relevant `Modules/*` directory.

Inspect as applicable:

* `Modules/<Module>/Config`
* `Modules/<Module>/Database/Migrations`
* `Modules/<Module>/Database/Seeders`
* `Modules/<Module>/Entities` or `Models`
* `Modules/<Module>/Http/Controllers`
* `Modules/<Module>/Http/Requests`
* `Modules/<Module>/Policies`
* `Modules/<Module>/Providers`
* `Modules/<Module>/Resources/views`
* `Modules/<Module>/Routes`
* `module.json`

Do not duplicate a module feature in `app/` when the source of truth is inside a module, or vice versa.

Preserve module enable/disable behavior and service-provider registration.

---

## 8. Authorization and Permission System

The project uses `spatie/laravel-permission ^5.5`.

The authorization model must support:

* Role-based permissions
* Direct user permissions
* Record-level authorization
* Branch, school, office, and cluster data isolation

### 8.1 Core Model

Use this model:

```text
Permission = a capability checked by the application
Role       = a named collection of permissions
User       = receives permissions through roles and may receive exceptional direct permissions
Policy     = decides whether the user may perform that capability on a specific record
Scope      = limits which records the user can query or see
```

Effective permission is additive:

```text
Effective permissions
= permissions inherited from roles
+ direct permissions assigned to the user
+ an explicitly defined super-admin bypass, if the project uses one
```

Spatie direct permissions do not create a deny rule. Removing a direct permission does not override the same permission inherited through a role.

Do not invent user-level denial semantics. If explicit deny or override behavior is required, design it as a separate, documented authorization layer with clear precedence and tests.

### 8.2 Role-First, User-Exception Strategy

Use roles for normal access management.

Use direct user permissions only for exceptional cases such as:

* Temporary additional access
* A single-user operational responsibility
* A controlled exception that does not justify creating another role

Do not assign large permission sets directly to many users. That creates an unmanageable shadow-role system.

### 8.3 Check Permissions, Not Role Names

Business code should normally check capabilities:

```php
$user->can('fees.collect');

$this->authorize('update', $student);
```

Blade should use Laravel authorization directives:

```blade
@can('fees.collect')
    <!-- Authorized action -->
@endcan
```

Routes may use permission middleware when appropriate:

```php
Route::post('/fees/{student}/collect', ...)
    ->middleware('permission:fees.collect');
```

Do not scatter checks such as `role_id == 1` or `hasRole('Admin')` through business code.

Role checks are acceptable only when the role itself is the business concept, in role-management screens, compatibility code during migration, or a narrowly defined super-admin mechanism.

### 8.4 Guard Rules

Use the existing authentication guard. For normal web users, this is typically `web`, but the agent must inspect `config/auth.php`, the User model, and existing permission records before creating permissions.

Do not create duplicate permissions under multiple guards unless the application genuinely has separate authenticated user types that require them.

Every role and permission operation must use a consistent `guard_name`.

### 8.5 User Model

The authenticatable user model should use Spatie's `HasRoles` trait if it does not already.

Do not add the trait blindly. First inspect:

* Existing role relations
* A legacy `role_id` column
* Custom `roles()` or `permissions()` methods
* Existing traits with overlapping method names
* Guard configuration
* Tenant or cluster traits

Resolve conflicts explicitly and preserve backward compatibility.

### 8.6 Policies and Record-Level Access

A permission answers: “May this user perform this type of action?”

A Policy answers: “May this user perform the action on this specific record?”

Example:

```php
public function update(User $user, Student $student): bool
{
    return $user->can('students.update')
        && $user->school_id === $student->school_id;
}
```

Use the project's actual school, office, branch, or cluster relationship instead of copying this example literally.

Never treat a global permission as permission to access every tenant's records.

### 8.7 Tenant, Cluster, School, and Branch Isolation

Role and permission checks do not replace data isolation.

Every query must still apply the project's tenant, cluster, school, branch, and office restrictions.

Preserve existing global scopes and traits such as cluster-scoping behavior.

Do not use `withoutGlobalScopes()`, `withoutGlobalScope()`, cross-school joins, or unqualified bulk operations unless explicitly required, authorized, and safely constrained.

Authorization must enforce both:

```text
Capability check + data-scope check
```

A user with `fees.view` may view only the fee records allowed by their assigned organization scope.

### 8.8 Super Admin

Use a single, explicit super-admin strategy.

Preferred pattern when compatible with the existing project:

```php
Gate::before(function (User $user, string $ability) {
    return $user->hasRole('Super Admin') ? true : null;
});
```

Do not combine multiple unrelated bypasses such as role IDs, email addresses, user IDs, and permission wildcards.

Do not apply a super-admin bypass to operations that must remain prohibited by business rules, such as deleting posted journals, unless explicitly designed and approved.

Protect against:

* Removing the last super admin
* A user removing their own ability to manage access
* Deleting protected system roles
* Renaming protected roles without a migration plan

### 8.9 Permission Administration

Permission-management screens must themselves be protected by permissions such as:

```text
roles.manage
permissions.manage
users.permissions.manage
```

Only authorized administrators may:

* Create or rename roles
* Assign permissions to roles
* Assign roles to users
* Assign direct permissions to users
* Remove roles or direct permissions

Wrap related changes in a transaction.

Validate every submitted role and permission ID against the correct guard and allowed organization scope.

Use package methods such as `syncRoles()` and `syncPermissions()` carefully. Do not accidentally remove unrelated existing assignments.

### 8.10 Auditing Permission Changes

Every access-control change must be auditable.

Record at minimum:

* Acting user
* Target user or role
* Added roles
* Removed roles
* Added permissions
* Removed permissions
* Timestamp
* Relevant tenant, school, office, branch, or cluster context

Do not log tokens, passwords, or unrelated personal data.

### 8.11 Permission Cache

Spatie caches permissions.

After seeders, migrations, scripted permission updates, or troubleshooting stale permission behavior, use the package-supported cache reset command for the installed version:

```bash
php artisan permission:cache-reset
```

Application code should use package assignment methods so normal cache invalidation behavior is preserved.

Do not directly manipulate Spatie pivot tables unless there is a documented migration requirement.

### 8.12 Permission Seeders

Permission and protected-role seeders must be idempotent.

Use stable names and `firstOrCreate`, `updateOrCreate`, or package APIs as appropriate.

Do not delete unknown roles or permissions from production simply because they are absent from a seeder.

Seeders must not reset administrators' assignments or wipe direct user permissions.

### 8.13 Legacy Authorization Migration

The existing codebase may contain legacy `role_id` checks or custom authorization logic.

Before adding the new permission system, inventory:

```text
role_id
hasRole(
hasPermissionTo(
can(
@can
@role
middleware('role
middleware('permission
Gate::
authorize(
Policies
custom permission helpers
menu visibility checks
```

Do not remove legacy authorization in one broad rewrite.

Use an incremental migration:

1. Document existing roles and checks.
2. Define canonical permission names.
3. Create idempotent role and permission seeders.
4. Map existing roles to Spatie roles without changing user access.
5. Add policy and permission checks to one module at a time.
6. Keep a temporary compatibility layer where required.
7. Add tests proving old and new behavior match.
8. Remove legacy checks only after coverage and migration verification.
9. Avoid maintaining two permanent sources of truth.

### 8.14 Permission Test Matrix

For every protected feature, test at minimum:

* User receives access through a role.
* User receives access through a direct permission.
* User without permission is denied.
* Direct permission removal does not remove role-inherited access.
* Role permission removal does not remove an equivalent direct grant.
* Wrong guard does not grant access.
* Authorized user cannot access another school, branch, office, or cluster's record.
* UI visibility matches backend authorization.
* Direct URL or crafted Ajax requests are denied when unauthorized.
* Permission cache does not leave stale access after supported updates.

Never rely only on hidden buttons. Backend authorization is mandatory.

---

## 9. Validation

Use Form Request classes for non-trivial input.

Controller-level validation is acceptable only for very small existing flows where adding a request class would create unnecessary inconsistency.

Validate:

* Required and nullable fields
* Numeric ranges and precision
* Dates and date ordering
* Foreign keys and organization ownership
* Enum-like values
* Arrays and nested arrays
* Uploaded file type, MIME type, extension, and size
* Permission, role, branch, school, office, bank, account, and session IDs

Validation does not replace authorization.

Do not trust hidden inputs, disabled form fields, JavaScript-calculated totals, or client-provided tenant IDs.

---

## 10. Database Rules

Prefer Eloquent or Query Builder when they remain clear and efficient.

Use raw SQL only when necessary for correctness or demonstrated performance, and bind all parameters.

Never use `SELECT *` in new report, export, join, or high-volume code. Select only required columns.

Always consider:

* N+1 queries
* Eager loading
* Indexes
* Composite indexes matching filter and join patterns
* Pagination
* Aggregation in SQL
* Query plan impact
* Memory usage
* Lock duration
* Concurrency
* Duplicate submissions
* Idempotency

Use:

```php
DB::transaction(function () {
    // Atomic operation
});
```

for multi-table operations that represent one business action.

Use `lockForUpdate()` or another concurrency strategy when duplicate voucher numbers, double payments, overselling, or conflicting approvals are possible.

Qualify ambiguous columns in joins.

Avoid functions on indexed filter columns when a range condition can be used instead.

For large datasets, prefer:

* `chunkById()`
* `lazyById()`
* `cursor()` when safe for the query and connection behavior
* Pagination
* Database aggregation
* Queued exports

Do not use `Model::all()` for unbounded operational tables.

---

## 11. Migrations and Schema Changes

Never edit an old migration that may have run in any environment.

Create a new migration for every schema change.

A migration must:

* Be reversible where reasonably possible
* Preserve existing data
* Handle nullability and defaults deliberately
* Add indexes needed by the new access pattern
* Avoid long table locks where possible
* Be safe with existing rows
* Avoid destructive changes without an explicit staged plan

Do not assume production has the same data quality as local development.

For risky changes, use an expand-and-contract approach:

1. Add the new structure.
2. Backfill safely.
3. Deploy compatible code.
4. Verify.
5. Remove old structure in a later deployment only when approved.

---

## 12. Accounting Rules

Accounting correctness is the highest business priority.

Every posted journal entry must satisfy:

```text
Total Debit = Total Credit
```

Never post an unbalanced journal.

Never delete or silently edit posted accounting entries.

Use reversal and replacement entries for corrections.

Preserve references between source transactions, vouchers, journals, ledgers, receipts, payments, branches, schools, users, and audit records.

Use decimal-safe database values and consistent rounding rules. Do not use binary floating-point arithmetic for money.

Prevent duplicate posting and double submission.

Financial operations that update multiple records must be atomic.

---

## 13. Voucher Rules

System-generated and manually created vouchers use separate numbering sequences unless the existing module explicitly defines otherwise.

Examples:

```text
JV-000245
M-JV-000041
```

Never mix sequences.

Voucher numbers must be:

* Unique within their defined scope
* Generated safely under concurrency
* Immutable after posting
* Traceable to their source transaction

Do not generate the next voucher number using an unsafe `MAX(number) + 1` flow without locking or a dedicated sequence strategy.

---

## 14. Chart of Accounts

Every financial transaction must map to valid Chart of Accounts entries.

Never hardcode account IDs.

Resolve accounts through configuration, account codes, mapping tables, or existing lookup services.

Validate that mapped accounts:

* Exist
* Are active when required
* Belong to the correct organization or branch scope
* Accept posting at the required account level

Do not silently fall back to an arbitrary account.

---

## 15. Branch Accounting

Each branch is an independent accounting and operational scope unless an existing module explicitly defines a different rule.

Preserve branch-level:

* Inventory
* Profit and Loss
* Balance Sheet
* Cash and bank balances
* Receivables
* Payables
* Voucher sequences where applicable

Current business rule: transfers between branches are treated as internal sales. Before changing or extending this flow, inspect the existing journal, inventory, tax, receivable/payable, and elimination behavior.

Never leak branch data through reports, joins, exports, DataTables, API responses, dropdowns, or aggregates.

---

## 16. Inventory Rules

Inventory valuation uses FIFO unless the existing module explicitly proves otherwise.

Every stock change must create a traceable stock movement or stock-ledger entry.

Never update stock quantity directly as the source of truth.

Stock must be derived from valid movements or maintained through the existing tested stock-ledger mechanism.

Supported movement concepts include:

* Purchase
* Sale
* Purchase Return
* Sale Return
* Transfer In
* Transfer Out
* Adjustment
* Damage
* Opening Stock
* Production when introduced

Inventory operations must preserve:

* Item
* Warehouse or branch
* Quantity
* Unit
* Cost
* Source reference
* Movement type
* Timestamp
* Acting user

Prevent negative stock when the current business rules prohibit it.

Use transactions and locking where concurrent sales or transfers can oversell stock.

---

## 17. Student Fee Rules

Support existing fee behavior, including:

* Monthly fees
* Quarterly fees
* Half-yearly fees
* Yearly fees
* Advance payments
* Partial payments
* Late fees
* Concessions
* Scholarships
* Admission fees
* Transport
* Hostel
* Security
* Fines

Never lose or overwrite payment history.

Every fee adjustment must be traceable.

Do not change historical challans merely because the current fee structure changed.

Preserve links among challans, students, enrollments, fee structures, payments, concessions, sessions, schools, and accounting entries.

Prevent duplicate challans for the same defined student, fee type, session, and fee period unless the business rule explicitly permits them.

Use decimal-safe calculations and define allocation behavior for partial and advance payments.

---

## 18. Payroll and HR Rules

Salary may include:

* Basic salary
* Allowances
* Benefits
* Deductions
* Loans
* Advances
* Income tax
* EOBI when applicable
* Attendance deductions
* Leave deductions
* Provident fund when introduced

Salary posting must generate balanced and traceable journal entries when accounting integration applies.

Employee advances remain assets until settled.

Expense claims must adjust advances according to the existing settlement workflow.

Track salary advances separately from general employee advances when the current domain model requires it.

Do not recompute or overwrite finalized payroll periods without an explicit reversal or adjustment workflow.

---

## 19. Reporting Rules

Financial and operational reports must reconcile with their source ledgers.

Important reports include:

* Trial Balance
* General Ledger
* Cash Book
* Bank Book
* Balance Sheet
* Profit and Loss
* Receivable Aging
* Payable Aging
* Inventory Valuation
* Stock Ledger
* Student Ledger
* Employee Ledger

For every report change:

* Define the reporting date basis.
* Preserve opening-balance logic.
* Preserve debit/credit sign conventions.
* Apply organization and branch scope.
* Avoid double counting caused by joins.
* Test empty data, partial periods, reversals, and boundary dates.
* Ensure exports use the same query rules as the screen where appropriate.

Cache only when invalidation rules are clear and correctness is preserved.

Large reports and exports should be queued or chunked when practical.

---

## 20. API, Controllers, and Responses

Do not introduce a new API when the requested feature belongs to the existing Blade/controller workflow.

For existing APIs:

* Use the established authentication method.
* Validate every request.
* Authorize every action.
* Return consistent response structures.
* Use proper HTTP status codes.
* Use API Resources where the module already uses them or when they materially improve consistency.
* Do not expose sensitive fields or unnecessary internal implementation details.
* Preserve existing response contracts unless a versioned change is approved.

For Ajax endpoints, authorization and validation are still mandatory.

---

## 21. Blade and Frontend Rules

Keep Blade templates focused on presentation.

Move complex calculations and business decisions to:

* Services
* View Models
* Presenters
* Controllers for simple view composition
* Dedicated query objects

Use existing Blade components and partials before creating new markup patterns.

Do not introduce Vue, React, Livewire, Inertia, Alpine, or another frontend framework unless explicitly requested and compatible with the project.

Server-side enforcement is the source of truth. Hiding a button is not authorization.

---

## 22. JavaScript, jQuery, Ajax, and DataTables

Follow the existing frontend stack.

Use delegated events for dynamically rendered elements:

```javascript
$(document).on('click', '.btn-save', function () {
    // Handler
});
```

Avoid:

* Duplicate event binding
* Inline JavaScript in repeated Blade loops
* Repeated Ajax boilerplate
* Trusting client-side totals
* Silent request failures
* Global variables without need

Use reusable functions for:

* CSRF setup
* Loading states
* Error rendering
* Confirmation flows
* Ajax response handling

For server-side DataTables:

* Apply authorization and tenant scope before returning rows.
* Select only required columns.
* Avoid per-row queries.
* Escape output by default.
* Mark raw columns only when necessary and safe.
* Keep action-button permissions consistent with backend endpoints.

---

## 23. File Uploads and Media

Use Laravel Storage and the configured disk.

Do not trust the original filename.

Validate:

* MIME type
* Extension
* Size
* Image dimensions where relevant
* Media duration or format where relevant

Generate safe unique storage names.

Do not store executable uploads in publicly executable paths.

Do not assume Spatie Media Library is installed. Use the project's existing upload/storage implementation unless the package is deliberately added through an approved dependency change.

Preserve file references when records are audited or financially significant. Do not physically delete evidence files merely because a record is disabled unless retention rules explicitly allow it.

---

## 24. Jobs, Queues, Scheduler, and Integrations

Move long-running, retryable, or high-volume work to queued jobs when the queue system is configured and the behavior warrants it.

Jobs should be:

* Idempotent where possible
* Safe to retry
* Scoped to the correct tenant or branch
* Explicit about timeouts and retries when necessary
* Careful with serialized models and stale state

Avoid heavy work inside HTTP requests or scheduler callbacks.

For third-party integrations and payment gateways:

* Never log secrets or complete sensitive payloads.
* Verify webhook signatures where supported.
* Make webhook processing idempotent.
* Store external transaction references.
* Handle timeouts and retries.
* Never mark payment successful solely from a client redirect.

---

## 25. Error Handling and Logging

Do not catch exceptions merely to log and rethrow them everywhere. Laravel already reports unhandled exceptions.

Catch an exception only when you can:

* Recover safely
* Add useful domain context
* Convert it to an expected application response
* Roll back or compensate for an external operation

Prefer:

```php
try {
    // Operation that needs contextual handling
} catch (\Throwable $exception) {
    report($exception);

    throw $exception;
}
```

Use structured, useful log context without exposing sensitive information.

Never log:

* Passwords
* Access tokens
* API secrets
* Full card or bank details
* Sensitive financial payloads
* Unnecessary personal information
* Full uploaded document contents

Do not silently suppress exceptions.

---

## 26. Coding Style

Follow existing Laravel and project conventions.

Use Laravel Pint where compatible with the current codebase.

Use meaningful names.

Good:

```php
$studentReceipt
$feeAllocation
$branchExpense
```

Avoid vague names when the value has a clear domain meaning:

```php
$data
$temp
$result
```

Methods should have one clear responsibility.

Extract repeated rules, not merely repeated syntax.

Prefer explicit, readable code over clever compression.

Add comments only for non-obvious intent, business constraints, concurrency decisions, or unusual compatibility behavior.

Do not add comments that simply repeat the code.

---

## 27. Package and Dependency Rules

Do not add, remove, upgrade, downgrade, or replace packages unless explicitly required.

Before changing dependencies:

* Inspect `composer.json`.
* Inspect `composer.lock`.
* Run `composer why` or `composer why-not` where relevant.
* Check PHP and Laravel compatibility.
* Check security and maintenance impact.
* Identify breaking changes.
* Avoid broad `composer update` operations.

Prefer targeted updates:

```bash
composer update vendor/package --with-all-dependencies
```

only when an approved dependency change requires them.

Do not modify `vendor/`.

The presence of several PDF libraries does not authorize replacing an existing PDF pipeline without a task-specific reason.

---

## 28. Testing and Verification

Before considering a task complete, verify the relevant items:

* Existing behavior still works.
* Validation is enforced.
* Backend authorization is enforced.
* UI visibility matches authorization.
* Tenant, cluster, school, office, and branch isolation is preserved.
* No N+1 query was introduced.
* No duplicate voucher number can be generated.
* Accounting remains balanced.
* Inventory remains consistent.
* Payment and fee history remains intact.
* No duplicate submission issue was introduced.
* No report double counting was introduced.
* No sensitive data is exposed.
* No unrelated file changed.

Use the narrowest relevant commands first.

Common commands, subject to repository compatibility:

```bash
php artisan test
vendor/bin/phpunit
vendor/bin/pint --test
php artisan route:list
php artisan config:show permission
php artisan permission:cache-reset
```

For a changed PHP file, syntax-check when useful:

```bash
php -l path/to/file.php
```

Do not run `php artisan optimize:clear` as a substitute for fixing incorrect code. Use it only when cache state is relevant.

Never claim tests passed if they were not run.

If tests cannot run, state exactly why and describe the alternative verification performed.

---

## 29. Production and Deployment Handoff

When a change affects deployment, state the exact required steps, such as:

* Backup requirement
* Maintenance mode requirement
* Migration command
* Seeder command
* Permission cache reset
* Config cache rebuild
* Route cache rebuild
* View cache rebuild
* Queue restart
* Scheduler impact
* Storage link or disk configuration
* Environment variable additions
* Rollback steps

Do not recommend cache-clearing commands blindly.

Never include real secrets in documentation or commands.

---

## 30. Code Review Checklist

Before final handoff, confirm:

* The task requirement is fully addressed.
* The root cause or extension point was identified.
* The change is minimal.
* Existing architecture was followed.
* Validation is correct.
* Authorization is correct.
* Direct user and role permissions behave as intended.
* Record-level policies are applied where needed.
* Tenant and branch scopes are preserved.
* Transactions are used where atomicity is required.
* Concurrency risks were considered.
* No N+1 query was introduced.
* Queries select only required columns in high-volume paths.
* Accounting entries remain balanced.
* Voucher numbering remains safe and immutable.
* Inventory movements remain traceable.
* Historical fee and payment records are preserved.
* Errors are not silently suppressed.
* Logs contain no sensitive data.
* Tests were added or updated where practical.
* Relevant tests and checks were run.
* No unrelated changes are present.
* No breaking change was introduced without explicit approval.

---

## 31. Agent Communication and Final Response

Before implementation, provide a concise plan when the task is non-trivial.

During implementation, report meaningful blockers or discovered risks rather than low-level activity.

The final response must include:

1. Summary of what changed
2. Root cause or implementation rationale
3. Files changed
4. Important business or technical behavior
5. Validation and authorization behavior
6. Tests and checks run
7. Deployment commands, if any
8. Risks, assumptions, or unverified items

Do not claim completion when a required part was not implemented.

Do not hide uncertainty.

Do not provide lengthy generic explanations when a concrete diff, command, or decision is more useful.

---

## 32. Definition of Done

A task is complete only when:

* The requested behavior is implemented.
* The implementation follows the applicable architecture.
* Data integrity is preserved.
* Accounting and inventory rules are preserved where relevant.
* Authorization is enforced on the backend.
* Tenant and branch isolation is preserved.
* Validation handles expected and malicious input.
* Relevant tests pass or limitations are explicitly reported.
* The final diff contains no unrelated changes.
* Deployment and migration requirements are documented.
* The handoff is clear enough for another developer to review and deploy safely.

---

## 33. Maintaining This File

Update this file when the agent repeatedly makes the same incorrect assumption or when a stable repository-wide convention changes.

Put module-specific rules in a closer `AGENTS.md`, for example:

```text
Modules/Accounts/AGENTS.md
Modules/Inventory/AGENTS.md
Modules/School/AGENTS.md
Modules/Payroll/AGENTS.md
```

Keep module-level instructions focused on that module's commands, file locations, invariants, and tests.

Do not copy the full root file into every module.
