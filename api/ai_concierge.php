<?php
/**
 * EventPulse - API: AI Concierge Schedule & Matchmaker Engine
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$role = $input['role'] ?? 'Software Engineer';
$goal = $input['goal'] ?? 'Technical Masterclasses';
$pace = $input['pace'] ?? 'Balanced';

$pdo = Database::getConnection();

// Fetch scheduled sessions for event 1 (or general)
$stmt = $pdo->prepare("SELECT * FROM event_schedule WHERE event_id = 1 ORDER BY id ASC");
$stmt->execute();
$dbSessions = $stmt->fetchAll();

// Curated session tracks tailored by role
$trackProfiles = [
    'Software Engineer' => [
        'title' => 'Deep Tech & System Architecture Track',
        'desc' => 'Handpicked for high-throughput engineers, autonomous model deployment, and distributed systems.',
        'sessions' => [
            [
                'time' => '09:00 AM - 10:15 AM',
                'title' => 'Opening Keynote: Frontier of Autonomous Agency',
                'speaker' => 'Dr. Aris Thorne (Chief Scientist)',
                'room' => 'Main Stage - Grand Auditorium',
                'description' => 'Explore the transition from passive LLMs to stateful agent reasoning loops.',
                'relevanceScore' => 98
            ],
            [
                'time' => '10:30 AM - 11:45 AM',
                'title' => 'Real-time Multimodal Synthesis & Spatial Latency',
                'speaker' => 'Maya Lin (VP Research)',
                'room' => 'Deep Tech Stage - Hall B',
                'description' => 'Architectural patterns for sub-10ms neural audio/video streaming.',
                'relevanceScore' => 96
            ],
            [
                'time' => '01:30 PM - 03:00 PM',
                'title' => 'Hands-on Lab: Fine-tuning Foundation Models on Edge GPUs',
                'speaker' => 'Frontier Core Engineering Team',
                'room' => 'Developer Lab Suite 3',
                'description' => 'Quantization, LoRA adapters, and on-device inference benchmarking.',
                'relevanceScore' => 99
            ],
            [
                'time' => '04:00 PM - 05:30 PM',
                'title' => 'Open-Source AI Infrastructure Roundtable',
                'speaker' => 'Engineering Directors',
                'room' => 'Forum Room C',
                'description' => 'Debating open weights vs API endpoints and self-hosted inference clusters.',
                'relevanceScore' => 92
            ]
        ],
        'cohorts' => [
            ['name' => 'Kernel & Inference Hackers', 'icon' => 'bi-terminal-fill', 'matchScore' => 97, 'description' => 'Engineers optimizing CUDA kernels, vLLM, and model serving runtimes.'],
            ['name' => 'Autonomous Agent Builders', 'icon' => 'bi-robot', 'matchScore' => 94, 'description' => 'Founders and devs building production multi-agent workflows.']
        ]
    ],
    'Founder / Executive' => [
        'title' => 'Venture Growth & Frontier Strategy Track',
        'desc' => 'Designed for venture-backed founders, enterprise executives, and technology strategists.',
        'sessions' => [
            [
                'time' => '09:00 AM - 10:15 AM',
                'title' => 'Opening Keynote: The Macro Economics of Frontier AI',
                'speaker' => 'Dr. Aris Thorne',
                'room' => 'Main Stage - Grand Auditorium',
                'description' => 'Capital allocation, sovereign compute, and enterprise moats in 2026.',
                'relevanceScore' => 95
            ],
            [
                'time' => '11:00 AM - 12:30 PM',
                'title' => 'Venture Panel: Where Capital is Flowing in 2026-2027',
                'speaker' => 'Julian Sterling & Top Tier VCs',
                'room' => 'Main Stage - Grand Auditorium',
                'description' => 'Unfiltered discussions on seed vs series B valuations and market consolidation.',
                'relevanceScore' => 99
            ],
            [
                'time' => '01:30 PM - 03:30 PM',
                'title' => 'Closed-Door Founder & Investor Speed Roundtables',
                'speaker' => 'Curated VC Matchmaking Cohort',
                'room' => 'VIP Sovereign Lounge',
                'description' => '1-on-1 scheduled deal briefings with partners from leading deep-tech venture funds.',
                'relevanceScore' => 98
            ],
            [
                'time' => '04:30 PM - 06:00 PM',
                'title' => 'Cocktail Mixer: Founders Scaling from 1 to 100M ARR',
                'speaker' => 'Executive Network',
                'room' => 'Rooftop Sunset Terrace',
                'description' => 'Relaxed evening drinks with founders who scaled enterprise AI sales.',
                'relevanceScore' => 96
            ]
        ],
        'cohorts' => [
            ['name' => 'Series A/B Founder Circle', 'icon' => 'bi-graph-up-arrow', 'matchScore' => 99, 'description' => 'CEOs sharing go-to-market playbooks and international expansion tactics.'],
            ['name' => 'Deep Tech Angel Syndicate', 'icon' => 'bi-coin', 'matchScore' => 95, 'description' => 'Private syndicates deploying capital in edge computing and robotics.']
        ]
    ],
    'Product & UI/UX Designer' => [
        'title' => 'Generative Interface & Spatial Design Track',
        'desc' => 'Tailored for interaction designers, design system engineers, and spatial computing creators.',
        'sessions' => [
            [
                'time' => '09:30 AM - 10:45 AM',
                'title' => 'The Death of Static UI: Designing Self-Synthesizing Interfaces',
                'speaker' => 'Elena Vance (Apex Creative Director)',
                'room' => 'Design Theater - Hall C',
                'description' => 'How dynamic intent-driven components are replacing fixed dashboard wireframes.',
                'relevanceScore' => 98
            ],
            [
                'time' => '11:15 AM - 12:30 PM',
                'title' => 'Spatial Canvas & Haptic Micro-Interactions in AR/VR',
                'speaker' => 'Spatial Design Lab Lead',
                'room' => 'Immersive Studio A',
                'description' => 'Crafting depth cues, glass physics, and tactile audio feedback in modern apps.',
                'relevanceScore' => 95
            ],
            [
                'time' => '02:00 PM - 03:30 PM',
                'title' => 'Critique Lounge: Live Portfolio & Product Teardowns',
                'speaker' => 'Global Design Directors',
                'room' => 'Atrium Pavilion',
                'description' => 'Get real-time feedback on your product ergonomics and interaction hierarchy.',
                'relevanceScore' => 93
            ]
        ],
        'cohorts' => [
            ['name' => 'Spatial UX Collective', 'icon' => 'bi-bounding-box-circles', 'matchScore' => 96, 'description' => 'Designers exploring VisionOS, AR headsets, and 3D web interfaces.'],
            ['name' => 'Design System Architects', 'icon' => 'bi-layers-fill', 'matchScore' => 92, 'description' => 'Design leads standardizing cross-platform tokens and accessibility.']
        ]
    ]
];

$selectedProfile = $trackProfiles[$role] ?? $trackProfiles['Software Engineer'];

echo json_encode([
    'success' => true,
    'attendeeProfile' => [
        'role' => $role,
        'goal' => $goal,
        'pace' => $pace
    ],
    'summaryTitle' => $selectedProfile['title'],
    'summaryDescription' => $selectedProfile['desc'],
    'itinerary' => $selectedProfile['sessions'],
    'networkingMatches' => $selectedProfile['cohorts']
]);
