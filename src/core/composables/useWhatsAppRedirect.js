/**
 * useWhatsAppRedirect - Composable para gerar link do WhatsApp com mensagem personalizada
 * 
 * Usado quando VUE_APP_MANUAL_MODE=true para redirecionar o cliente
 * ao WhatsApp do SDR ao invés do checkout de pagamento.
 */

const WHATSAPP_NUMBER = process.env.VUE_APP_WHATSAPP_SDR || '5511911019666';

/**
 * Formata valor em Reais
 */
function formatBRL(value) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

/**
 * Mapa de nomes legíveis para páginas
 */
const PAGE_NAMES = {
  about: 'Sobre',
  services: 'Serviços',
  portfolio: 'Portfolio',
  faq: 'FAQ',
  contact: 'Contato',
  blog: 'Blog',
  showcase: 'Vitrine de Produtos'
};

/**
 * Gera a mensagem personalizada para o WhatsApp
 * 
 * @param {Object} options
 * @param {string} options.packageName - Nome do pacote (ex: "Essencial", "Autoridade")
 * @param {string[]} options.pages - Lista de páginas selecionadas
 * @param {number} options.priceAvista - Preço à vista
 * @param {number} options.priceParcelado - Preço parcelado total
 * @param {number} options.parcela12 - Valor da parcela 12x
 * @param {string} [options.customerName] - Nome do cliente (se já preencheu)
 * @returns {string} Mensagem formatada
 */
function buildMessage({ packageName, pages, priceAvista, parcela12, customerName }) {
  const pagesList = (pages || [])
    .map(p => PAGE_NAMES[p] || p)
    .join(', ');

  let msg = `Olá! 👋 Acabei de montar meu site na Unli!\n\n`;
  
  if (customerName) {
    msg += `👤 *Meu nome:* ${customerName}\n`;
  }
  
  msg += `📦 *Pacote:* ${packageName || 'Personalizado'}\n`;
  
  if (pagesList) {
    msg += `📄 *Páginas:* ${pagesList}\n`;
  }
  
  if (priceAvista) {
    msg += `💰 *Valor à vista:* ${formatBRL(priceAvista)}\n`;
  }
  
  if (parcela12) {
    msg += `💳 *Ou 12x de:* ${formatBRL(parcela12)}\n`;
  }
  
  msg += `\nGostaria de finalizar minha compra!`;
  
  return msg;
}

/**
 * Gera o link completo do WhatsApp
 */
function buildWhatsAppLink(options) {
  const message = buildMessage(options);
  return `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`;
}

/**
 * Abre o WhatsApp em nova aba
 */
function redirectToWhatsApp(options) {
  const url = buildWhatsAppLink(options);
  window.open(url, '_blank', 'noopener,noreferrer');
}

/**
 * Verifica se o modo manual está ativo
 */
function isManualMode() {
  return process.env.VUE_APP_MANUAL_MODE === 'true';
}

export function useWhatsAppRedirect() {
  return {
    isManualMode,
    buildMessage,
    buildWhatsAppLink,
    redirectToWhatsApp,
    WHATSAPP_NUMBER
  };
}
