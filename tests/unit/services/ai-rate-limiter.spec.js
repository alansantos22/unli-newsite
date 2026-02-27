/**
 * Testes Unitários - AI Rate Limiter
 * 
 * Testa o sistema de limitação de uso da IA no frontend
 */

import { checkAILimit, recordAIUsage } from '@/core/services/ai-rate-limiter.js';

describe('AI Rate Limiter', () => {
  beforeEach(() => {
    localStorage.clear();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.useRealTimers();
  });

  describe('checkAILimit', () => {
    it('deve retornar limite completo quando não há uso', () => {
      const result = checkAILimit();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(10);
      expect(result.used).toBe(0);
    });

    it('deve retornar dados corretos após uso', () => {
      recordAIUsage();
      recordAIUsage();
      
      const result = checkAILimit();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(8);
      expect(result.used).toBe(2);
    });

    it('deve bloquear após atingir limite diário', () => {
      // Usa 10 vezes
      for (let i = 0; i < 10; i++) {
        recordAIUsage();
      }
      
      const result = checkAILimit();
      
      expect(result.canUse).toBe(false);
      expect(result.remaining).toBe(0);
      expect(result.used).toBe(10);
    });

    it('deve resetar no novo dia', () => {
      // Simular uso ontem
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      
      localStorage.setItem('ai_usage_data', JSON.stringify({
        date: yesterday.toISOString().split('T')[0],
        count: 10,
        history: []
      }));
      
      const result = checkAILimit();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(10);
    });

    it('deve incluir tempo de reset', () => {
      const result = checkAILimit();
      
      expect(result.resetTime).toBeDefined();
      expect(typeof result.resetTime).toBe('string');
    });
  });

  describe('recordAIUsage', () => {
    it('deve incrementar contagem de uso', () => {
      const result1 = recordAIUsage();
      expect(result1.used).toBe(1);
      
      const result2 = recordAIUsage();
      expect(result2.used).toBe(2);
    });

    it('deve retornar status atualizado', () => {
      const result = recordAIUsage();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(9);
      expect(result.used).toBe(1);
    });

    it('deve indicar quando limite é atingido', () => {
      for (let i = 0; i < 9; i++) {
        recordAIUsage();
      }
      
      const lastAllowed = recordAIUsage();
      expect(lastAllowed.canUse).toBe(false);
      expect(lastAllowed.remaining).toBe(0);
    });

    it('deve persistir dados no localStorage', () => {
      recordAIUsage();
      
      const stored = localStorage.getItem('ai_usage_data');
      expect(stored).not.toBeNull();
      
      const data = JSON.parse(stored);
      expect(data.count).toBe(1);
      expect(data.history).toHaveLength(1);
    });

    it('deve registrar histórico de uso', () => {
      recordAIUsage();
      recordAIUsage();
      
      const stored = JSON.parse(localStorage.getItem('ai_usage_data'));
      
      expect(stored.history).toHaveLength(2);
      expect(stored.history[0].type).toBe('generation');
      expect(stored.history[0].time).toBeDefined();
    });

    it('deve iniciar nova contagem em novo dia', () => {
      // Simular uso de ontem
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      
      localStorage.setItem('ai_usage_data', JSON.stringify({
        date: yesterday.toISOString().split('T')[0],
        count: 5,
        history: []
      }));
      
      const result = recordAIUsage();
      
      expect(result.used).toBe(1);
      expect(result.remaining).toBe(9);
    });
  });

  describe('Integração localStorage', () => {
    it('deve lidar com localStorage corrompido', () => {
      localStorage.setItem('ai_usage_data', 'invalid json');
      
      const result = checkAILimit();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(10);
    });

    it('deve lidar com dados ausentes graciosamente', () => {
      localStorage.removeItem('ai_usage_data');
      
      const result = checkAILimit();
      
      expect(result.canUse).toBe(true);
      expect(result.remaining).toBe(10);
    });
  });
});
