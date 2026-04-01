# Feature Verification Spec

## Why
The app has many modules and roles, and it is easy for screens to silently break (missing permissions, broken routes, placeholder pages, runtime errors). A full feature check prevents shipping regressions.

## What Changes
- Define what “feature fully works” means for this project (core flows vs. intentionally incomplete modules).
- Add a repeatable verification process covering: routes, UI navigation, permissions, and the main business flows.
- Add lightweight automated coverage for the highest-risk flows (smoke tests) to prevent regressions.
- Standardize placeholder/coming-soon behavior so unfinished modules do not look broken.

## Impact
- Affected specs: navigation visibility, role access, OPD booking, doctor workflow, billing, IPD admission/discharge, settings/master data, reports, webhook settings.
- Affected code: Blade layout components, Livewire components, routes, policies/permissions, and tests.

## ADDED Requirements
### Requirement: Feature Verification Standard
The system SHALL have a documented, repeatable checklist to verify all user-facing modules that exist in production navigation.

#### Scenario: Verification run
- **WHEN** a developer runs the verification steps in the checklist
- **THEN** they can confirm the app has no blocking errors (500s), broken navigation, or permission leaks for the supported modules.

### Requirement: Minimal Smoke Test Coverage
The system SHALL provide automated smoke tests for the core supported flows:
- Auth access control to protected routes
- Patient list page loads
- OPD booking page loads
- Doctor dashboard loads for doctor role

#### Scenario: CI/local test run
- **WHEN** `php artisan test` is executed
- **THEN** the smoke tests pass and detect major routing/view regressions early.

### Requirement: Unfinished Modules Are Not “Broken”
If a module is not implemented beyond a placeholder, the system SHALL present it as “Coming soon” and SHALL NOT show it in navigation for roles that cannot use it yet.

#### Scenario: User opens unfinished module
- **WHEN** a user navigates to an unfinished module page (direct URL or allowed menu entry)
- **THEN** the UI clearly indicates the module is not available yet and does not throw errors.

## MODIFIED Requirements
### Requirement: Role-Based Navigation Visibility
The sidebar/topbar navigation SHALL only show sections and links that the signed-in user can access based on permissions, avoiding empty section headers.

## REMOVED Requirements
None.
