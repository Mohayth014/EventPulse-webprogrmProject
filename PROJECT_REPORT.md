# EventPulse: Next-Gen Intelligent Event Registration & Ticketing Ecosystem
## Comprehensive Engineering Project Report

---

### Executive Summary
**EventPulse** is an advanced, production-grade web application engineered to redefine event ticketing and venue admission. Developed with **PHP 8.3**, **MySQL 8.0**, and **Bootstrap 5.3**, the platform integrates modern cryptographic anti-scalping protocols, spatial computing venue simulations, AI-driven itinerary scheduling, and team group booking perks into a unified ecosystem.

Unlike traditional event registration portals that treat tickets as static PDF receipts and seating as abstract dropdown lists, EventPulse addresses major industry pain points:
1. **Ticket scalping and duplicate entry fraud** via rotating HMAC-SHA256 Time-based One-Time Passwords (TOTP) and browser-based optical gatekeeper scanning with Web Audio synthesizer chimes.
2. **Blind seat reservations** via an interactive SVG venue grid coupled with real-time 2D Canvas stage perspective rendering and acoustic clarity scoring.
3. **Conference decision fatigue** via an AI Concierge that synthesizes personalized session agendas and outputs native `.ics` iCalendar exports.
4. **Group coordination hurdles** via a Squad RSVP engine offering viral invite links and automatic volume tier discounts.
5. **Environmental impact awareness** via a verified Carbon Offset module issuing cryptographic tree-planting certificates and verifiable Eco-Guardian digital badges.

---

### Table of Contents
1. **System Introduction & Motivation**
2. **System Architecture & Technology Stack**
3. **Deep Dive: 5 Core System Novelties**
   - Novelty 1: Interactive SVG Seating Map with Live Stage Perspective Simulation
   - Novelty 2: Anti-Scalp Rotating TOTP Digital Pass & Audio Gatekeeper Station
   - Novelty 3: AI Event Concierge & Itinerary Matchmaker with 1-Click .ICS Export
   - Novelty 4: Squad RSVP & Group Perks Engine
   - Novelty 5: Real-Time Demand Surge Ticker & Verified Carbon Offset Eco-Badge
4. **Relational Database Design & Data Dictionary**
5. **Security & Cryptographic Architecture**
6. **API Specifications**
7. **Testing, Verification & Quality Assurance**
8. **Deployment & Execution Guide**
9. **Conclusion & Future Roadmap**

---

### 1. System Introduction & Motivation
The global ticketing industry is burdened by fraudulent secondary markets, unauthorized scalping bots, duplicate barcode reproduction, and suboptimal attendee experiences. Traditional ticketing systems suffer from three primary architectural deficits:
- **Static Verification Tokens:** Traditional static QR codes or barcodes can be screenshotted, duplicated, and shared across multiple attendees, creating congestion and security breaches at venue gates.
- **Disconnected Spatial Information:** Attendees purchase tickets with minimal insight into their physical viewpoint, distance from performers, or audio quality.
- **Unstructured Multi-Track Content:** Modern summits host dozens of concurrent tracks across multiple halls, leaving attendees overwhelmed and unable to maximize networking synergy.

EventPulse was engineered from the ground up to solve these systemic limitations using standard web primitives, ensuring exceptional performance without reliance on heavy proprietary runtime frameworks.

---

### 2. System Architecture & Technology Stack

EventPulse adheres to a modular, lightweight MVC (Model-View-Controller) architecture:

```
                  +----------------------------------------------+
                  |               Client Browser                 |
                  |  - Bootstrap 5.3 Responsive Layout (Dark/Light) |
                  |  - Canvas 2D Stage Perspective Renderer       |
                  |  - Web Audio API Sound Synthesizer            |
                  |  - html5-qrcode Camera Gatekeeper Scanner    |
                  +-----------------------+----------------------+
                                          |
                                          | HTTP / JSON REST APIs
                                          v
                  +----------------------------------------------+
                  |            PHP 8.3 Backend Engine            |
                  |  - PDO Singleton Database Handler (utf8mb4)  |
                  |  - Cryptographic HMAC-SHA256 Generator       |
                  |  - Session Concurrency & Row-Lock Manager    |
                  |  - Role-Based Access Control (RBAC)          |
                  +-----------------------+----------------------+
                                          |
                                          | SQL Prepared Statements
                                          v
                  +----------------------------------------------+
                  |            MySQL 8.0 Relational DB           |
                  |  - InnoDB Storage Engine (ACID Transactions) |
                  |  - Foreign Key Constraints & Indexes         |
                  |  - Row-Level Locking (`FOR UPDATE`)          |
                  +----------------------------------------------+
```

