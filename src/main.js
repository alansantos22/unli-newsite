import * as Vue from 'vue';
import * as VueRouter from 'vue-router';

import App from './App.vue'
import routes from './router';

//Stores
import store from './core/store/store'

const app = Vue.createApp(App);

const router = VueRouter.createRouter({
    history: VueRouter.createWebHistory(),
    routes,
});

app.use(router);
app.use(store);

app.mount('#app');