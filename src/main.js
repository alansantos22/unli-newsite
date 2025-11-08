import * as Vue from 'vue';
import * as VueRouter from 'vue-router';

// Font Awesome Icons
import '@fortawesome/fontawesome-free/css/all.css';

import App from './App.vue'
import routes from './router';

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

app.use(router);
app.use(store);

app.mount('#app');