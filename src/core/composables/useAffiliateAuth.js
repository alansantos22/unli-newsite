import { ref, computed } from 'vue';

const TOKEN_KEY = 'affiliate_token';
const affiliateUser = ref(null);
const isLoading = ref(false);

/**
 * useAffiliateAuth - Composable para autenticação do painel de Afiliados
 */
export function useAffiliateAuth() {
  const isAuthenticated = computed(() => !!affiliateUser.value);

  function getToken() {
    return sessionStorage.getItem(TOKEN_KEY);
  }

  function setToken(token) {
    sessionStorage.setItem(TOKEN_KEY, token);
  }

  function clearToken() {
    sessionStorage.removeItem(TOKEN_KEY);
    affiliateUser.value = null;
  }

  /**
   * Registrar novo afiliado
   */
  async function register(data) {
    isLoading.value = true;
    try {
      const res = await fetch('/api/affiliate/auth.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const result = await res.json();
      if (result.ok) {
        setToken(result.token);
        affiliateUser.value = result.user;
        return { ok: true, user: result.user };
      }
      return { ok: false, error: result.error || result.errors?.join(', ') || 'Erro no cadastro' };
    } catch (e) {
      return { ok: false, error: 'Erro de conexão com o servidor' };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Login do afiliado
   */
  async function login(email, password) {
    isLoading.value = true;
    try {
      const res = await fetch('/api/affiliate/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (data.ok) {
        setToken(data.token);
        affiliateUser.value = data.user;
        return { ok: true };
      }
      return { ok: false, error: data.error || 'Credenciais inválidas' };
    } catch (e) {
      return { ok: false, error: 'Erro de conexão com o servidor' };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Valida o token existente e recupera dados do afiliado
   */
  async function validate() {
    const token = getToken();
    if (!token) return false;

    isLoading.value = true;
    try {
      const res = await fetch('/api/affiliate/auth.php?action=validate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await res.json();
      if (data.ok) {
        affiliateUser.value = data.user;
        return true;
      }
      clearToken();
      return false;
    } catch {
      clearToken();
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  function logout() {
    clearToken();
  }

  /**
   * Retorna headers com Authorization para chamadas autenticadas
   */
  function authHeaders() {
    return {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${getToken()}`
    };
  }

  return {
    affiliateUser,
    isAuthenticated,
    isLoading,
    login,
    register,
    validate,
    logout,
    authHeaders,
    getToken
  };
}
