import './bootstrap';
import { createApp } from 'vue';

const app = createApp({
    data() {
        return {
            // Data properties
        }
    },
    // Diğer Vue ayarları
});

// Global bileşenler
app.component('package-card', require('./components/PackageCard.vue').default);

app.mount('#EsimApp');