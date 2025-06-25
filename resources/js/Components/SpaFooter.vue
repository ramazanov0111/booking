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
                            г. {{ contacts.location }}, {{ contacts.address }}
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-blue-400"></i>
                            <a href="tel:{{ contacts.phone }}">{{ contacts.phone }}</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-blue-400"></i>
                            <a href="mailto:{{ contacts.email }}">{{ contacts.email }}</a>
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

<script setup>

import NavLink from '@/Components/NavLink.vue';
import {onMounted, ref} from "vue";
import {route} from "ziggy-js";

const loading = ref(true)
const contacts = ref([])

const loadContacts = async () => {
    try {
        loading.value = true

        const {data} = await axios.get(route('contacts'))

        contacts.value = {
            address: data.find(contact => contact.key === 'Адрес')?.value ?? "ул. Примерная, д. 11",
            phone: data.find(contact => contact.key === 'Телефон')?.value ?? "+7 (999) 123-45-67",
            email: data.find(contact => contact.key === 'Email')?.value ?? "contact@guesthouse.ru",
            location: data.find(contact => contact.key === 'Город')?.value ?? "Астрахань",
        }
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await loadContacts()
})

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
