<template>
    <Head :title="title" />
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Шапка -->
            <SpaHeader/>

            <!-- Основной контент -->
            <main class="flex-1">
                <slot/> <!-- Сюда будет вставляться контент конкретной страницы -->
            </main>

            <!-- Подвал -->
            <SpaFooter/>
        </div>
    </div>
</template>

<script setup>
import SpaHeader from '@/Components/SpaHeader.vue'
import SpaFooter from '@/Components/SpaFooter.vue'
import 'flatpickr/dist/flatpickr.min.css';
import {Head} from "@inertiajs/vue3";

defineProps({
    title: String,
});

</script>

<style>
/* Глобальные стили */
body {
    @apply bg-gray-50;
}

/* Анимации */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}

/* Кастомные классы */
.container {
    @apply mx-auto px-4 max-w-7xl;
}

.page-title {
    @apply text-3xl md:text-4xl font-bold text-gray-800 mb-8 text-center;
}
</style>
