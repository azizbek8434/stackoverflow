require('./bootstrap');

require('./fontawesome')

window.Vue = require('vue');

import VueIziToast from 'vue-izitoast';

import 'izitoast/dist/css/iziToast.min.css';

import Authorization from './authorization/authorize';

Vue.use(VueIziToast);
Vue.use(Authorization);

Vue.component('user-info', require('./components/UserInfo.vue').default);
Vue.component('answer-component', require('./components/Answer.vue').default);
Vue.component('vote-component', require('./components/Vote.vue').default);

const app = new Vue({
    el: '#app'
});
