import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { createPinia } from 'pinia'
// import 'ant-design-vue/dist/antd.css';


// Import Ant Design Vue & CSS
import Antd from 'ant-design-vue'
import 'ant-design-vue/dist/reset.css' // dùng reset.css cho Vue 3



const app = createApp(App)
app.use(router)
app.use(createPinia())
app.use(Antd)
app.mount('#app')
