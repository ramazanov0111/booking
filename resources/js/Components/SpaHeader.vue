<template>
    <header class="site-header bg-white shadow-sm">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <!-- Логотип -->
                <Link :href="route('main')" class="flex items-center space-x-2">
                    <ApplicationLogo class="block h-12 w-auto" />
                </Link>

                <!-- Навигация для десктопа -->
                <div class="hidden md:flex items-center space-x-8">
                    <NavLink :href="route('main')" :active="route().current('main')" class="nav-link"
                             active-class="text-blue-600 font-semibold">
                        Главная
                    </NavLink>
                    <NavLink :href="route('rooms')" :active="route().current('rooms')" class="nav-link"
                             active-class="text-blue-600 font-semibold">
                        Номера
                    </NavLink>
                    <NavLink v-if="$page.props.auth.user" :href="route('booking', $page.props.auth.user.id)" :active="route().current('booking')" class="nav-link"
                             active-class="text-blue-600 font-semibold">
                        Мои бронирования
                    </NavLink>
                </div>

                <!-- Правая часть (авторизация/профиль) -->
                <div class="flex items-center space-x-4">
                    <template v-if="$page.props.auth.user">
                        <NavLink :href="route('guest.profile')" class="flex items-center space-x-2 hover:text-blue-600">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span>{{ $page.props.auth.user.name }}</span>
                        </NavLink>
                        <button
                            @click="logout"
                            class="ml-4 px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200"
                        >
                            Выйти
                        </button>
                    </template>
                    <template v-else>
                        <NavLink :href="route('login')" :active="route().current('login')"
                                 class="px-4 py-2 hover:text-blue-600">
                            Вход
                        </NavLink>
                        <NavLink :href="route('register')" :active="route().current('register')"
                                 class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Регистрация
                        </NavLink>
                    </template>
                </div>

                <!-- Мобильное меню -->
                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100"
                >
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </nav>

            <!-- Мобильное меню (выпадашка) -->
            <div
                v-show="isMobileMenuOpen"
                class="md:hidden absolute bg-white w-full left-0 shadow-lg"
            >
                <div class="container px-4 py-4">
                    <ResponsiveNavLink :href="route('main')" :active="route().current('main')" class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                             active-class="text-blue-600 font-semibold"
                             @click="isMobileMenuOpen = false">
                        Главная
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('rooms')" :active="route().current('rooms')" class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                             active-class="text-blue-600 font-semibold"
                             @click="isMobileMenuOpen = false">
                        Номера
                    </ResponsiveNavLink>
                    <ResponsiveNavLink v-if="$page.props.auth.user" :href="route('booking', $page.props.auth.user.id)" :active="route().current('booking')" class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                             active-class="text-blue-600 font-semibold"
                             @click="isMobileMenuOpen = false">
                        Мои бронирования
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
import { Head, Link, router } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {route} from "ziggy-js";
import ApplicationMark from "@/Components/ApplicationMark.vue";

export default {
    name: 'SpaHeader',
    components: {
        ApplicationMark,
        ApplicationLogo,
        Link,
        NavLink,
        ResponsiveNavLink
    },
    data() {
        return {
            isMobileMenuOpen: false,
            navItems: [
                { title: 'Главная', path: '/' },
                { title: 'Номера', path: '/rooms' },
                { title: 'Мои бронирования', path: '/booking' },
            ]
        }
    },
    methods: {
        route,
        async logout() {
            router.post(route('logout'));
            // router.get(route('login'));
        }
    }
}
</script>

<style scoped>
.site-header {
    position: sticky;
    top: 0;
    z-index: 50;
}

.nav-link {
    @apply text-gray-600 hover:text-blue-600 transition-colors;
}

.router-link-active:not(.logo) {
    @apply text-blue-600 font-semibold;
}
</style>
