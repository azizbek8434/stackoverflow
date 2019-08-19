require('./bootstrap');

require('./fontawesome')

window.Vue = require('vue');

import VueIziToast from 'vue-izitoast';

import 'izitoast/dist/css/iziToast.min.css';

import policies from './policies'

Vue.prototype.authorize = function (policy, model) {
    if (!window.Auth.signedIn) return false;
    if (typeof policy === 'string' && typeof model === 'object') {
        const user = window.Auth.user;
        return policies[policy](user, model);
    }
}

Vue.use(VueIziToast);

Vue.component('user-info', require('./components/UserInfo.vue').default);
Vue.component('answer-component', require('./components/Answer.vue').default);
Vue.component('favorite-component', require('./components/Favorite.vue').default);
Vue.component('accept-component', require('./components/Accept.vue').default);

const app = new Vue({
    el: '#app',
});
