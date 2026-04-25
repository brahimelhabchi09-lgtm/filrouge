import { createApp } from 'vue'
import { createPinia } from 'pinia'
import * as LucideIcons from 'lucide-vue-next'
import './style.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)

Object.keys(LucideIcons).forEach((key) => {
  const icon = (LucideIcons as any)[key]
  if (icon && typeof icon === 'object' && icon.render) {
    app.component(key, icon)
  }
})

app.directive('lucide', {
  mounted(el, binding) {
    const iconName = binding.value
    const icon = (LucideIcons as any)[iconName]
    if (icon) {
      el.innerHTML = ''
      el.appendChild(icon.render())
    }
  }
})

app.use(createPinia())
app.use(router)
app.mount('#app')
