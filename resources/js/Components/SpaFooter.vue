<template>
    <footer class="bg-gray-800 text-white py-12 mt-20">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                <!-- Контакты -->
                <div class="footer-section">
                    <h3 class="footer-title">Контакты</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-3 text-blue-400"></i>
                            г. Санкт-Петербург, ул. Лесная, 15
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-blue-400"></i>
                            <a href="tel:+78121234567">+7 (812) 123-45-67</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-blue-400"></i>
                            <a href="mailto:info@foresthouse.ru">info@foresthouse.ru</a>
                        </li>
                    </ul>
                </div>

                <!-- Навигация -->
                <div class="footer-section">
                    <h3 class="footer-title">Навигация</h3>
                    <nav class="space-y-2">
                        <NavLink :href="route('main')" :active="route().current('main')" class="nfooter-link">
                            Главная
                        </NavLink>
                        <NavLink :href="route('rooms')" :active="route().current('rooms')" class="footer-link">
                            Номера
                        </NavLink>
                        <NavLink v-if="$page.props.auth.user" :href="route('booking', $page.props.auth.user.id)" :active="route().current('booking')" class="footer-link">
                            Мои бронирования
                        </NavLink>
                    </nav>
                </div>

                <!-- Соцсети -->
                <div class="footer-section">
                    <h3 class="footer-title">Мы в соцсетях</h3>
                    <div class="flex space-x-4">
                        <a
                            v-for="social in socials"
                            :key="social.name"
                            :href="social.link"
                            target="_blank"
                            class="social-icon"
                        >
                            <i :class="social.icon"></i>
                        </a>
                    </div>
                </div>

                <!-- Рассылка -->
                <div class="footer-section">
                    <h3 class="footer-title">Подписка</h3>
                    <p class="mb-4 text-gray-400">Узнавайте первыми о спецпредложениях!</p>
                    <form @submit.prevent="subscribe" class="flex gap-2">
                        <input
                            type="email"
                            v-model="email"
                            placeholder="Ваш email"
                            class="flex-1 px-4 py-2 rounded-lg text-gray-800"
                            required
                        >
                        <button
                            type="submit"
                            class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Копирайт -->
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; 2024 Forest House. Все права защищены</p>
                <div class="mt-2">
                    <NavLink to="/privacy" class="hover:text-white transition">Политика конфиденциальности</NavLink>
                    <span class="mx-2">|</span>
                    <NavLink to="/terms" class="hover:text-white transition">Пользовательское соглашение</NavLink>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>

import { Head, Link, router } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
export default {
    name: 'SpaFooter',
    components: {
        Link,
        NavLink,
        ResponsiveNavLink
    },
    data() {
        return {
            email: '',
            navItems: [
                { title: 'Главная', path: '/' },
                { title: 'Номера', path: '/rooms' },
                { title: 'Мои бронирования', path: '/booking' }
            ],
            socials: [
                { name: 'VK', icon: 'fab fa-vk', link: '#' },
                { name: 'Telegram', icon: 'fab fa-telegram-plane', link: '#' },
                { name: 'Instagram', icon: 'fab fa-instagram', link: '#' }
            ]
        }
    },
    methods: {
        subscribe() {
            // Логика подписки
            console.log('Subscribed:', this.email)
            this.email = ''
        }
    }
}
</script>

<style scoped>
.footer-section {
    @apply mb-8 md:mb-0;
}

.footer-title {
    @apply text-xl font-semibold mb-4 border-b-2 border-blue-600 pb-2 inline-block;
}

.footer-link {
    @apply block hover:text-blue-400 transition-colors;
}

.social-icon {
    @apply text-2xl p-2 hover:text-blue-400 transition-colors;
}

.router-link-active {
    @apply text-blue-400 font-medium;
}
</style>
