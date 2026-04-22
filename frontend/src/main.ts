import { createApp } from 'vue'
import { createPinia } from 'pinia'
import * as LucideIcons from 'lucide-vue-next'
import './style.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)

Object.keys(LucideIcons).forEach((key) => {
  if (key.startsWith('Lucide')) {
    const iconName = key.replace('Lucide', '')
    app.component(iconName, (LucideIcons as any)[key])
  }
})

app.directive('lucide', {
  mounted(el, binding) {
    const iconName = binding.value
    if (iconName && (LucideIcons as any)[iconName]) {
      const icon = (LucideIcons as any)[iconName]
      el.innerHTML = ''
      el.appendChild(icon.render())
    }
  }
})

app.use(createPinia())
app.use(router)
app.mount('#app')
