/**
 * ============================================
 * AI RATE LIMITER
 * Gerenciamento de limite de uso da IA no frontend
 * ============================================
 * 
 * Limite: 10 requisições por dia por usuário
 * Armazenamento: localStorage com reset à meia-noite
 */

const STORAGE_KEY = 'ai_usage_data';
const DAILY_LIMIT = 10;

/**
 * Obtém os dados de uso do localStorage
 */
function getUsageData() {
  try {
    const data = localStorage.getItem(STORAGE_KEY);
    if (!data) return null;
    return JSON.parse(data);
  } catch {
    return null;
  }
}

/**
 * Salva os dados de uso no localStorage
 */
function saveUsageData(data) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
}

/**
 * Obtém a data de hoje no formato YYYY-MM-DD
 */
function getTodayKey() {
  return new Date().toISOString().split('T')[0];
}

/**
 * Verifica se o usuário ainda pode usar a IA
 * @returns {Object} { canUse: boolean, remaining: number, resetTime: string }
 */
export function checkAILimit() {
  const today = getTodayKey();
  const data = getUsageData();
  
  // Se não há dados ou é um novo dia, reseta
  if (!data || data.date !== today) {
    return {
      canUse: true,
      remaining: DAILY_LIMIT,
      used: 0,
      resetTime: getNextResetTime()
    };
  }
  
  const remaining = DAILY_LIMIT - data.count;
  
  return {
    canUse: remaining > 0,
    remaining: Math.max(0, remaining),
    used: data.count,
    resetTime: getNextResetTime()
  };
}

/**
 * Registra uma nova utilização da IA
 * @returns {Object} Status atualizado
 */
export function recordAIUsage() {
  const today = getTodayKey();
  let data = getUsageData();
  
  // Novo dia ou primeiro uso
  if (!data || data.date !== today) {
    data = {
      date: today,
      count: 1,
      history: [{ time: new Date().toISOString(), type: 'generation' }]
    };
  } else {
    data.count++;
    data.history.push({ time: new Date().toISOString(), type: 'generation' });
  }
  
  saveUsageData(data);
  
  return checkAILimit();
}

/**
 * Calcula o tempo até o próximo reset (meia-noite)
 */
function getNextResetTime() {
  const now = new Date();
  const tomorrow = new Date(now);
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);
  
  const diff = tomorrow - now;
  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  
  return `${hours}h ${minutes}min`;
}

/**
 * Obtém estatísticas de uso
 */
export function getUsageStats() {
  const data = getUsageData();
  const today = getTodayKey();
  
  if (!data || data.date !== today) {
    return {
      todayCount: 0,
      history: [],
      limit: DAILY_LIMIT
    };
  }
  
  return {
    todayCount: data.count,
    history: data.history || [],
    limit: DAILY_LIMIT
  };
}

/**
 * Reseta o limite (para debug/admin)
 */
export function resetAILimit() {
  localStorage.removeItem(STORAGE_KEY);
}

export default {
  checkAILimit,
  recordAIUsage,
  getUsageStats,
  resetAILimit,
  DAILY_LIMIT
};