#### Technology Stack Summary:
- **Backend Language:** PHP 8.3 (High performance, native PDO MySQL connector, OpenSSL, BCrypt).
- **Database:** MySQL 8.0 Server (InnoDB engine, utf8mb4 charset, relational integrity).
- **Frontend Framework:** Bootstrap 5.3 (Responsive grid, accessible components, CSS custom variables).
- **Typography & Styling:** Google Fonts (*Space Grotesk*, *Plus Jakarta Sans*), Glassmorphism styling with backdrop blur.
- **Client Multimedia APIs:**
  - **HTML5 Canvas 2D:** Renders real-time stage perspective viewpoint simulation.
  - **Web Audio API:** Synthesizes audio feedback frequencies (chimes for valid passes, saw-wave buzzes for duplicates).
  - **html5-qrcode:** Hardware-accelerated camera video stream QR code scanning.
  - **Chart.js:** Real-time analytics visualization of sales velocity and tier revenue.

---

### 3. Deep Dive: 5 Core System Novelties

#### Novelty 1: Interactive SVG Seating Map & Real-Time Stage Perspective Simulation
Traditional ticket portals only provide a flat dropdown quantity selector. EventPulse implements a visual venue mapping engine:
- **Tiered Seating Grid:** Generates 96 interactive seat nodes arranged across rows A through F:
  - Rows A-B: VIP Sovereign Tier ($299.00)
  - Rows C-D: Premium Gold Tier ($149.00)
  - Rows E-F: Standard Access Tier ($79.00)
- **Live Stage Perspective Simulation:** A dedicated Canvas 2D simulation box dynamically computes:
  - *Distance from Stage:* Mathematically derived from the selected row (Row A = 6m, Row F = 26m).
  - *Viewing Angle Offset:* Calculated from the seat's column offset relative to the venue center (-36° Left Wing to +36° Right Wing).
  - *Acoustic Clarity Rating:* A predictive formula evaluating sound pressure and distance:
    $$\text{AcousticScore} = \max(78, \text{round}(99 - (\text{dist} \times 0.7) - |\text{angle} \times 0.3|))$$
  - *Dynamic Canvas Lighting:* Live stage platform, LED backdrop, and spotlights adjust size and focal angle as seats are selected.
- **Seat-Hold Concurrency Timer:** When a user proceeds to checkout, selected seats are held with a 5-minute reservation timer in MySQL, preventing race conditions.

#### Novelty 2: Anti-Scalp Rotating TOTP Pass & Audio Gatekeeper Station
To eradicate screenshot-based ticket scalping and fraudulent duplication:
- **Dynamic 30-Second Security Token:** Each pass computes an ephemeral 8-character token based on 30-second Unix time slots:
  $$\text{TOTP} = \text{Substr}(\text{HMAC\_SHA256}(\lfloor \text{Time}() / 30 \rfloor, \text{TicketHash} \parallel \text{SecretKey}), 0, 8)$$
- **Mobile Wallet Aesthetic:** Features an Apple Wallet / Google Wallet card layout with metallic holographic sweeps, perforated notch borders, and high-contrast QR matrix.
- **Universal Optical Gatekeeper Scanner:** Organizers can transform any device with a camera into an entry gatekeeper station using `html5-qrcode`.
- **Web Audio Haptic Sound Effects:**
  - *Access Granted:* Synthesizes an ascending two-tone sine-wave chime (587.33 Hz to 880 Hz).
  - *Duplicate or Counterfeit:* Synthesizes an assertive 140 Hz saw-wave error buzz.
  - *Instant Invalidation:* Check-in status is stamped in MySQL, preventing duplicate entry attempts.

#### Novelty 3: AI Event Concierge & Itinerary Matchmaker
Large conferences overwhelm attendees with decision fatigue. The AI Concierge features:
- **Persona & Objective Analysis:** Evaluates attendee profile (e.g., Software Engineer, Founder, UI/UX Designer), learning targets (Technical Masterclasses, Venture Capital), and intensity pace.
- **Algorithmic Schedule Synthesis:** Generates a conflict-free chronological session schedule with match percentages (e.g., "98% Match").
- **Targeted Networking Cohorts:** Pairs attendees with relevant roundtable cohorts (e.g., "Kernel & Inference Hackers", "Series A Founders").
- **1-Click .ICS Calendar Export:** Synthesizes standard RFC-5545 iCalendar files directly in the browser, allowing immediate import into Google Calendar, Apple iCal, and Microsoft Outlook.

