## Project Overview
AccessForm is a web-based accessible form builder prototype developed with HTML, CSS, JavaScript, Bootstrap, PHP, and MySQL. The project focuses on inclusive design for Persons with Disabilities (PWD), role-based dashboards, form creation workflows, preview/submission flow, and response analysis.

The prototype also includes optional AI support for creators to improve form quality and usability through suggestions and accessibility checks.

## Setup Instructions
1. Import `database.sql` into MySQL Workbench.
2. Update DB credentials in `auth/config.php`.
3. Start a PHP server from project root.
4. Open `index.php` in browser.
5. Create/login with a `creator` account for full builder and AI features.
6. If you previously imported an older SQL version, run latest `database.sql` again to add `forms`, `form_fields`, and `form_responses` tables.

## Description of Implemented Features

### Authentication and Role Setup
- Secure signup/login using PHP + MySQL.
- Password hashing for credential safety.
- Roles implemented: `admin`, `creator`, `viewer`.

### Dashboard Development
- Role-aware dashboard in `index.php`.
- Different actions and content visibility by role.

### Form Builder Core Features
- Drag-and-drop form element workflow.
- Supported fields: text, email, number, checkbox, radio, dropdown.
- Form settings: title and description.
- Field-level properties: label, required toggle, options.

### Preview and Responses
- Preview screen for live form rendering.
- Form submission flow.
- Responses page with latest submissions and details.

### End-to-End MySQL Persistence
- Forms and fields saved in MySQL (`forms`, `form_fields`).
- Submissions saved in MySQL (`form_responses`).
- API endpoints:
	- `api/forms.php`
	- `api/form-responses.php`

### Optional AI Support (Implemented)
- Creator-only AI assistant in builder.
- AI actions:
	- Generate title/description from topic.
	- Suggest fields by context.
	- Accessibility check with basic auto-fixes.
- AI APIs:
	- `api/ai-suggest.php`
	- `api/ai-a11y-check.php`

### Accessibility and Responsiveness
- Skip link for keyboard navigation.
- Labels/legends for assistive technologies.
- Keyboard-friendly controls and clear focus states.
- Responsive layout for desktop and mobile.

## Requirement Analysis Summary

### Target Users
- Students, faculty, and administrative staff.
- PWD users requiring keyboard and assistive-tech friendly UI.

### Problem Statement
Many form systems do not adequately support accessibility. AccessForm addresses this by combining inclusive UI patterns with practical form workflows.

### User Flow
1. User logs in or signs up.
2. User lands on role-based dashboard.
3. Creator opens form builder.
4. User adds/arranges fields and updates settings.
5. User previews and submits form.
6. User reviews collected responses.

## Prototype Artifacts

### Low-Fidelity Wireframes (Embedded)
Dashboard wireframe  
![Dashboard wireframe](assets/images/wireframe-dashboard.svg)

Form builder wireframe  
![Builder wireframe](assets/images/wireframe-builder.svg)

Preview wireframe  
![Preview wireframe](assets/images/wireframe-preview.svg)

Responses wireframe  
![Responses wireframe](assets/images/wireframe-responses.svg)


Recommended high-fidelity screens:
- Login
- Signup
- Dashboard
- Form Builder
- Preview
- Responses

## Reflection on Learning
- Understood full prototype lifecycle from UX analysis to implementation.
- Applied practical accessibility patterns in forms and navigation.
- Learned role-based rendering and secure auth fundamentals in PHP.
- Improved integration between frontend interactions and MySQL persistence.
- Implemented optional AI assistant behavior with guarded backend APIs.

## Planned Future Work
- Public shareable form links for external respondents.
- Rich analytics dashboard (charts, completion rate, field-level insights).
- Advanced AI mode with external LLM provider integration.
- Export options (CSV/PDF) for responses.
- Granular permission controls and stronger audit/reporting.

## Project Structure (Current)
- Frontend pages: `index.php`, `login.php`, `signup.php`, `builder.php`, `preview.php`, `responses.php`
- CSS: `assets/css/styles.css`
- JavaScript: `assets/js/app.js`, `assets/js/builder.js`, `assets/js/preview.js`, `assets/js/responses.js`, `assets/js/auth.js`, `assets/js/ai-assistant.js`
- Auth backend: `auth/config.php`, `auth/db.php`, `auth/login.php`, `auth/register.php`, `auth/logout.php`, `auth/middleware.php`
- API backend: `api/forms.php`, `api/form-responses.php`, `api/ai-suggest.php`, `api/ai-a11y-check.php`
- Schema: `database.sql`