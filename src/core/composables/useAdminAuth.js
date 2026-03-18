import { ref, computed } from 'vue';

const TOKEN_KEY = 'admin_token';
const adminUser = ref(null);
const isLoading = ref(false);

/**
 * useAdminAuth - Composable para autenticação do painel Super Admin
 */
export function useAdminAuth() {
  const isAuthenticated = computed(() => !!adminUser.value);
  const isSuperAdmin = computed(() => adminUser.value?.role === 'super_admin');
  const isManager = computed(() => ['super_admin', 'manager'].includes(adminUser.value?.role));

  function getToken() {
    return sessionStorage.getItem(TOKEN_KEY);
  }

  function setToken(token) {
    sessionStorage.setItem(TOKEN_KEY, token);
  }

  function clearToken() {
    sessionStorage.removeItem(TOKEN_KEY);
    adminUser.value = null;
  }

  /**
   * Login do admin
   */
  async function login(email, password) {
    isLoading.value = true;
    try {
      const res = await fetch('/api/admin/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (data.ok) {
        setToken(data.token);
        adminUser.value = data.user;
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
   * Valida o token existente e recupera dados do admin
   */
  async function validate() {
    const token = getToken();
    if (!token) return false;

    isLoading.value = true;
    try {
      const res = await fetch('/api/admin/auth.php?action=validate', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await res.json();
      if (data.ok) {
        adminUser.value = data.user;
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

  /**
   * Helper para chamadas à API admin
   */
  async function adminFetch(url, options = {}) {
    const res = await fetch(url, {
      ...options,
      headers: { ...authHeaders(), ...(options.headers || {}) }
    });
    const data = await res.json();
    if (res.status === 401) {
      clearToken();
      window.location.href = '/admin';
    }
    return data;
  }

  return {
    adminUser,
    isAuthenticated,
    isSuperAdmin,
    isManager,
    isLoading,
    login,
    validate,
    logout,
    authHeaders,
    adminFetch
  };
}
