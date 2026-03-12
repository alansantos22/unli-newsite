/**
 * useAffiliateTracking - Composable para tracking de afiliados via cookie
 * 
 * - Salva o hash do afiliado (param ?ref=xxx) em cookie de 30 dias
 * - Recupera o hash quando necessário (WhatsApp, formulários, etc)
 */
export function useAffiliateTracking() {

  const COOKIE_NAME = 'unli_aff';
  const COOKIE_DAYS = 30;

  /**
   * Salva o hash do afiliado em cookie
   */
  function saveAffiliateRef(hash) {
    if (!hash || !/^[a-f0-9]{16}$/.test(hash)) return;
    
    const expires = new Date();
    expires.setDate(expires.getDate() + COOKIE_DAYS);
    document.cookie = `${COOKIE_NAME}=${hash}; expires=${expires.toUTCString()}; path=/; SameSite=Lax; Secure`;
  }

  /**
   * Recupera o hash do afiliado do cookie
   */
  function getAffiliateRef() {
    const match = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([a-f0-9]{16})'));
    return match ? match[1] : null;
  }

  /**
   * Remove o cookie de afiliado
   */
  function clearAffiliateRef() {
    document.cookie = `${COOKIE_NAME}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
  }

  /**
   * Detecta param ?ref= na URL e salva no cookie
   * Chamar no mounted/created do App ou nas páginas de destino
   */
  function detectAndSave() {
    const params = new URLSearchParams(window.location.search);
    const ref = params.get('ref');
    if (ref) {
      saveAffiliateRef(ref);
    }
  }

  /**
   * Retorna a mensagem de afiliado para WhatsApp
   */
  function getWhatsAppSuffix() {
    const ref = getAffiliateRef();
    if (ref) {
      return `\n\n📎 Indicado pelo afiliado: ${ref}`;
    }
    return '';
  }

  return {
    saveAffiliateRef,
    getAffiliateRef,
    clearAffiliateRef,
    detectAndSave,
    getWhatsAppSuffix
  };
}
