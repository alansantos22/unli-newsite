const MEASUREMENT_ID = process.env.VUE_APP_GA_ID;
const DEBUG = process.env.NODE_ENV !== 'production';

export const config = {
  property: {
    id: MEASUREMENT_ID
  },
  isEnabled: !!MEASUREMENT_ID,
  bootstrap: true,
  params: {
    send_page_view: false,
    debug_mode: DEBUG
  }
};

export const pageView = (pageName, path) => {
  if (!MEASUREMENT_ID || typeof window.gtag !== 'function') return;
  window.gtag('event', 'page_view', {
    page_title: pageName,
    page_path: path,
    page_location: window.location.href
  });
};

export const trackEvent = (eventName, params = {}) => {
  if (!MEASUREMENT_ID || typeof window.gtag !== 'function') return;
  window.gtag('event', eventName, params);
};

// Meta Pixel helpers
export const fbPageView = () => {
  if (typeof window.fbq !== 'function') return;
  window.fbq('track', 'PageView');
};

export const fbTrackEvent = (eventName, params = {}) => {
  if (typeof window.fbq !== 'function') return;
  window.fbq('track', eventName, params);
};

export const trackContact = (method = 'whatsapp') => {
  trackEvent('contact', { method });
};

export const trackPurchase = (purchase) => {
  if (!purchase?.items?.length || !MEASUREMENT_ID) return;
  try {
    window.gtag('event', 'purchase', {
      currency: 'BRL',
      transaction_id: purchase.orderId || `T${Date.now()}`,
      value: purchase.total || 0,
      items: purchase.items.map(item => ({
        item_id: item.id || '',
        item_name: item.name || '',
        price: item.price || 0,
        quantity: item.quantity || 1
      }))
    });
  } catch (error) {
    console.error('[Analytics] Error tracking purchase:', error);
  }
};
