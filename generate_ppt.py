"""
EventPulse - PowerPoint Presentation Generator (.pptx)
Generates a 12-slide modern widescreen presentation deck.
"""

import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

def create_presentation():
    prs = Presentation()
    # 16:9 Widescreen dimensions
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)

    # Theme Colors
    BG_DARK = RGBColor(15, 23, 42)      # #0f172a Slate 900
    CARD_BG = RGBColor(30, 41, 59)      # #1e293b Slate 800
    TEXT_WHITE = RGBColor(248, 250, 252) # #f8fafc
    TEXT_MUTED = RGBColor(148, 163, 184) # #94a3b8
    ACCENT_PURPLE = RGBColor(139, 92, 246) # #8b5cf6
    ACCENT_INDIGO = RGBColor(99, 102, 241) # #6366f1
    ACCENT_CYAN = RGBColor(56, 189, 248)   # #38bdf8
    ACCENT_GREEN = RGBColor(16, 185, 129)  # #10b981
    ACCENT_AMBER = RGBColor(245, 158, 11)  # #f59e0b

    blank_layout = prs.slide_layouts[6]

    def set_slide_background(slide):
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, prs.slide_width, prs.slide_height)
        bg.fill.solid()
        bg.fill.fore_color.rgb = BG_DARK
        bg.line.fill.background()
        return bg

    def add_header(slide, tag, title):
        # Tag
        tag_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(8), Inches(0.4))
        tf = tag_box.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = tag.upper()
        p.font.name = 'Arial'
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = ACCENT_PURPLE

        # Title
        title_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.85), Inches(11), Inches(0.8))
        tf2 = title_box.text_frame
        tf2.word_wrap = True
        p2 = tf2.paragraphs[0]
        p2.text = title
        p2.font.name = 'Arial'
        p2.font.size = Pt(26)
        p2.font.bold = True
        p2.font.color.rgb = TEXT_WHITE

    # -------------------------------------------------------------
    # SLIDE 1: Title Slide
    # -------------------------------------------------------------
    slide1 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide1)

    # Accent decorative bar
    bar = slide1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.8), Inches(0.15), Inches(3.6))
    bar.fill.solid()
    bar.fill.fore_color.rgb = ACCENT_INDIGO
    bar.line.fill.background()

    # Title box
    t_box = slide1.shapes.add_textbox(Inches(1.2), Inches(1.7), Inches(11.2), Inches(3.8))
    tf = t_box.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "EVENTPULSE ECOSYSTEM"
    p.font.name = 'Arial'
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = ACCENT_CYAN

    p = tf.add_paragraph()
    p.text = "Next-Gen Intelligent Event Registration & Ticketing System"
    p.font.name = 'Arial'
    p.font.size = Pt(36)
    p.font.bold = True
    p.font.color.rgb = TEXT_WHITE
    p.space_before = Pt(10)

    p = tf.add_paragraph()
    p.text = "Engineered with Interactive SVG Venue Seating, Anti-Scalp Rotating TOTP Passes, AI Concierge Matchmaking, and Squad Group RSVP Perks."
    p.font.name = 'Arial'
    p.font.size = Pt(16)
    p.font.color.rgb = TEXT_MUTED
    p.space_before = Pt(14)

    p = tf.add_paragraph()
    p.text = "Technology Stack: PHP 8.3 • MySQL 8.0 • Bootstrap 5.3 • Web Audio API • Canvas 2D"
    p.font.name = 'Arial'
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = ACCENT_GREEN
    p.space_before = Pt(18)

    # -------------------------------------------------------------
    # SLIDE 2: Problem Statement & Industry Challenges
    # -------------------------------------------------------------
    slide2 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide2)
    add_header(slide2, "Market Context & Analysis", "Challenges in Modern Event Ticketing")

    cards_data = [
        ("Ticket Scalping & Fraud", "Screenshot replication and unauthorized second-market bots cause rampant ticket fraud and duplicate check-in chaos at venue gates.", ACCENT_AMBER),
        ("Blind Seat Selection", "Traditional ticketing shows abstract row dropdowns. Attendees have zero spatial awareness of their stage angle, distance, or acoustic fidelity.", ACCENT_PURPLE),
        ("Conference Decision Fatigue", "Massive multi-track conferences overwhelm attendees with dozens of overlapping sessions and zero guidance on relevant peer networking.", ACCENT_CYAN),
        ("Friction in Group Bookings", "Squads, hackathon teams, and corporate delegations struggle to split bookings, coordinate seating blocks, and unlock volume perks.", ACCENT_GREEN)
    ]

    for i, (head, desc, col) in enumerate(cards_data):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = head
        p.font.name = 'Arial'
        p.font.size = Pt(17)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = desc
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_MUTED
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 3: System Overview & Architecture
    # -------------------------------------------------------------
    slide3 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide3)
    add_header(slide3, "System Engineering", "Core Architectural Foundation")

    arch_points = [
        ("Presentation Layer", "Responsive Bootstrap 5.3 UI with dark/light themes, Canvas 2D stage perspective engine, interactive SVG seating grid, and real-time Web Audio API feedback.", ACCENT_CYAN),
        ("Application Backend", "PHP 8.3 modular MVC architecture featuring robust PDO database wrappers, cryptographic token generation, and RESTful JSON micro-endpoints.", ACCENT_INDIGO),
        ("Persistence Layer", "MySQL 8.0 relational database with normalized schema, foreign keys, row-level locking for seat concurrency, and ACID transaction safety.", ACCENT_PURPLE),
        ("Security & Integrity", "Bcrypt password hashing, SHA-256 ticket hashing, HMAC-based 30-second rotating TOTP passes, and full CSRF/XSS defense.", ACCENT_GREEN)
    ]

    for i, (title, desc, col) in enumerate(arch_points):
        top = Inches(1.8 + i * 1.3)
        card = slide3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), top, Inches(11.7), Inches(1.15))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.2)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.name = 'Arial'
        p.font.size = Pt(15)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = desc
        p2.font.name = 'Arial'
        p2.font.size = Pt(12)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(3)

    # -------------------------------------------------------------
    # SLIDE 4: 5 Groundbreaking Novelties Overview
    # -------------------------------------------------------------
    slide4 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide4)
    add_header(slide4, "Core Innovation", "The 5 System Novelties at a Glance")

    nov_list = [
        ("Novelty 1: Interactive SVG Seating Map", "Visual venue layout, tiered seat selection, live Canvas stage perspective simulation, and 5-min cart hold.", ACCENT_PURPLE),
        ("Novelty 2: Anti-Scalp Rotating TOTP Pass", "Cryptographic HMAC 30-second rotating code with live webcam QR scanning and Web Audio gatekeeper chimes.", ACCENT_CYAN),
        ("Novelty 3: AI Concierge & Matchmaker", "Personalized session agenda synthesis tailored by professional role, goal synergy, and 1-click .ICS export.", ACCENT_AMBER),
        ("Novelty 4: Squad RSVP & Perks Engine", "Viral team referral codes, automated 15-20% group volume discounts, and unified delegation badges.", ACCENT_INDIGO),
        ("Novelty 5: Demand Surge & Carbon Offset", "Live ticket velocity monitoring, early-bird price tiers, and verified tree-planting Eco-Guardian certificates.", ACCENT_GREEN)
    ]

    for i, (title, desc, col) in enumerate(nov_list):
        top = Inches(1.7 + i * 1.05)
        card = slide4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), top, Inches(11.7), Inches(0.95))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = title
        p.font.name = 'Arial'
        p.font.size = Pt(15)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = desc
        p2.font.name = 'Arial'
        p2.font.size = Pt(12)
        p2.font.color.rgb = TEXT_MUTED
        p2.space_before = Pt(2)

    # -------------------------------------------------------------
    # SLIDE 5: Novelty 1 Deep Dive
    # -------------------------------------------------------------
    slide5 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide5)
    add_header(slide5, "Novelty Deep Dive #1", "Interactive SVG Seating & Stage Perspective Simulation")

    n1_points = [
        ("Dynamic Stadium Seating Layout", "Rows A-F with 96 distinct seat nodes, color-coded across VIP Sovereign, Premium Gold, and Standard tiers with hover states and booked flags.", CARD_BG),
        ("Real-time Perspective Rendering", "Canvas 2D renders lighting beams and stage scale based on seat coordinates: row distance (6m to 26m) and angle offset (-36° to +36°).", CARD_BG),
        ("Acoustic Clarity Algorithm", "Computes dynamic acoustic scores (e.g. 96% direct clarity) and distance metrics instantly as attendees interact with the venue canvas.", CARD_BG),
        ("Concurrency & Race-Condition Lock", "MySQL transaction row locks and 5-minute session countdown timers prevent double-booking collisions during checkout.", CARD_BG)
    ]

    for i, (h, d, bg) in enumerate(n1_points):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide5.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = bg
        card.line.color.rgb = ACCENT_PURPLE
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = ACCENT_PURPLE

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 6: Novelty 2 Deep Dive
    # -------------------------------------------------------------
    slide6 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide6)
    add_header(slide6, "Novelty Deep Dive #2", "Anti-Scalp Dynamic TOTP Pass & Audio Gatekeeper")

    n2_points = [
        ("Rotating HMAC-SHA256 Token", "Static screenshots are rendered useless! Every pass computes a fresh 8-character TOTP security token rotated precisely every 30 seconds.", CARD_BG),
        ("Apple / Google Wallet Aesthetics", "High-fidelity digital pass with holographic metallic light sweep, perforated notch dividers, and high-contrast scannable QR matrix.", CARD_BG),
        ("Live Optical Gate Scanner", "Organizers use any smartphone or laptop camera with html5-qrcode library to scan and decode passes in under 400 milliseconds.", CARD_BG),
        ("Web Audio Synthesizer Haptics", "Built-in dual oscillator synthesizes pleasant chord chimes for valid admissions and sharp saw-wave alerts for duplicate check-ins.", CARD_BG)
    ]

    for i, (h, d, bg) in enumerate(n2_points):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide6.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = bg
        card.line.color.rgb = ACCENT_CYAN
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = ACCENT_CYAN

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 7: Novelty 3 Deep Dive
    # -------------------------------------------------------------
    slide7 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide7)
    add_header(slide7, "Novelty Deep Dive #3", "AI Concierge & Smart Itinerary Matchmaker")

    n3_points = [
        ("Persona-Driven Scheduling", "Analyzes attendee role (Engineer, Executive, Designer), strategic objectives, and preferred intensity pace to craft tailored tracks.", CARD_BG),
        ("Conflict Resolution Engine", "Calculates hall transit buffer times between concurrent stages to ensure attendees never book overlapping keynotes.", CARD_BG),
        ("Curated Networking Synergy", "Pairs attendees with targeted peer cohorts (e.g. 'Inference Hackers', 'Series A Founders') with percentage match scores.", CARD_BG),
        ("Native Calendar Export (.ICS)", "Generates standardized iCalendar .ics files on the fly, enabling 1-click sync to Google Calendar, Apple iCal, and Outlook.", CARD_BG)
    ]

    for i, (h, d, bg) in enumerate(n3_points):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = bg
        card.line.color.rgb = ACCENT_AMBER
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = ACCENT_AMBER

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 8: Novelties 4 & 5 Deep Dive
    # -------------------------------------------------------------
    slide8 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide8)
    add_header(slide8, "Novelties Deep Dive #4 & #5", "Squad RSVP Engine & Demand Eco-Offset Badge")

    n4_5_points = [
        ("Viral Squad Referral Loop", "Squad leaders create team codes (e.g. SQ-TECH-4912) and invite colleagues via shareable links, tracking member progress in real time.", ACCENT_INDIGO),
        ("Tiered Group Discounts", "Automatically unlocks 15% to 20% discounts upon code redemption, incentivizing larger delegation purchases with zero manual friction.", ACCENT_INDIGO),
        ("Real-time Demand Velocity", "Live telemetry measures tickets sold per hour and dynamically updates early-bird countdown triggers and remaining seat alerts.", ACCENT_GREEN),
        ("Verified Eco-Guardian Badge", "Attendees can add a $5 carbon offset funding 2 native trees, generating a cryptographic certificate hash and green ticket badge.", ACCENT_GREEN)
    ]

    for i, (h, d, col) in enumerate(n4_5_points):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 9: Relational Database Architecture
    # -------------------------------------------------------------
    slide9 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide9)
    add_header(slide9, "Data Modeling", "Normalized MySQL 8.0 Relational Schema")

    tables_data = [
        ("users", "id, name, email, password_hash, role (admin, organizer, attendee), organization, avatar, created_at"),
        ("events", "id, organizer_id, category_id, title, slug, venue_name, city, start_date, banner_image, total_seats"),
        ("ticket_tiers", "id, event_id, name, price, base_price, capacity, sold_count, perks_json, color_code"),
        ("seats", "id, event_id, tier_id, seat_number, row_name, col_number, status (available, reserved, booked), locked_at"),
        ("bookings", "id, booking_reference, user_id, event_id, squad_id, total_amount, discount_amount, final_amount, qr_token"),
        ("ticket_items", "id, booking_id, tier_id, seat_id, attendee_name, attendee_email, ticket_hash, checkin_status, eco_badge"),
        ("squads", "id, event_id, squad_leader_id, squad_code, squad_name, discount_percent, member_count, target_size"),
        ("carbon_offsets", "id, booking_id, trees_funded, kg_co2_offset, certificate_hash, created_at")
    ]

    for i, (t_name, t_cols) in enumerate(tables_data):
        col_idx = i % 2
        row_idx = i // 4
        sub_row = i % 4
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.7 + sub_row * 1.25)

        card = slide9.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(1.1))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = ACCENT_INDIGO
        card.line.width = Pt(1)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = f"TABLE: `{t_name}`"
        p.font.name = 'Arial'
        p.font.size = Pt(13)
        p.font.bold = True
        p.font.color.rgb = ACCENT_CYAN

        p2 = tf.add_paragraph()
        p2.text = t_cols
        p2.font.name = 'Arial'
        p2.font.size = Pt(10.5)
        p2.font.color.rgb = TEXT_MUTED
        p2.space_before = Pt(2)

    # -------------------------------------------------------------
    # SLIDE 10: Security & Fraud Prevention
    # -------------------------------------------------------------
    slide10 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide10)
    add_header(slide10, "Application Security", "Multi-Layered Cyber Defense & Anti-Fraud")

    sec_items = [
        ("Bcrypt Password Hashing", "Zero plain-text password exposure; salt generation and adaptive work factor protect user credentials.", ACCENT_GREEN),
        ("SQL Injection Immunity", "100% of queries use PDO Prepared Statements with strict typed parameter bindings and no concatenated SQL strings.", ACCENT_CYAN),
        ("Cross-Site Scripting (XSS)", "All template outputs sanitized via htmlspecialchars with ENT_QUOTES and UTF-8 encoding escaping.", ACCENT_PURPLE),
        ("Cryptographic Signatures", "Tickets signed with SHA-256 HMAC and dynamic Unix time slots, rendering static counterfeiting mathematically impossible.", ACCENT_AMBER)
    ]

    for i, (h, d, col) in enumerate(sec_items):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide10.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 11: Organizer Telemetry & Gate Station
    # -------------------------------------------------------------
    slide11 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide11)
    add_header(slide11, "Operations Management", "Organizer Dashboard & Gate Telemetry")

    ops_items = [
        ("Real-Time Sales Telemetry", "Live telemetry tracking total gross revenue, ticket volume across summits, gate check-in completion percentages, and carbon offsets funded.", ACCENT_INDIGO),
        ("Visual Data Analytics", "Integrated Chart.js line charts illustrating hourly demand surge velocity and doughnut graphs displaying revenue share by ticket tier.", ACCENT_CYAN),
        ("Universal WebCam Gatekeeper", "Turn-key camera QR scanning without specialized hardware; instant database lookups update check-in timestamps in real time.", ACCENT_GREEN),
        ("Exportable Attendee Manifests", "Single-click export to CSV and JSON formats for badge printing, on-site security rosters, and CRM integration.", ACCENT_AMBER)
    ]

    for i, (h, d, col) in enumerate(ops_items):
        col_idx = i % 2
        row_idx = i // 2
        left = Inches(0.8 + col_idx * 6.0)
        top = Inches(1.8 + row_idx * 2.5)

        card = slide11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, Inches(5.6), Inches(2.2))
        card.fill.solid()
        card.fill.fore_color.rgb = CARD_BG
        card.line.color.rgb = col
        card.line.width = Pt(1.5)

        tf = card.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = h
        p.font.name = 'Arial'
        p.font.size = Pt(16)
        p.font.bold = True
        p.font.color.rgb = col

        p2 = tf.add_paragraph()
        p2.text = d
        p2.font.name = 'Arial'
        p2.font.size = Pt(13)
        p2.font.color.rgb = TEXT_WHITE
        p2.space_before = Pt(8)

    # -------------------------------------------------------------
    # SLIDE 12: Summary & Future Roadmap
    # -------------------------------------------------------------
    slide12 = prs.slides.add_slide(blank_layout)
    set_slide_background(slide12)
    add_header(slide12, "Conclusion & Vision", "Project Achievements & Next Horizons")

    summary_card = slide12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.8), Inches(11.7), Inches(4.8))
    summary_card.fill.solid()
    summary_card.fill.fore_color.rgb = CARD_BG
    summary_card.line.color.rgb = ACCENT_PURPLE
    summary_card.line.width = Pt(2)

    tf = summary_card.text_frame
    tf.word_wrap = True

    p = tf.paragraphs[0]
    p.text = "Key Project Achievements:"
    p.font.name = 'Arial'
    p.font.size = Pt(18)
    p.font.bold = True
    p.font.color.rgb = ACCENT_AMBER

    achievements = [
        "Fully working, end-to-end event ticketing web ecosystem served live via PHP 8.3 & MySQL 8.0.",
        "5 Tangible, Demonstable Novelties implemented with zero external framework bloat.",
        "Production-grade security: Cryptographic rotating TOTP, SQL injection immunity, and audio feedback.",
        "Seamless cross-platform usability on desktops, tablets, and smartphones with dark/light themes."
    ]

    for a in achievements:
        p = tf.add_paragraph()
        p.text = f"•  {a}"
        p.font.name = 'Arial'
        p.font.size = Pt(14)
        p.font.color.rgb = TEXT_WHITE
        p.space_before = Pt(6)

    p = tf.add_paragraph()
    p.text = "Future Roadmap Horizons:"
    p.font.name = 'Arial'
    p.font.size = Pt(18)
    p.font.bold = True
    p.font.color.rgb = ACCENT_CYAN
    p.space_before = Pt(18)

    roadmap = [
        "Offline PWA Sync: SQLite local storage cache enabling gate check-in even when internet drops.",
        "Apple / Google Wallet NFC Pass Generation (.pkpass passbook bundles with push notifications).",
        "Autonomous Multi-Agent AI matchmaking facilitating scheduled 1-on-1 attendee meetings."
    ]

    for r in roadmap:
        p = tf.add_paragraph()
        p.text = f"•  {r}"
        p.font.name = 'Arial'
        p.font.size = Pt(14)
        p.font.color.rgb = TEXT_MUTED
        p.space_before = Pt(6)

    output_path = os.path.join(os.path.dirname(__file__), "EventPulse_Presentation.pptx")
    prs.save(output_path)
    print(f"Presentation successfully generated at: {output_path}")

if __name__ == "__main__":
    create_presentation()
