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

// Paletas pré-definidas por ramo
$palettes = [
    'alimentacao' => [
        [
            'primary' => '#FF6B35',
            'secondary' => '#FFC107',
            'name' => 'Apetitoso',
            'reason' => 'Cores quentes estimulam o apetite e passam energia'
        ],
        [
            'primary' => '#E63946',
            'secondary' => '#F4A261',
            'name' => 'Sabor Intenso',
            'reason' => 'Vermelho e laranja são clássicos da gastronomia'
        ],
        [
            'primary' => '#2D6A4F',
            'secondary' => '#95D5B2',
            'name' => 'Natural & Saudável',
            'reason' => 'Ideal para alimentação saudável ou orgânica'
        ]
    ],
    'saude' => [
        [
            'primary' => '#28A745',
            'secondary' => '#17A2B8',
            'name' => 'Saúde & Cuidado',
            'reason' => 'Verde transmite saúde, azul transmite confiança'
        ],
        [
            'primary' => '#0077B6',
            'secondary' => '#90E0EF',
            'name' => 'Clínico Moderno',
            'reason' => 'Azul é a cor mais usada em saúde, passa credibilidade'
        ],
        [
            'primary' => '#48CAE4',
            'secondary' => '#CAF0F8',
            'name' => 'Refrescante',
            'reason' => 'Tons claros transmitem limpeza e tranquilidade'
        ]
    ],
    'beleza' => [
        [
            'primary' => '#E83E8C',
            'secondary' => '#6F42C1',
            'name' => 'Glamour',
            'reason' => 'Rosa e roxo são sofisticados e femininos'
        ],
        [
            'primary' => '#D4A373',
            'secondary' => '#FAEDCD',
            'name' => 'Nude Elegante',
            'reason' => 'Tons neutros passam sofisticação e clean beauty'
        ],
        [
            'primary' => '#212529',
            'secondary' => '#F8F9FA',
            'name' => 'Minimalista Chic',
            'reason' => 'Preto e branco para uma estética premium'
        ]
    ],
    'tecnologia' => [
        [
            'primary' => '#0066CC',
            'secondary' => '#6F42C1',
            'name' => 'Tech Moderno',
            'reason' => 'Azul transmite confiança, roxo inovação'
        ],
        [
            'primary' => '#00D9FF',
            'secondary' => '#7B2CBF',
            'name' => 'Futurista',
            'reason' => 'Cores vibrantes e tecnológicas'
        ],
        [
            'primary' => '#212529',
            'secondary' => '#00FF88',
            'name' => 'Developer',
            'reason' => 'Inspirado em terminais e código'
        ]
    ],
    'educacao' => [
        [
            'primary' => '#3A86FF',
            'secondary' => '#8338EC',
            'name' => 'Conhecimento',
            'reason' => 'Azul estimula concentração, roxo criatividade'
        ],
        [
            'primary' => '#FF6B6B',
            'secondary' => '#4ECDC4',
            'name' => 'Divertido',
            'reason' => 'Cores vibrantes para cursos mais descontraídos'
        ],
        [
            'primary' => '#1D3557',
            'secondary' => '#457B9D',
            'name' => 'Acadêmico',
            'reason' => 'Tons sóbrios para instituições tradicionais'
        ]
    ],
    'juridico' => [
        [
            'primary' => '#212529',
            'secondary' => '#0066CC',
            'name' => 'Sóbrio & Sério',
            'reason' => 'Preto passa seriedade, azul confiança'
        ],
        [
            'primary' => '#1D3557',
            'secondary' => '#A8DADC',
            'name' => 'Clássico Moderno',
            'reason' => 'Equilíbrio entre tradição e modernidade'
        ],
        [
            'primary' => '#2C3E50',
            'secondary' => '#BDC3C7',
            'name' => 'Executivo',
            'reason' => 'Elegância corporativa tradicional'
        ]
    ],
    'construcao' => [
        [
            'primary' => '#FF6B35',
            'secondary' => '#212529',
            'name' => 'Construção Forte',
            'reason' => 'Laranja passa energia, preto solidez'
        ],
        [
            'primary' => '#FFC107',
            'secondary' => '#343A40',
            'name' => 'Obra em Ação',
            'reason' => 'Amarelo é cor de segurança e atenção'
        ],
        [
            'primary' => '#0077B6',
            'secondary' => '#ADB5BD',
            'name' => 'Construtora Moderna',
            'reason' => 'Azul profissional para grandes projetos'
        ]
    ],
    'moda' => [
        [
            'primary' => '#212529',
            'secondary' => '#F8F9FA',
            'name' => 'Minimalista',
            'reason' => 'Clássico da moda: elegância atemporal'
        ],
        [
            'primary' => '#E83E8C',
            'secondary' => '#FFC0CB',
            'name' => 'Feminino',
            'reason' => 'Tons de rosa para moda feminina'
        ],
        [
            'primary' => '#8B4513',
            'secondary' => '#DEB887',
            'name' => 'Terroso',
            'reason' => 'Ideal para moda sustentável ou artesanal'
        ]
    ],
    'pets' => [
        [
            'primary' => '#FF6B35',
            'secondary' => '#4ECDC4',
            'name' => 'Alegre & Divertido',
            'reason' => 'Cores vibrantes que passam energia e diversão'
        ],
        [
            'primary' => '#28A745',
            'secondary' => '#90E0EF',
            'name' => 'Pet Natural',
            'reason' => 'Verde para clínicas ou produtos naturais'
        ],
        [
            'primary' => '#6F42C1',
            'secondary' => '#FFC107',
            'name' => 'Pet Premium',
            'reason' => 'Sofisticado para petshops de alto padrão'
        ]
    ],
    'eventos' => [
        [
            'primary' => '#6F42C1',
            'secondary' => '#FFC107',
            'name' => 'Celebração',
            'reason' => 'Roxo é festivo, dourado é comemorativo'
        ],
        [
            'primary' => '#E83E8C',
            'secondary' => '#00D9FF',
            'name' => 'Festa Vibrante',
            'reason' => 'Cores eletrizantes para festas animadas'
        ],
        [
            'primary' => '#212529',
            'secondary' => '#D4AF37',
            'name' => 'Elegante',
            'reason' => 'Preto e dourado para eventos sofisticados'
        ]
    ],
    'imoveis' => [
        [
            'primary' => '#0066CC',
            'secondary' => '#28A745',
            'name' => 'Confiança',
            'reason' => 'Azul passa segurança, verde crescimento'
        ],
        [
            'primary' => '#212529',
            'secondary' => '#D4AF37',
            'name' => 'Alto Padrão',
            'reason' => 'Para imóveis de luxo'
        ],
        [
            'primary' => '#1D3557',
            'secondary' => '#A8DADC',
            'name' => 'Imobiliária Tradicional',
            'reason' => 'Cores sóbrias que passam credibilidade'
        ]
    ],
    'automotivo' => [
        [
            'primary' => '#DC3545',
            'secondary' => '#212529',
            'name' => 'Potência',
            'reason' => 'Vermelho é velocidade, preto é poder'
        ],
        [
            'primary' => '#0066CC',
            'secondary' => '#6C757D',
            'name' => 'Oficina Profissional',
            'reason' => 'Azul passa confiança técnica'
        ],
        [
            'primary' => '#FFC107',
            'secondary' => '#343A40',
            'name' => 'Auto Center',
            'reason' => 'Amarelo chama atenção na estrada'
        ]
    ]
];

// Default palette
$defaultPalettes = [
    [
        'primary' => '#0066CC',
        'secondary' => '#28A745',
        'name' => 'Profissional',
        'reason' => 'Combinação clássica e versátil'
    ],
    [
        'primary' => '#6366F1',
        'secondary' => '#8B5CF6',
        'name' => 'Moderno',
        'reason' => 'Gradiente roxo muito popular em 2024-2026'
    ],
    [
        'primary' => '#212529',
        'secondary' => '#0066CC',
        'name' => 'Corporativo',
        'reason' => 'Elegante e profissional'
    ]
];

// Buscar paleta
$suggestions = $palettes[$businessType] ?? $defaultPalettes;

echo json_encode([
    'success' => true,
    'business_type' => $businessType,
    'suggestions' => $suggestions
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
