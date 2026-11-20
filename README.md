# UniVibe — University Event Management Platform

A responsive, multi-page PHP + MySQL starter project. Every screen has its own HTML, CSS, and JavaScript file. `api.php` is the single PHP backend entry point and contains the database connection, session handling, and API actions.

## Features
- Welcome page with 3-slide hero carousel
- Browse/search/filter events and view event details
- Student registration and login
- Register for an event; seats-left count updates
- Student dashboard with registrations and notifications
- Admin dashboard: create/edit/delete events, registrations, attendance, announcements
- Export registrations as CSV
- Contact form
- Responsive desktop/mobile layout

## Requirements
- XAMPP / WAMP / MAMP (PHP 8.0+ and MySQL/MariaDB)
- A modern browser

## Setup (XAMPP)
1. Extract the `UniVibe` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Create a database named `univibe`.
5. Select the database and import `database.sql`.
6. Open `api.php` and confirm the database settings near the top:
   - host: `localhost`
   - database: `univibe`
   - username: `root`
   - password: empty (default XAMPP)
7. Visit `http://localhost/UniVibe/index.html`.

## First admin account
The API automatically creates the default admin account if no admin exists:
- Email: `admin@univibe.edu`
- Password: `Admin@12345`

**Change this password immediately after your first login.** This is a development starter, not a production deployment. For production, use HTTPS, SMTP for email notifications, CSRF protection, rate limiting, secure hosting, and environment-based secrets.

## Project structure
```text
UniVibe/
├── index.html                 # Welcome / landing page
├── events.html                # Event discovery and filters
├── event-details.html         # Event details (reads ?id=)
├── login.html                 # Login
├── register.html              # Student sign-up
├── student-dashboard.html     # Student dashboard
├── admin-dashboard.html       # Admin management dashboard
├── about.html                 # About UniVibe
├── contact.html               # Contact form
├── api.php                    # Single PHP backend + database connection
├── database.sql               # Database schema + sample events
├── assets/
│   ├── logo.svg
│   └── images/                # Remote image URLs are used in the starter UI
├── css/                       # One stylesheet per screen + shared.css
└── js/                        # One JavaScript file per screen + shared.js
```

## Important notes
- The event photos use Unsplash image URLs, so an internet connection is required to load them. Replace them with your own licensed images in the `assets/images/` folder if you want a fully offline project.
- The contact form currently stores no message because an email service is not configured. It validates and shows a success state; connect SMTP or add a `messages` table to persist messages.
- Browser pages are separate `.html` files; the PHP API is called using `fetch()` from each page.


## Updated features (v2)
- Expanded sample event collection (20 additional campus events across technology, cultural, workshop, competition, sports and seminar categories).
- Students can suggest events from their dashboard; administrators approve or reject suggestions before publication.
- Password visibility toggle on sign-in and sign-up forms.
- Responsive dashboard drawer with Overview, Explore Events, My registrations, My profile and Logout.
- Editable My Profile page.
- Local campus-festival image asset used as a reliable fallback for event cards and the login/sign-up artwork.
- Admin dashboard includes quick Add Event navigation and pending student suggestions.

After replacing the old project, import the updated `database.sql` in phpMyAdmin to create the new `event_suggestions` table and add the extra sample events. Existing tables and existing events are preserved; seed events are inserted only if their titles are not already present.


## Latest visual and event updates
- Login and sign-up now use separate campus images from the UniVibe artwork.
- The starter database seeds exactly 12 events, each with its own local SVG illustration (no repeated event image).
- Students can submit event suggestions from the dashboard; administrators can review and approve them.
- Student and admin dashboards include a refreshed purple welcome header, interactive stat cards, and responsive drawer navigation.

If you are importing this updated `database.sql` into an existing database, export a backup first. The demo seed section clears registrations and events so it can recreate the exact 12 starter events.


## Visual refresh
The interface uses a professional navy, teal and off-white palette. The home-page slideshow uses three different locally stored generated photographic images. The 12 seeded events each use a distinct locally stored generated photographic image. Login and sign-up pages use a compact desktop viewport layout; on small mobile screens they remain naturally scrollable for accessibility.


## Latest visual and navigation fixes
- Replaced remaining purple accents with a consistent navy, teal and off-white theme.
- Added locally bundled imagery to the About page.
- Updated the landing-page carousel to cycle through three different local images.
- Added an Add an event action to Explore Events (sign-in required to submit).
- Added an always-visible hamburger control and off-canvas drawer on both dashboards.


## November 2026 visual and dashboard refresh
- Replaced landing, About, Contact, login and signup artwork with high-resolution locally stored images.
- Student and admin workspaces now use a dedicated dashboard toolbar and a working left-side navigation drawer.
- The drawer opens from the Menu button and closes with its close button, the dimmed backdrop, Escape, or a navigation link.
- Public-site navigation and footer are intentionally hidden inside authenticated dashboards.
- Palette: navy, teal and warm off-white.


## Latest visual and navigation update
- Replaced the third landing-page slideshow image with a separate campus festival photograph.
- Added independent local header images for the About and Contact pages.
- Added a photographic student dashboard welcome banner.
- Reorganized the student drawer into My Campus, Community, and Account groups.
- Reorganized the admin drawer into Management, Student Activity, and Account groups.
- Added a shared accessible drawer controller for both dashboards, including hamburger toggle, close button, outside click, Escape key, and active-page highlighting.
