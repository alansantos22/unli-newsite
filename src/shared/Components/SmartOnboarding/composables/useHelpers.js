/**
 * ============================================
 * SMART ONBOARDING - CEP & HELPERS SERVICE
 * ============================================
 * 
 * Serviços auxiliares para:
 * - Busca de CEP (ViaCEP)
 * - Formatação de telefones
 * - Validações
 * - Sons do sistema
 */

// ============================================
// BUSCA DE CEP
// ============================================

const VIACEP_URL = 'https://viacep.com.br/ws';

/**
 * Busca endereço pelo CEP usando ViaCEP
 * @param {string} cep - CEP com ou sem formatação
 * @returns {Promise<Object|null>} Dados do endereço ou null se não encontrado
 */
export async function fetchAddressByCEP(cep) {
  // Limpar CEP (apenas números)
  const cleanCep = cep.replace(/\D/g, '');
  
  if (cleanCep.length !== 8) {
    return null;
  }
  
  try {
    const response = await fetch(`${VIACEP_URL}/${cleanCep}/json/`);
    const data = await response.json();
    
    if (data.erro) {
      return null;
    }
    
    return {
      cep: data.cep,
      street: data.logradouro,
      neighborhood: data.bairro,
      city: data.localidade,
      state: data.uf,
      complement: data.complemento || ''
    };
  } catch (error) {
    console.error('[CEP] Erro na busca:', error);
    return null;
  }
}

/**
 * Formata CEP para exibição (00000-000)
 */
export function formatCEP(cep) {
  const clean = cep.replace(/\D/g, '');
  if (clean.length !== 8) return cep;
  return `${clean.slice(0, 5)}-${clean.slice(5)}`;
}

// ============================================
// FORMATAÇÃO DE TELEFONE
// ============================================

/**
 * Formata telefone brasileiro
 * @param {string} phone - Telefone com ou sem formatação
 * @returns {string} Telefone formatado
 */
export function formatPhone(phone) {
  const clean = phone.replace(/\D/g, '');
  
  if (clean.length === 11) {
    // Celular: (00) 00000-0000
    return `(${clean.slice(0, 2)}) ${clean.slice(2, 7)}-${clean.slice(7)}`;
  } else if (clean.length === 10) {
    // Fixo: (00) 0000-0000
    return `(${clean.slice(0, 2)}) ${clean.slice(2, 6)}-${clean.slice(6)}`;
  } else if (clean.length === 9) {
    // Sem DDD, celular
    return `${clean.slice(0, 5)}-${clean.slice(5)}`;
  } else if (clean.length === 8) {
    // Sem DDD, fixo
    return `${clean.slice(0, 4)}-${clean.slice(4)}`;
  }
  
  return phone;
}

/**
 * Extrai apenas números do telefone
 */
export function cleanPhone(phone) {
  return phone.replace(/\D/g, '');
}

/**
 * Valida se é um telefone brasileiro válido
 */
export function isValidPhone(phone) {
  const clean = cleanPhone(phone);
  return clean.length >= 8 && clean.length <= 11;
}

// ============================================
// FORMATAÇÃO DE REDES SOCIAIS
// ============================================

/**
 * Normaliza usuário de rede social
 * Aceita @usuario ou URL completa, retorna @usuario
 */
export function normalizeSocialUsername(input, platform = 'instagram') {
  if (!input) return '';
  
  const value = input.trim();
  
  // Se já começa com @, está ok
  if (value.startsWith('@')) {
    return value;
  }
  
  // Tentar extrair de URL
  const patterns = {
    instagram: /(?:instagram\.com|instagr\.am)\/([^/?]+)/i,
    facebook: /facebook\.com\/([^/?]+)/i,
    linkedin: /linkedin\.com\/(?:in|company)\/([^/?]+)/i,
    youtube: /youtube\.com\/(?:@|channel\/|c\/)?([^/?]+)/i,
    tiktok: /tiktok\.com\/@?([^/?]+)/i,
    twitter: /(?:twitter\.com|x\.com)\/([^/?]+)/i
  };
  
  const pattern = patterns[platform];
  if (pattern) {
    const match = value.match(pattern);
    if (match && match[1]) {
      return `@${match[1]}`;
    }
  }
  
  // Se não é URL, adicionar @
  if (!value.includes('http') && !value.includes('.com')) {
    return `@${value.replace('@', '')}`;
  }
  
  return value;
}

/**
 * Gera URL da rede social a partir do username
 */
export function getSocialUrl(username, platform) {
  if (!username) return '';
  
  const clean = username.replace('@', '');
  
  const baseUrls = {
    instagram: 'https://instagram.com/',
    facebook: 'https://facebook.com/',
    linkedin: 'https://linkedin.com/in/',
    youtube: 'https://youtube.com/@',
    tiktok: 'https://tiktok.com/@',
    twitter: 'https://twitter.com/'
  };
  
  return (baseUrls[platform] || '') + clean;
}