#### Novelty 4: Squad RSVP & Group Perks Engine
Incentivizes viral group bookings and simplifies team reservations:
- **Squad Code Generation:** Squad leaders generate a unique team invite code (e.g., `SQ-TECH-4912`) and shareable link.
- **Automated Tier Discounts:** Applying a valid squad code at checkout immediately grants a **15% to 20% discount** across all selected tickets.
- **Team Delegation Passes:** Automatically links team members to the squad manifest in MySQL and reserves designated discussion cohorts at the venue.

#### Novelty 5: Dynamic Demand Surge & Verified Carbon Offset Eco-Badge
- **Live Ticket Velocity Telemetry:** Tracks tickets sold per hour to drive early-bird pricing tiers and capacity alerts ("Early Bird Wave 2 - 84% Sold Out").
- **Verified Tree-Planting Carbon Offset:** Attendees can opt-in to a $5.00/ticket offset that funds 2 certified native trees and neutralizes 50 kg of CO₂.
- **Eco-Guardian Digital Badge:** Validated bookings receive a cryptographic certificate hash and a verifiable "Eco-Guardian" badge displayed directly on their digital pass.

---

### 4. Relational Database Design & Data Dictionary

The `eventpulse_db` schema is fully normalized and organized into 11 tables:

| Table Name | Primary Key | Description | Key Relationships |
|---|---|---|---|
| `users` | `id` (INT) | User credentials, roles (admin, organizer, attendee), organization, and avatars. | Referenced by `events`, `bookings`, `squads`. |
| `categories` | `id` (INT) | Event categories (Tech & AI, Music, Business, Gaming, etc.). | Referenced by `events`. |
| `events` | `id` (INT) | Core event metadata: title, slug, venue, dates, seating type, capacity, status. | FK to `users`, `categories`. Referenced by `ticket_tiers`, `seats`, `bookings`. |
| `ticket_tiers` | `id` (INT) | Ticket tiers: VIP, Premium, Standard, price, capacity, perks JSON. | FK to `events`. Referenced by `seats`, `ticket_items`. |
| `seats` | `id` (INT) | Individual seat nodes: row name, column number, status (available, reserved, booked). | FK to `events`, `ticket_tiers`. Referenced by `ticket_items`. |
| `bookings` | `id` (INT) | Financial transactions: booking reference, total amount, discount, net amount, status. | FK to `users`, `events`, `squads`. Referenced by `ticket_items`, `carbon_offsets`. |
| `ticket_items` | `id` (INT) | Individual issued passes: attendee name, email, ticket hash, check-in status and time. | FK to `bookings`, `ticket_tiers`, `seats`. |
| `squads` | `id` (INT) | Group RSVP records: squad code, squad name, leader ID, discount percent, member count. | FK to `events`, `users`. Referenced by `bookings`. |
| `event_speakers` | `id` (INT) | Speaker profiles: name, role, company, bio, avatar. | FK to `events`. |
| `event_schedule` | `id` (INT) | Schedule timeline: session title, speaker, room, track, start/end time. | FK to `events`. |
| `carbon_offsets` | `id` (INT) | Environmental impact: trees funded, kg CO₂ offset, certificate hash. | FK to `bookings`. |

---

### 5. Security & Cryptographic Architecture

1. **Password Security:**
   All user passwords are encrypted using `password_hash()` with the **BCrypt algorithm** (Work Factor 10), ensuring resilience against rainbow table and brute-force attacks.
2. **SQL Injection Immunity:**
   The application exclusively utilizes **PDO Prepared Statements** with strict parameterized inputs. No raw user input is ever concatenated into SQL query strings.
