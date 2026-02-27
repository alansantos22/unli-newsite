/**
 * Testes Unitários - CookieHelper
 * 
 * Testa as funções de manipulação de cookies
 */

const CookieHelper = require('@/core/helpers/CookieHelper.js');

describe('CookieHelper', () => {
  beforeEach(() => {
    // Limpar todos os cookies antes de cada teste
    document.cookie.split(';').forEach(c => {
      document.cookie = c.trim().split('=')[0] + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
    });
  });

  describe('setCookie', () => {
    it('deve criar um cookie com o valor especificado', () => {
      CookieHelper.setCookie('testCookie', 'testValue', 1);
      
      expect(document.cookie).toContain('testCookie=testValue');
    });

    it('deve criar cookie com expiração', () => {
      CookieHelper.setCookie('expireCookie', 'value', 7);
      
      // O cookie deve existir
      expect(document.cookie).toContain('expireCookie=value');
    });

    it('deve sobrescrever cookie existente', () => {
      CookieHelper.setCookie('overwrite', 'original', 1);
      CookieHelper.setCookie('overwrite', 'updated', 1);
      
      expect(CookieHelper.getCookie('overwrite')).toBe('updated');
    });
  });

  describe('getCookie', () => {
    it('deve retornar valor do cookie existente', () => {
      document.cookie = 'myCookie=myValue';
      
      const result = CookieHelper.getCookie('myCookie');
      
      expect(result).toBe('myValue');
    });

    it('deve retornar string vazia para cookie inexistente', () => {
      const result = CookieHelper.getCookie('nonExistentCookie');
      
      expect(result).toBe('');
    });

    it('deve retornar cookie correto quando há múltiplos cookies', () => {
      document.cookie = 'first=1';
      document.cookie = 'second=2';
      document.cookie = 'third=3';
      
      expect(CookieHelper.getCookie('second')).toBe('2');
    });

    it('deve lidar com valores vazios', () => {
      document.cookie = 'emptyCookie=';
      
      const result = CookieHelper.getCookie('emptyCookie');
      
      expect(result).toBe('');
    });

    it('deve lidar com espaços antes do nome do cookie', () => {
      // Cookies separados por ponto-e-vírgula podem ter espaços
      document.cookie = 'spacedCookie=spaceValue';
      
      const result = CookieHelper.getCookie('spacedCookie');
      
      expect(result).toBe('spaceValue');
    });
  });

  describe('deleteAllCookies', () => {
    it('deve deletar todos os cookies', () => {
      document.cookie = 'cookie1=value1';
      document.cookie = 'cookie2=value2';
      document.cookie = 'cookie3=value3';
      
      CookieHelper.deleteAllCookies();
      
      // Verificar que os cookies foram deletados
      expect(CookieHelper.getCookie('cookie1')).toBe('');
      expect(CookieHelper.getCookie('cookie2')).toBe('');
      expect(CookieHelper.getCookie('cookie3')).toBe('');
    });

    it('deve funcionar quando não há cookies', () => {
      // Não deve lançar erro
      expect(() => CookieHelper.deleteAllCookies()).not.toThrow();
    });
  });

  describe('Integração', () => {
    it('deve permitir ciclo completo: set, get, delete', () => {
      // Criar
      CookieHelper.setCookie('lifecycle', 'testValue', 1);
      expect(CookieHelper.getCookie('lifecycle')).toBe('testValue');
      
      // Atualizar
      CookieHelper.setCookie('lifecycle', 'newValue', 1);
      expect(CookieHelper.getCookie('lifecycle')).toBe('newValue');
      
      // Deletar
      CookieHelper.deleteAllCookies();
      expect(CookieHelper.getCookie('lifecycle')).toBe('');
    });
  });
});
