import { ref, computed } from 'vue';

const TOKEN_KEY = 'sdr_token';
const sdrUser = ref(null);
const isLoading = ref(false);

/**
 * useSDRAuth - Composable para autenticação do painel SDR
 */
export function useSDRAuth() {
  const isAuthenticated = computed(() => !!sdrUser.value);

  function getToken() {
    return sessionStorage.getItem(TOKEN_KEY);
  }

  function setToken(token) {
    sessionStorage.setItem(TOKEN_KEY, token);
  }

  function clearToken() {
    sessionStorage.removeItem(TOKEN_KEY);
    sdrUser.value = null;
  }

  /**
   * Login do SDR
   */
  async function login(email, password) {
    isLoading.value = true;
    try {
      const res = await fetch('/api/sdr/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (data.ok) {
        setToken(data.token);
        sdrUser.value = data.user;
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
   * Valida o token existente e recupera dados do SDR
   */
  async function validate() {
    const token = getToken();
    if (!token) return false;

    isLoading.value = true;
    try {
      const res = await fetch('/api/sdr/auth.php?action=validate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await res.json();
      if (data.ok) {
        sdrUser.value = data.user;
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
    sdrUser,
    isAuthenticated,
    isLoading,
    login,
    validate,
    logout,
    getToken,
    authHeaders
  };
}
