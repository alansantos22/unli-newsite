/**
 * CONFIG MODULE - Configurações globais do sistema
 * 
 * DEBUG MODE:
 * - true: Força modo local (não chama API PHP)
 * - false: Tenta usar API normalmente
 * 
 * IMPORTANTE: Em produção (npm run build), debug é automaticamente false
 */

const ConfigModule = {
  namespaced: true,
  
  state: {
    // ⚠️ Desenvolvimento: true (não chama PHP)
    // ✅ Produção: false (usa API PHP)
    debug: process.env.NODE_ENV !== 'production',
    
    // URL base da API
    apiBaseUrl: process.env.VUE_APP_API_URL || '/api',
    
    // Timeout para requisições (ms)
    apiTimeout: 5000,
    
    // Outras configurações
    appVersion: '1.0.0',
    environment: process.env.NODE_ENV
  },
  
  getters: {
    isDebugMode: state => state.debug,
    isProduction: state => state.environment === 'production',
    isDevelopment: state => state.environment === 'development',
    apiUrl: state => state.apiBaseUrl,
    config: state => state
  },
  
  mutations: {
    SET_DEBUG(state, value) {
      console.warn(`🔧 Debug mode: ${value ? 'ON (modo local)' : 'OFF (usa API)'}`);
      state.debug = value;
    },
    
    SET_API_URL(state, url) {
      state.apiBaseUrl = url;
    }
  },
  
  actions: {
    toggleDebug({ commit, state }) {
      commit('SET_DEBUG', !state.debug);
    },
    
    enableDebug({ commit }) {
      commit('SET_DEBUG', true);
    },
    
    disableDebug({ commit }) {
      commit('SET_DEBUG', false);
    },
    
    setApiUrl({ commit }, url) {
      commit('SET_API_URL', url);
    }
  }
};

export default ConfigModule;
