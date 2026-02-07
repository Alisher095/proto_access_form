Prototype Assignment: Access Form
LMS
Submission Date: Sep-12-2025
Supervisor: Abdullah Qamar
Email ID: Abdullah.qamar@vu.edu.pk
You can contact through google chat https://chat.google.com/ using official VU ID

Project Overview
AccessForm is a web-based, accessible form builder prototype built with HTML, CSS, JavaScript, and Bootstrap. It focuses on inclusive design for PWD users, providing keyboard-friendly navigation, clear form labels, and responsive layouts. The prototype demonstrates authentication, dashboards, form creation, preview, and response viewing.

Quick Start
Open index.html in a browser to access the dashboard. Use the Login and Sign Up screens for the authentication prototype.

Implemented Screens
- Dashboard: index.html
- Login: login.html
- Sign Up: signup.html
- Form Builder: builder.html
- Preview: preview.html
- Responses: responses.html

Prototype Wireframes (Embedded)
Dashboard wireframe
![Dashboard wireframe](assets/images/wireframe-dashboard.svg)

Form builder wireframe
![Builder wireframe](assets/images/wireframe-builder.svg)

Preview wireframe
![Preview wireframe](assets/images/wireframe-preview.svg)

Responses wireframe
![Responses wireframe](assets/images/wireframe-responses.svg)

Requirement Analysis
Target Users
- Students, faculty, and administrative staff who need to create accessible forms
- PWD users who require keyboard navigation and assistive technology support

Problem Statement
Traditional form builders often overlook accessibility needs. This prototype demonstrates a form builder focused on inclusive design and usability for all users.

Functional Requirements (Implemented)
- Secure-looking login and signup screens (frontend prototype)
- Dashboard with form cards and quick actions
- Drag and drop form elements
- Field types: text, email, number, checkbox, radio, dropdown
- Form title and description settings
- Form preview mode
- Response submission and response viewing

Nonfunctional Requirements (Implemented)
- Usability: consistent navigation and clear labels
- Accessibility: skip link, focus states, fieldsets/legends
- Responsiveness: mobile-first Bootstrap layout

Core Features
- Drag and drop form elements
- Editable field properties (label, required, options)
- Preview and submit responses
- Response list with timestamps
- Optional AI assist placeholder

User Flow
1. User visits Login or Sign Up
2. User lands on Dashboard
3. User opens Form Builder
4. User adds fields via drag and drop
5. User updates form settings and field properties
6. User previews the form
7. User submits a response
8. User reviews responses in the Responses dashboard

Accessibility Highlights
- Skip to content link
- Form labels and legends for assistive tech
- High-contrast primary actions
- Keyboard-friendly controls

Optional AI Support (Prototype)
AI assist placeholder is displayed in the dashboard and builder for future enhancement (suggesting questions, checking accessibility).

Assets
- CSS: assets/css/styles.css
- JavaScript: assets/js/app.js, assets/js/builder.js, assets/js/preview.js, assets/js/responses.js, assets/js/auth.js
- Images: assets/images/*.svg

Backend (PHP + MySQL)
- SQL schema: database.sql
- PHP auth: auth/config.php, auth/db.php, auth/login.php, auth/register.php, auth/logout.php, auth/middleware.php

Setup Notes
1. Import database.sql in MySQL Workbench.
2. Update auth/config.php with your MySQL credentials.
3. Run the project through a PHP server and open index.php.