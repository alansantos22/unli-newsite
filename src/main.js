import * as Vue from 'vue';
import * as VueRouter from 'vue-router';
import VueGtag from 'vue-gtag-next';

// Font Awesome Icons
import '@fortawesome/fontawesome-free/css/all.css';

import App from './App.vue'
import routes from './router';
import { config, pageView } from './plugins/analytics';

//Stores
import store from './core/store/store'

const app = Vue.createApp(App);

const router = VueRouter.createRouter({
    history: VueRouter.createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Se houver posição salva (botão voltar), usar ela
        if (savedPosition) {
            return savedPosition;
        }
        // Se for navegação para hash (#section), scroll suave
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
            };
        }
        // Padrão: sempre voltar ao topo
        return { top: 0, behavior: 'smooth' };
    },
});

app.use(VueGtag, config);
app.use(router);
app.use(store);

// SDR Panel auth guard
router.beforeEach((to, from, next) => {
    if (to.meta.requiresSDR) {
        const token = sessionStorage.getItem('sdr_token');
        if (!token) {
            return next({ name: 'SDRLogin' });
        }
    }
    if (to.meta.requiresAffiliate) {
        const token = sessionStorage.getItem('affiliate_token');
        if (!token) {
            return next({ name: 'AffiliateLogin' });
        }
    }
    next();
});

// SPA page view tracking — Google Analytics + Microsoft Clarity
router.afterEach((to) => {
    // Google Analytics
    pageView(to.name || to.path, to.fullPath);

    // Microsoft Clarity
    if (typeof window.clarity === 'function') {
        window.clarity('set', 'pageUrl', to.fullPath);
    }
});

app.mount('#app');