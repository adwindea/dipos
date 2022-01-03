import 'core-js/stable'
import Vue from 'vue'
import App from './App'
import router from './router'
import CoreuiVue from '@coreui/vue'
import { iconsSet as icons } from './assets/icons/icons.js'
import store from './store'
// import VueHtmlToPaper from 'vue-html-to-paper';

let subdomain = location.hostname.split('.').shift() + '.'; //remove when subdomain disabled
Vue.prototype.$apiAdress = 'https://'+subdomain+'dipos.sekaradi.id' //for enabled subdomain

//Vue.prototype.$apiAdress = 'https://dipos.sekaradi.id'//for disabled subdomain

Vue.config.performance = true
Vue.use(CoreuiVue)
// Vue.use(VueHtmlToPaper);


new Vue({
  el: '#app',
  router,
  store,
  icons,
  template: '<App/>',
  components: {
    App
  },
})
