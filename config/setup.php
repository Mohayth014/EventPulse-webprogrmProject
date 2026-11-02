<?php
/**
 * EventPulse - Database Schema Migration & Seeder Script
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$pdo = Database::getConnection();

// Create Tables
$queries = [
    // Users table
    "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'organizer', 'attendee') DEFAULT 'attendee',
        `phone` VARCHAR(30) NULL,
        `organization` VARCHAR(150) NULL,
        `avatar` VARCHAR(255) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Categories
    "CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `icon` VARCHAR(50) NOT NULL,
        `description` TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Events
    "CREATE TABLE IF NOT EXISTS `events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `organizer_id` INT NOT NULL,
        `category_id` INT NOT NULL,
        `title` VARCHAR(200) NOT NULL,
        `slug` VARCHAR(220) NOT NULL UNIQUE,
        `tagline` VARCHAR(255) NOT NULL,
        `description` LONGTEXT NOT NULL,
        `venue_name` VARCHAR(150) NOT NULL,
        `venue_address` VARCHAR(255) NOT NULL,
        `city` VARCHAR(100) NOT NULL,
        `start_date` DATETIME NOT NULL,
        `end_date` DATETIME NOT NULL,
        `banner_image` VARCHAR(255) NOT NULL,
        `seating_type` ENUM('general', 'seated') DEFAULT 'seated',
        `total_seats` INT DEFAULT 120,
        `is_featured` TINYINT(1) DEFAULT 0,
        `status` ENUM('published', 'draft', 'ended') DEFAULT 'published',
        `dynamic_pricing_enabled` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`category_id`),
        INDEX (`organizer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Ticket Tiers
    "CREATE TABLE IF NOT EXISTS `ticket_tiers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `description` VARCHAR(255) NULL,
        `price` DECIMAL(10, 2) NOT NULL,
        `base_price` DECIMAL(10, 2) NOT NULL,
        `capacity` INT NOT NULL,
        `sold_count` INT DEFAULT 0,
        `perks_json` TEXT NULL,
        `color_code` VARCHAR(20) DEFAULT '#3b82f6',
        INDEX (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Interactive Seats
    "CREATE TABLE IF NOT EXISTS `seats` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `tier_id` INT NOT NULL,
        `seat_number` VARCHAR(15) NOT NULL,
        `row_name` VARCHAR(5) NOT NULL,
        `col_number` INT NOT NULL,
        `status` ENUM('available', 'reserved', 'booked') DEFAULT 'available',
        `locked_by_session` VARCHAR(100) NULL,
        `locked_at` DATETIME NULL,
        INDEX (`event_id`),
        INDEX (`tier_id`),
        UNIQUE KEY `unique_event_seat` (`event_id`, `seat_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Squads (Novelty 4)
    "CREATE TABLE IF NOT EXISTS `squads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `squad_leader_id` INT NOT NULL,
        `squad_code` VARCHAR(20) NOT NULL UNIQUE,
        `squad_name` VARCHAR(100) NOT NULL,
        `discount_percent` INT DEFAULT 15,
        `member_count` INT DEFAULT 1,
        `target_size` INT DEFAULT 4,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Bookings
    "CREATE TABLE IF NOT EXISTS `bookings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `booking_reference` VARCHAR(40) NOT NULL UNIQUE,
        `user_id` INT NOT NULL,
        `event_id` INT NOT NULL,
        `squad_id` INT NULL,
        `total_amount` DECIMAL(10, 2) NOT NULL,
        `discount_amount` DECIMAL(10, 2) DEFAULT 0.00,
        `carbon_offset_fee` DECIMAL(10, 2) DEFAULT 0.00,
        `final_amount` DECIMAL(10, 2) NOT NULL,
        `payment_status` ENUM('paid', 'pending', 'refunded') DEFAULT 'paid',
        `payment_method` VARCHAR(50) DEFAULT 'Card / Digital Pass',
        `qr_token` VARCHAR(100) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`user_id`),
        INDEX (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Ticket Items
    "CREATE TABLE IF NOT EXISTS `ticket_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `booking_id` INT NOT NULL,
        `tier_id` INT NOT NULL,
        `seat_id` INT NULL,
        `attendee_name` VARCHAR(100) NOT NULL,
        `attendee_email` VARCHAR(150) NOT NULL,
        `ticket_hash` VARCHAR(80) NOT NULL UNIQUE,
        `checkin_status` ENUM('pending', 'checked_in') DEFAULT 'pending',
        `checkin_time` DATETIME NULL,
        `eco_badge_granted` TINYINT(1) DEFAULT 0,
        INDEX (`booking_id`),
        INDEX (`ticket_hash`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Speakers
    "CREATE TABLE IF NOT EXISTS `event_speakers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `role` VARCHAR(100) NOT NULL,
        `company` VARCHAR(100) NOT NULL,
        `bio` TEXT NULL,
        `avatar` VARCHAR(255) NULL,
        INDEX (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Event Schedule
    "CREATE TABLE IF NOT EXISTS `event_schedule` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `event_id` INT NOT NULL,
        `title` VARCHAR(200) NOT NULL,
        `speaker` VARCHAR(100) NULL,
        `start_time` VARCHAR(20) NOT NULL,
        `end_time` VARCHAR(20) NOT NULL,
        `track` VARCHAR(50) DEFAULT 'Main Stage',
        `room` VARCHAR(50) DEFAULT 'Auditorium A',
        `description` TEXT NULL,
        INDEX (`event_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    // Carbon Offsets (Novelty 5)
    "CREATE TABLE IF NOT EXISTS `carbon_offsets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `booking_id` INT NOT NULL,
        `trees_funded` INT DEFAULT 1,
        `kg_co2_offset` DECIMAL(8, 2) DEFAULT 25.00,
        `certificate_hash` VARCHAR(64) NOT NULL UNIQUE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`booking_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

foreach ($queries as $sql) {
    $pdo->exec($sql);
}

// Seed Users
$userCheck = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
if ($userCheck == 0) {
    $users = [
        [
            'name' => 'Alex Rivera (Administrator)',
            'email' => 'admin@eventpulse.io',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'role' => 'admin',
            'phone' => '+1 (555) 019-2831',
            'organization' => 'EventPulse HQ Global',
            'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80'
        ],
        [
            'name' => 'Elena Vance (Organizer)',
            'email' => 'organizer@eventpulse.io',
            'password_hash' => password_hash('organizer123', PASSWORD_BCRYPT),
            'role' => 'organizer',
            'phone' => '+1 (555) 438-9210',
            'organization' => 'Apex Event Productions',
            'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=250&q=80'
        ],
        [
            'name' => 'Sarah Connor (Attendee)',
            'email' => 'attendee@eventpulse.io',
            'password_hash' => password_hash('attendee123', PASSWORD_BCRYPT),
            'role' => 'attendee',
            'phone' => '+1 (555) 872-3319',
            'organization' => 'TechNovation Labs',
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=250&q=80'
        ]
    ];

    $uStmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `phone`, `organization`, `avatar`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $uStmt->execute([$u['name'], $u['email'], $u['password_hash'], $u['role'], $u['phone'], $u['organization'], $u['avatar']]);
    }
}

// Seed Categories
$catCheck = $pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
if ($catCheck == 0) {
    $categories = [
        ['Technology & AI', 'tech-ai', 'bi-cpu', 'Conferences, AI expos, robotics, hackathons & developer summits.'],
        ['Music & Festivals', 'music-festivals', 'bi-music-note-beamed', 'Live concerts, EDM festivals, acoustic gigs and jazz nights.'],
        ['Business & Startups', 'business-startups', 'bi-briefcase', 'Venture capital summits, startup showcases & leadership keynotes.'],
        ['Gaming & Esports', 'gaming-esports', 'bi-controller', 'Game developer gatherings, esports championships & LAN parties.'],
        ['Design & Arts', 'design-arts', 'bi-palette', 'UI/UX conferences, digital art exhibitions & creative bootcamps.'],
        ['Food & Lifestyle', 'food-lifestyle', 'bi-cup-hot', 'Gourmet expos, culinary masterclasses & wellness retreats.']
    ];

    $cStmt = $pdo->prepare("INSERT INTO `categories` (`name`, `slug`, `icon`, `description`) VALUES (?, ?, ?, ?)");
    foreach ($categories as $c) {
        $cStmt->execute($c);
    }
}

// Seed Events
$evCheck = $pdo->query("SELECT COUNT(*) FROM `events`")->fetchColumn();
if ($evCheck == 0) {
    $events = [
        [
            'organizer_id' => 2,
            'category_id' => 1,
            'title' => 'Global AI & Autonomous Systems Summit 2026',
            'slug' => 'global-ai-summit-2026',
            'tagline' => 'The vanguard of frontier intelligence, quantum computing and autonomous agent systems.',
            'description' => "Welcome to the world's most prestigious convention on frontier AI architectures, multimodal perception, and autonomous intelligent agents. Over three immersive days, witness 40+ keynote addresses from renowned researchers, live hardware demos, hands-on masterclasses, and an unprecedented networking floor connecting founders, engineers, and global investors.",
            'venue_name' => 'Silicon Horizon Mega Convention Center',
            'venue_address' => '500 Innovation Way, Tech District',
            'city' => 'San Francisco, CA',
            'start_date' => '2026-11-18 09:00:00',
            'end_date' => '2026-11-20 18:00:00',
            'banner_image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 1,
            'dynamic_pricing_enabled' => 1
        ],
        [
            'organizer_id' => 2,
            'category_id' => 2,
            'title' => 'Neon Odyssey: Cyberpunk EDM Festival',
            'slug' => 'neon-odyssey-edm-fest',
            'tagline' => 'A futuristic audio-visual multisensory spectacle with laser holographics.',
            'description' => "Prepare to transcend auditory reality. Neon Odyssey gathers the globe's finest electronic, synthwave, and techno artists on a massive 360-degree holographic stage. Featuring custom immersive sound arrays, spatial lasers, and wearable synced LED bands for every attendee.",
            'venue_name' => 'Starlight Amphitheater & Dome',
            'venue_address' => '88 Horizon Boulevard',
            'city' => 'Austin, TX',
            'start_date' => '2026-11-28 17:00:00',
            'end_date' => '2026-11-29 02:00:00',
            'banner_image' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 1,
            'dynamic_pricing_enabled' => 1
        ],
        [
            'organizer_id' => 2,
            'category_id' => 3,
            'title' => 'VentureForge: Founder & Investor Conclave 2026',
            'slug' => 'ventureforge-investor-conclave',
            'tagline' => 'Connecting elite early-stage founders with tier-1 venture capitalists.',
            'description' => "VentureForge is the annual high-stakes summit where capital meets game-changing technology. Featuring speed-pitch arenas, closed-door LP roundtables, term sheet workshops, and deal lounges where over $250M in funding discussions initiate.",
            'venue_name' => 'The Grand Plaza Sovereign Hall',
            'venue_address' => '120 Park Avenue, Financial Hub',
            'city' => 'New York, NY',
            'start_date' => '2026-12-05 08:30:00',
            'end_date' => '2026-12-06 17:30:00',
            'banner_image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 1,
            'dynamic_pricing_enabled' => 0
        ],
        [
            'organizer_id' => 2,
            'category_id' => 4,
            'title' => 'Nexus Arena: Esports Championship & Indie Jam',
            'slug' => 'nexus-arena-esports-jam',
            'tagline' => 'Battle for the regional crown and build tomorrow’s indie games in 48 hours.',
            'description' => "Nexus Arena fuses championship tier competitive gaming with an energetic 48-hour game jam. Join 1,200 gamers, stream live on jumbo OLED stadium displays, and meet game developers from top global studios.",
            'venue_name' => 'CyberPulse Arena Dome',
            'venue_address' => '740 Arena Way',
            'city' => 'Seattle, WA',
            'start_date' => '2026-12-12 10:00:00',
            'end_date' => '2026-12-14 20:00:00',
            'banner_image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 0,
            'dynamic_pricing_enabled' => 1
        ],
        [
            'organizer_id' => 2,
            'category_id' => 5,
            'title' => 'Pixel & Poly: Future of UI/UX & Spatial Design',
            'slug' => 'pixel-poly-spatial-design',
            'tagline' => 'Crafting spatial interfaces, neuromorphic design systems and haptics.',
            'description' => "Explore how generative interfaces, augmented spatial glasses, and responsive typography are redefining interaction design. Hands-on critique lounges with design directors from top consumer tech firms.",
            'venue_name' => 'Metropolis Arts & Design Pavilion',
            'venue_address' => '330 Broadway Center',
            'city' => 'Chicago, IL',
            'start_date' => '2026-12-18 09:30:00',
            'end_date' => '2026-12-19 18:00:00',
            'banner_image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 0,
            'dynamic_pricing_enabled' => 0
        ],
        [
            'organizer_id' => 2,
            'category_id' => 6,
            'title' => 'Epicurean Masters: Global Culinary Symposium',
            'slug' => 'epicurean-masters-culinary-symposium',
            'tagline' => 'Michelin star chefs, molecular gastronomy demonstrations and rare wine tastings.',
            'description' => "An extraordinary multi-course culinary experience featuring 12 Michelin-starred master chefs demonstrating advanced techniques, live tasting flights, and sustainable farm-to-table workshops.",
            'venue_name' => 'The Glasshouse Waterfront',
            'venue_address' => '10 Ocean Drive',
            'city' => 'Miami, FL',
            'start_date' => '2026-12-28 12:00:00',
            'end_date' => '2026-12-29 22:00:00',
            'banner_image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
            'seating_type' => 'seated',
            'total_seats' => 96,
            'is_featured' => 0,
            'dynamic_pricing_enabled' => 1
        ]
    ];

    $eStmt = $pdo->prepare("INSERT INTO `events` (`organizer_id`, `category_id`, `title`, `slug`, `tagline`, `description`, `venue_name`, `venue_address`, `city`, `start_date`, `end_date`, `banner_image`, `seating_type`, `total_seats`, `is_featured`, `dynamic_pricing_enabled`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($events as $ev) {
        $eStmt->execute([
            $ev['organizer_id'], $ev['category_id'], $ev['title'], $ev['slug'], $ev['tagline'],
            $ev['description'], $ev['venue_name'], $ev['venue_address'], $ev['city'],
            $ev['start_date'], $ev['end_date'], $ev['banner_image'], $ev['seating_type'],
            $ev['total_seats'], $ev['is_featured'], $ev['dynamic_pricing_enabled']
        ]);
    }
}

// Seed Ticket Tiers & Seats for each event
$tierCheck = $pdo->query("SELECT COUNT(*) FROM `ticket_tiers`")->fetchColumn();
if ($tierCheck == 0) {
    $allEvents = $pdo->query("SELECT id FROM `events`")->fetchAll();

    foreach ($allEvents as $evRow) {
        $eventId = $evRow['id'];

        // Tiers: VIP Platinum, Premium Gold, General Admission
        $tiersData = [
            [
                'name' => 'VIP Sovereign Pass',
                'description' => 'Front row priority seating, private lounge access, speaker mixer & gift box.',
                'price' => 299.00,
                'base_price' => 299.00,
                'capacity' => 24,
                'perks' => ['Front Row Seating (Rows A-B)', 'Private Backstage Lounge', '1-on-1 Speaker Mixer', 'Exclusive Swag & Dinner Pass'],
                'color' => '#8b5cf6' // Purple
            ],
            [
                'name' => 'Premium Gold Pass',
                'description' => 'Mid-tier prime viewing, fast-track venue entry and workshop access.',
                'price' => 149.00,
                'base_price' => 149.00,
                'capacity' => 36,
                'perks' => ['Prime Mid-Auditorium Rows (C-D)', 'Fast-Track Queue Entry', 'Workshop Recording Access', 'Complimentary Gourmet Lunch'],
                'color' => '#3b82f6' // Blue
            ],
            [
                'name' => 'Standard Access',
                'description' => 'Full event access, keynote tracks, exhibition hall & standard kit.',
                'price' => 79.00,
                'base_price' => 79.00,
                'capacity' => 36,
                'perks' => ['Standard Seating Rows (E-F)', 'Full Access to Main Stage', 'Exhibition Hall Floor Pass', 'Official Event Certificate'],
                'color' => '#10b981' // Green
            ]
        ];

        $tStmt = $pdo->prepare("INSERT INTO `ticket_tiers` (`event_id`, `name`, `description`, `price`, `base_price`, `capacity`, `perks_json`, `color_code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $sStmt = $pdo->prepare("INSERT INTO `seats` (`event_id`, `tier_id`, `seat_number`, `row_name`, `col_number`, `status`) VALUES (?, ?, ?, ?, ?, ?)");

        $tierIds = [];
        foreach ($tiersData as $td) {
            $tStmt->execute([
                $eventId, $td['name'], $td['description'], $td['price'], $td['base_price'],
                $td['capacity'], json_encode($td['perks']), $td['color']
            ]);
            $tierIds[] = $pdo->lastInsertId();
        }

        // Generate 96 seats (6 rows x 16 seats per row)
        // Rows A, B -> VIP Sovereign Pass (Tier 0)
        // Rows C, D -> Premium Gold Pass (Tier 1)
        // Rows E, F -> Standard Access (Tier 2)
        $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($rows as $rowIndex => $rName) {
            $tIndex = ($rowIndex < 2) ? 0 : (($rowIndex < 4) ? 1 : 2);
            $assignedTierId = $tierIds[$tIndex];

            for ($col = 1; $col <= 16; $col++) {
                $seatNumber = sprintf('%s%02d', $rName, $col);
                // Pre-book a couple seats to make venue map realistic
                $status = 'available';
                if (($rName == 'A' && in_array($col, [3, 4, 8])) || ($rName == 'C' && in_array($col, [5, 6, 12]))) {
                    $status = 'booked';
                }
                $sStmt->execute([$eventId, $assignedTierId, $seatNumber, $rName, $col, $status]);
            }
        }
    }
}

// Seed Speakers
$spCheck = $pdo->query("SELECT COUNT(*) FROM `event_speakers`")->fetchColumn();
if ($spCheck == 0) {
    $speakers = [
        [1, 'Dr. Aris Thorne', 'Chief Scientist', 'Frontier AI Labs', 'Pioneering work in autonomous agent memory systems and sparse transformer architectures.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=250&q=80'],
        [1, 'Maya Lin', 'VP of Multimodal Research', 'Synthetix Dynamics', 'Leading next-gen neural video synthesis and real-time sensory perception engines.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=250&q=80'],
        [1, 'Julian Sterling', 'Founder & Partner', 'Apex Quantum Capital', 'Angel investor backing over 30 AI unicorns and autonomous hardware ventures.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=250&q=80'],
        [2, 'DJ Kaelen Nova', 'Headliner & Audio Architect', 'Neon Records', 'World-touring synthwave icon with over 200M streams and custom modular synthesizers.', 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=250&q=80'],
        [2, 'Aria Vane', 'Holographic Lighting Director', 'Vivid Matrix', 'Creator of revolutionary interactive spatial laser installations at Tomorrowland.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=250&q=80'],
        [3, 'Marcus Sterling', 'Managing Director', 'Beacon Ventures', 'Former tech CEO who completed 4 NASDAQ IPOs and advises Fortune 100 leaders.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=250&q=80'],
    ];

    $spStmt = $pdo->prepare("INSERT INTO `event_speakers` (`event_id`, `name`, `role`, `company`, `bio`, `avatar`) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($speakers as $sp) {
        $spStmt->execute($sp);
    }
}

// Seed Schedule
$scCheck = $pdo->query("SELECT COUNT(*) FROM `event_schedule`")->fetchColumn();
if ($scCheck == 0) {
    $schedule = [
        [1, 'Opening Keynote: The Frontier of Autonomous Agency', 'Dr. Aris Thorne', '09:00 AM', '10:15 AM', 'Main Stage', 'Grand Auditorium', 'A deep dive into how self-reasoning AI agents will transform global engineering.'],
        [1, 'Real-time Multimodal Synthesis & Spatial Computing', 'Maya Lin', '10:30 AM', '11:45 AM', 'Deep Tech Stage', 'Hall B', 'Live demonstration of sub-10ms multimodal latency architectures.'],
        [1, 'Networking Lunch & AI Startup Pitch Exhibition', NULL, '12:00 PM', '01:30 PM', 'Exhibition Arena', 'Atrium Courtyard', 'Connect with 60+ early-stage startups and VC representatives.'],
        [1, 'Venture Panel: Where Capital is Flowing in 2026-2027', 'Julian Sterling', '02:00 PM', '03:30 PM', 'Main Stage', 'Grand Auditorium', 'Top VCs dissect market trends, valuations, and sovereign AI investments.'],
        [1, 'Hands-on Lab: Fine-tuning Foundation Models on Edge GPUs', 'Aris Thorne & Engineers', '04:00 PM', '05:30 PM', 'Developer Lab', 'Workshop Suite 3', 'Bring your laptop for an interactive model pruning and quantization session.']
    ];

    $scStmt = $pdo->prepare("INSERT INTO `event_schedule` (`event_id`, `title`, `speaker`, `start_time`, `end_time`, `track`, `room`, `description`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($schedule as $sc) {
        $scStmt->execute($sc);
    }
}

// Seed Demo Booking with Ticket Items (so Attendee has tickets and Admin has analytics)
$bkCheck = $pdo->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
if ($bkCheck == 0) {
    $ref = 'EP-2026-X8910';
    $qrToken = bin2hex(random_bytes(16));
    $bStmt = $pdo->prepare("INSERT INTO `bookings` (`booking_reference`, `user_id`, `event_id`, `total_amount`, `discount_amount`, `carbon_offset_fee`, `final_amount`, `payment_status`, `payment_method`, `qr_token`) VALUES (?, 3, 1, 299.00, 0.00, 5.00, 304.00, 'paid', 'Visa ending in 4242', ?)");
    $bStmt->execute([$ref, $qrToken]);
    $bookingId = $pdo->lastInsertId();

    // Ticket Item
    $seat = $pdo->query("SELECT id FROM `seats` WHERE `event_id` = 1 AND `seat_number` = 'A03'")->fetch();
    $seatId = $seat ? $seat['id'] : null;
    $tier = $pdo->query("SELECT id FROM `ticket_tiers` WHERE `event_id` = 1 LIMIT 1")->fetch();
    $tierId = $tier ? $tier['id'] : 1;

    $ticketHash = hash('sha256', $ref . 'Sarah Connor' . APP_SECRET_KEY);
    $tiStmt = $pdo->prepare("INSERT INTO `ticket_items` (`booking_id`, `tier_id`, `seat_id`, `attendee_name`, `attendee_email`, `ticket_hash`, `checkin_status`, `eco_badge_granted`) VALUES (?, ?, ?, 'Sarah Connor', 'attendee@eventpulse.io', ?, 'pending', 1)");
    $tiStmt->execute([$bookingId, $tierId, $seatId, $ticketHash]);

    // Carbon offset record
    $certHash = hash('sha256', $bookingId . time() . 'eco_tree');
    $coStmt = $pdo->prepare("INSERT INTO `carbon_offsets` (`booking_id`, `trees_funded`, `kg_co2_offset`, `certificate_hash`) VALUES (?, 2, 50.00, ?)");
    $coStmt->execute([$bookingId, $certHash]);
}

echo "MIGRATION_AND_SEED_SUCCESSFUL\n";