3. **Cross-Site Scripting (XSS) Mitigation:**
   All dynamic client outputs pass through the helper function `e()` wrapping `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.
4. **Cross-Site Request Forgery (CSRF) Tokens:**
   Forms and mutating endpoints implement cryptographically secure anti-forgery tokens (`bin2hex(random_bytes(32))`) validated via `hash_equals()`.
5. **Anti-Scalp HMAC-SHA256 Signatures:**
   Every pass combines booking references, attendee names, seat coordinates, and a secret server salt into an immutable 64-character SHA-256 hash. The rotating TOTP ensures that even if a QR code is photographed, the pass cannot be reused once the 30-second window expires.

---

### 6. API Specifications

| Method | Endpoint | Description | Request Payload | Response |
|---|---|---|---|---|
| `POST` | `/api/book_ticket.php` | Processes checkout and issues passes. | `{event_id, seat_ids, attendees, squad_code, carbon_offset}` | `{success: true, booking_reference, redirect_url}` |
| `POST` | `/api/scan_qr.php` | Validates QR code and marks check-in. | `{token: "TICKET_HASH_OR_REF"}` | `{success: true, status: "VALID", ticket: {...}}` |
| `POST` | `/api/ai_concierge.php` | Synthesizes tailored schedule. | `{role: "...", goal: "...", pace: "..."}` | `{success: true, summaryTitle, itinerary, networkingMatches}` |
| `POST` | `/api/lock_seat.php` | Holds seat for 5-minute session cart. | `{seat_id: 12, action: "lock"}` | `{success: true, message: "Seat held", expires_in: 300}` |
| `POST` | `/api/squad_action.php` | Creates or verifies squad codes. | `{action: "verify_squad", code: "SQ-..."}` | `{success: true, squad: {...}, discount_percent: 15}` |

---

### 7. Testing, Verification & Quality Assurance

EventPulse has undergone comprehensive testing across all functional layers:

1. **Automated Endpoint Testing:**
   All primary web routes (`/`, `/events.php`, `/event.php`, `/checkout.php`, `/my_tickets.php`, `/admin/index.php`, `/admin/scanner.php`, `/admin/attendees.php`) returned **HTTP 200 OK**.
2. **Concurrency & Seat Locking Test:**
   Verified that attempting to lock an already booked or reserved seat cleanly rejects with `"Seat already sold out"`.
3. **Cryptographic Gate Verification Test:**
   - Scanning initial ticket `EP-2026-X8910`: Returned `status: "VALID"` and successfully updated database check-in timestamp.
   - Scanning the identical pass a second time: Returned `status: "ALREADY_CHECKED_IN"`, successfully triggering the duplicate-prevention warning.
4. **AI Concierge Test:**
   Confirmed that role selection dynamically generates tailored tracks with match synergy scores and outputs RFC-compliant `.ics` calendar files.
5. **Cross-Browser Responsiveness:**
   Verified fluid responsive layout across desktop, tablet, and smartphone viewports with dark mode and light mode persistence.

---

### 8. Deployment & Execution Guide

1. **Prerequisites:**
   - PHP 8.3 or higher with extensions: `pdo_mysql`, `mysqli`, `openssl`, `mbstring`, `fileinfo`, `gd`.
   - MySQL 8.0 Server running on `127.0.0.1:3306`.
2. **Database Setup:**
   Run the migration and seeder script from the workspace directory:
   ```bash
   php config/setup.php
   ```
3. **Launch Server:**
   Execute the included batch runner or command line:
   ```bash
   run_server.bat
   # OR
   php -S localhost:8000
   ```
4. **Access Application:**
   Open any modern web browser and navigate to:
   ```
   http://localhost:8000
   ```
5. **Demo Accounts:**
   - **Administrator:** `admin@eventpulse.io` / `admin123`
   - **Organizer:** `organizer@eventpulse.io` / `organizer123`
   - **Attendee:** `attendee@eventpulse.io` / `attendee123`

---

### 9. Conclusion & Future Roadmap

EventPulse demonstrates that modern, feature-rich web architectures can be achieved with pure **PHP 8.3**, **MySQL 8.0**, and **Bootstrap 5.3** without bloated JavaScript frameworks or high server overhead. By introducing five distinct novelties—interactive stage simulations, anti-scalp rotating TOTP passes, AI itinerary synthesis, squad referral discounts, and verified carbon offsets—the platform provides a compelling blueprint for next-generation ticketing systems.

#### Future Enhancements:
- **Offline Progressive Web App (PWA):** Service worker synchronization allowing gate scanning in subterranean or low-connectivity venues.
- **Apple Wallet / Google Wallet Native Passes:** Automatic generation of signed `.pkpass` and Google Wallet pass bundles with push notifications.
- **Biometric Gate Fast-Track:** Opt-in facial recognition check-in for VIP sovereignty passholders.
