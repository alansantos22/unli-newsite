<?php
/**
 * ============================================
 * SUGGEST COLORS API
 * Sugere paleta de cores baseada no ramo
 * ============================================
 * 
 * POST /api/ai/suggest-colors.php
 */

// Headers e CORS
require_once __DIR__ . '/../lib/cors.php';
header('Content-Type: application/json; charset=utf-8');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Use POST']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$businessType = $input['business_type'] ?? '';
$companyName = $input['company_name'] ?? '';

// ============================================
// FLAT UI COLORS - Cores validadas pelo mercado
// ============================================
// Turquoise: #1ABC9C | Green Sea: #16A085
// Emerald: #2ECC71 | Nephritis: #27AE60
// Peter River: #3498DB | Belize Hole: #2980B9
// Amethyst: #9B59B6 | Wisteria: #8E44AD
// Wet Asphalt: #34495E | Midnight Blue: #2C3E50
// Sun Flower: #F1C40F | Orange: #F39C12
// Carrot: #E67E22 | Pumpkin: #D35400
// Alizarin: #E74C3C | Pomegranate: #C0392B
// Clouds: #ECF0F1 | Silver: #BDC3C7
// Concrete: #95A5A6 | Asbestos: #7F8C8D

// Paletas pré-definidas por ramo (usando APENAS Flat UI Colors)
$palettes = [
    'alimentacao' => [
        [
            'primary' => '#E67E22', // Carrot
            'secondary' => '#F1C40F', // Sun Flower
            'name' => 'Apetitoso',
            'reason' => 'Cores quentes abrem o apetite e passam energia - perfeitas para alimentação'
        ],
        [
            'primary' => '#E74C3C', // Alizarin
            'secondary' => '#F39C12', // Orange
            'name' => 'Sabor Intenso',
            'reason' => 'Vermelho e laranja são os clássicos da gastronomia mundial'
        ],
        [
            'primary' => '#27AE60', // Nephritis
            'secondary' => '#2ECC71', // Emerald
            'name' => 'Natural & Saudável',
            'reason' => 'Tons de verde ideais para alimentação saudável ou orgânica'
        ]
    ],
    'saude' => [
        [
            'primary' => '#1ABC9C', // Turquoise
            'secondary' => '#3498DB', // Peter River
            'name' => 'Saúde & Cuidado',
            'reason' => 'Turquesa transmite cuidado, azul passa confiança'
        ],
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#2980B9', // Belize Hole
            'name' => 'Clínico Moderno',
            'reason' => 'Azul é a cor mais usada em saúde - passa credibilidade'
        ],
        [
            'primary' => '#2ECC71', // Emerald
            'secondary' => '#1ABC9C', // Turquoise
            'name' => 'Bem-estar',
            'reason' => 'Verde e turquesa transmitem saúde e tranquilidade'
        ]
    ],
    'beleza' => [
        [
            'primary' => '#9B59B6', // Amethyst
            'secondary' => '#E74C3C', // Alizarin
            'name' => 'Glamour',
            'reason' => 'Roxo e vermelho são sofisticados e expressivos'
        ],
        [
            'primary' => '#8E44AD', // Wisteria
            'secondary' => '#9B59B6', // Amethyst
            'name' => 'Elegante',
            'reason' => 'Tons de roxo passam sofisticação e feminilidade'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#ECF0F1', // Clouds
            'name' => 'Minimalista Chic',
            'reason' => 'Preto e branco para uma estética premium'
        ]
    ],
    'tecnologia' => [
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#2C3E50', // Midnight Blue
            'name' => 'Tech Moderno',
            'reason' => 'Azul transmite confiança, escuro passa profissionalismo'
        ],
        [
            'primary' => '#9B59B6', // Amethyst
            'secondary' => '#3498DB', // Peter River
            'name' => 'Inovação',
            'reason' => 'Roxo é inovação, azul é tecnologia confiável'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#1ABC9C', // Turquoise
            'name' => 'Developer',
            'reason' => 'Inspirado em terminais modernos'
        ]
    ],
    'educacao' => [
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#9B59B6', // Amethyst
            'name' => 'Conhecimento',
            'reason' => 'Azul estimula concentração, roxo criatividade'
        ],
        [
            'primary' => '#E74C3C', // Alizarin
            'secondary' => '#1ABC9C', // Turquoise
            'name' => 'Divertido',
            'reason' => 'Cores vibrantes para cursos mais descontraídos'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#3498DB', // Peter River
            'name' => 'Acadêmico',
            'reason' => 'Tons sóbrios para instituições tradicionais'
        ]
    ],
    'juridico' => [
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#3498DB', // Peter River
            'name' => 'Sóbrio & Sério',
            'reason' => 'Tons escuros passam seriedade, azul confiança'
        ],
        [
            'primary' => '#34495E', // Wet Asphalt
            'secondary' => '#95A5A6', // Concrete
            'name' => 'Clássico Moderno',
            'reason' => 'Equilíbrio entre tradição e modernidade'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#BDC3C7', // Silver
            'name' => 'Executivo',
            'reason' => 'Elegância corporativa tradicional'
        ]
    ],
    'construcao' => [
        [
            'primary' => '#E67E22', // Carrot
            'secondary' => '#34495E', // Wet Asphalt
            'name' => 'Construção Forte',
            'reason' => 'Laranja passa energia e movimento, cinza escuro solidez'
        ],
        [
            'primary' => '#F1C40F', // Sun Flower
            'secondary' => '#2C3E50', // Midnight Blue
            'name' => 'Obra em Ação',
            'reason' => 'Amarelo é cor de segurança e chama atenção'
        ],
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#95A5A6', // Concrete
            'name' => 'Construtora Moderna',
            'reason' => 'Azul profissional para grandes projetos'
        ]
    ],
    'moda' => [
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#ECF0F1', // Clouds
            'name' => 'Minimalista',
            'reason' => 'Clássico da moda: elegância atemporal'
        ],
        [
            'primary' => '#9B59B6', // Amethyst
            'secondary' => '#E74C3C', // Alizarin
            'name' => 'Elegante & Expressivo',
            'reason' => 'Roxo e vermelho passam sofisticação'
        ],
        [
            'primary' => '#34495E', // Wet Asphalt
            'secondary' => '#BDC3C7', // Silver
            'name' => 'Sofisticado',
            'reason' => 'Tons neutros para estética premium'
        ]
    ],
    'pets' => [
        [
            'primary' => '#E67E22', // Carrot
            'secondary' => '#1ABC9C', // Turquoise
            'name' => 'Alegre & Divertido',
            'reason' => 'Cores vibrantes que passam energia e diversão'
        ],
        [
            'primary' => '#2ECC71', // Emerald
            'secondary' => '#3498DB', // Peter River
            'name' => 'Pet Natural',
            'reason' => 'Verde para clínicas ou produtos naturais'
        ],
        [
            'primary' => '#9B59B6', // Amethyst
            'secondary' => '#F1C40F', // Sun Flower
            'name' => 'Pet Premium',
            'reason' => 'Sofisticado para petshops de alto padrão'
        ]
    ],
    'eventos' => [
        [
            'primary' => '#9B59B6', // Amethyst
            'secondary' => '#F1C40F', // Sun Flower
            'name' => 'Celebração',
            'reason' => 'Roxo é festivo, amarelo é comemorativo e alegre'
        ],
        [
            'primary' => '#E74C3C', // Alizarin
            'secondary' => '#3498DB', // Peter River
            'name' => 'Festa Vibrante',
            'reason' => 'Cores fortes e vibrantes para festas animadas'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#F39C12', // Orange
            'name' => 'Elegante',
            'reason' => 'Escuro com dourado para eventos sofisticados'
        ]
    ],
    'imoveis' => [
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#2ECC71', // Emerald
            'name' => 'Confiança',
            'reason' => 'Azul passa segurança, verde crescimento e prosperidade'
        ],
        [
            'primary' => '#2C3E50', // Midnight Blue
            'secondary' => '#F39C12', // Orange
            'name' => 'Alto Padrão',
            'reason' => 'Para imóveis de luxo - elegante e premium'
        ],
        [
            'primary' => '#34495E', // Wet Asphalt
            'secondary' => '#3498DB', // Peter River
            'name' => 'Imobiliária Tradicional',
            'reason' => 'Cores sóbrias que passam credibilidade'
        ]
    ],
    'automotivo' => [
        [
            'primary' => '#E74C3C', // Alizarin
            'secondary' => '#2C3E50', // Midnight Blue
            'name' => 'Potência',
            'reason' => 'Vermelho é velocidade, escuro é poder'
        ],
        [
            'primary' => '#3498DB', // Peter River
            'secondary' => '#7F8C8D', // Asbestos
            'name' => 'Oficina Profissional',
            'reason' => 'Azul passa confiança técnica'
        ],
        [
            'primary' => '#F1C40F', // Sun Flower
            'secondary' => '#34495E', // Wet Asphalt
            'name' => 'Auto Center',
            'reason' => 'Amarelo chama atenção e passa energia'
        ]
    ]
];

// Default palette (Flat UI Colors)
$defaultPalettes = [
    [
        'primary' => '#3498DB', // Peter River
        'secondary' => '#2ECC71', // Emerald
        'name' => 'Profissional',
        'reason' => 'Combinação clássica de confiança e crescimento'
    ],
    [
        'primary' => '#9B59B6', // Amethyst
        'secondary' => '#3498DB', // Peter River
        'name' => 'Moderno',
        'reason' => 'Roxo traz inovação, azul equilibra com confiança'
    ],
    [
        'primary' => '#2C3E50', // Midnight Blue
        'secondary' => '#1ABC9C', // Turquoise
        'name' => 'Corporativo',
        'reason' => 'Elegante e profissional - funciona para qualquer ramo'
    ]
];

// Buscar paleta
$suggestions = $palettes[$businessType] ?? $defaultPalettes;

echo json_encode([
    'success' => true,
    'business_type' => $businessType,
    'suggestions' => $suggestions
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