// ============================================
// VALIDAÇÕES
// ============================================

/**
 * Valida e-mail
 */
export function isValidEmail(email) {
  const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return pattern.test(email);
}

/**
 * Valida URL
 */
export function isValidUrl(url) {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

/**
 * Valida ano (1900-2026)
 */
export function isValidYear(year) {
  const num = parseInt(year, 10);
  return !isNaN(num) && num >= 1900 && num <= new Date().getFullYear();
}

/**
 * Valida cor hex
 */
export function isValidHexColor(color) {
  return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
}

/**
 * Converte nome de cor para hex
 */
export function colorNameToHex(colorName) {
  const colors = {
    // Cores básicas
    'vermelho': '#E74C3C',
    'azul': '#3498DB',
    'verde': '#2ECC71',
    'amarelo': '#F1C40F',
    'laranja': '#E67E22',
    'roxo': '#9B59B6',
    'rosa': '#E91E63',
    'preto': '#2C3E50',
    'branco': '#ECF0F1',
    'cinza': '#95A5A6',
    'marrom': '#8B4513',
    'dourado': '#FFD700',
    'prata': '#C0C0C0',
    
    // Tons específicos
    'azul marinho': '#2C3E50',
    'azul claro': '#3498DB',
    'azul escuro': '#1A5276',
    'verde claro': '#2ECC71',
    'verde escuro': '#27AE60',
    'vermelho escuro': '#C0392B',
    'roxo escuro': '#8E44AD',
    'laranja escuro': '#D35400',
    'cinza escuro': '#7F8C8D',
    'cinza claro': '#BDC3C7'
  };
  
  const normalized = colorName.toLowerCase().trim();
  return colors[normalized] || null;
}

// ============================================
// SISTEMA DE SONS
// ============================================

class SoundManager {
  constructor() {
    this.enabled = true;
    this.volume = 0.5;
    this.sounds = {};
  }
  
  /**
   * Pré-carrega os sons
   */
  preload() {
    const soundFiles = {
      fieldComplete: '/sounds/field-complete.mp3',
      stepComplete: '/sounds/step-complete.mp3',
      achievement: '/sounds/achievement.mp3',
      levelUp: '/sounds/level-up.mp3',
      error: '/sounds/error.mp3',
      send: '/sounds/send.mp3',
      click: '/sounds/click.mp3'
    };
    
    Object.entries(soundFiles).forEach(([key, url]) => {
      const audio = new Audio();
      audio.src = url;
      audio.volume = this.volume;
      audio.preload = 'auto';
      this.sounds[key] = audio;
    });
  }
  
  /**
   * Toca um som
   */
  play(soundName) {
    if (!this.enabled) return;
    
    const sound = this.sounds[soundName];
    if (sound) {
      // Clonar para permitir sons sobrepostos
      const clone = sound.cloneNode();
      clone.volume = this.volume;
      clone.play().catch(() => {
        // Ignorar erros de autoplay
      });
    }
  }
  
  /**
   * Ativa/desativa sons
   */
  toggle(enabled = !this.enabled) {
    this.enabled = enabled;
  }
  
  /**
   * Ajusta volume (0-1)
   */
  setVolume(volume) {
    this.volume = Math.max(0, Math.min(1, volume));
    Object.values(this.sounds).forEach(audio => {
      audio.volume = this.volume;
    });
  }
}

export const soundManager = new SoundManager();

// ============================================
// DEBOUNCE / THROTTLE
// ============================================

/**
 * Debounce function
 */
export function debounce(fn, delay = 300) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

/**
 * Throttle function
 */
export function throttle(fn, limit = 300) {
  let inThrottle;
  return function (...args) {
    if (!inThrottle) {
      fn.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

// ============================================
// TEXT HELPERS
// ============================================

/**
 * Trunca texto com ellipsis
 */
export function truncate(text, maxLength = 100) {
  if (!text || text.length <= maxLength) return text;
  return text.substring(0, maxLength - 3) + '...';
}

/**
 * Capitaliza primeira letra
 */
export function capitalize(text) {
  if (!text) return '';
  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
}

/**
 * Remove acentos
 */
export function removeAccents(text) {
  return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

// ============================================
// EXPORTS DEFAULT
// ============================================

export default {
  fetchAddressByCEP,
  formatCEP,
  formatPhone,
  cleanPhone,
  isValidPhone,
  normalizeSocialUsername,
  getSocialUrl,
  isValidEmail,
  isValidUrl,
  isValidYear,
  isValidHexColor,
  colorNameToHex,
  soundManager,
  debounce,
  throttle,
  truncate,
  capitalize,
  removeAccents
};
