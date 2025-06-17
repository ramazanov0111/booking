<template>
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Логотип -->
                    <div class="shrink-0 flex items-center">
                        <Link :href="route('main')" class="flex items-center space-x-2">
                            <ApplicationMark class="block h-12 w-auto"/>
                        </Link>
                    </div>

                    <!-- Навигация для десктопа -->
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                        <NavLink :href="route('main')" :active="route().current('main')" class="nav-link"
                                 active-class="text-blue-600 font-semibold">
                            Главная
                        </NavLink>
                        <NavLink :href="route('rooms')" :active="route().current('rooms')" class="nav-link"
                                 active-class="text-blue-600 font-semibold">
                            Номера
                        </NavLink>
                        <NavLink v-if="$page.props.auth.user" :href="route('booking', $page.props.auth.user.id)"
                                 :active="route().current('booking')" class="nav-link"
                                 active-class="text-blue-600 font-semibold">
                            Мои бронирования
                        </NavLink>
                    </div>
                </div>

                <!-- Правая часть (авторизация/профиль) -->
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <div class="ms-3 relative">
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                            <template v-if="$page.props.auth.user">
                                <NavLink :href="route('guest.profile')"
                                         class="flex items-center space-x-2 hover:text-blue-600">
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
                    </div>
                </div>
                <!-- Мобильное меню -->

                <div class="md:hidden flex justify-between h-16">
                    <template v-if="$page.props.auth.user">
                        <NavLink :href="route('guest.profile')"
                                 class="flex items-center space-x-2 hover:text-blue-600">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span>{{ $page.props.auth.user.name }}</span>
                        </NavLink>
                        <button
                            @click="logout"
                            class="ml-4 px-4 py-2 rounded-lg hover:bg-gray-200"
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

                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100"
                >
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Мобильное меню (выпадашка) -->
        <div
            v-show="isMobileMenuOpen"
            class="md:hidden absolute bg-white w-full left-0 shadow-lg"
        >
            <div class="container px-4 py-4">
                <DropdownLink :href="route('main')" :active="route().current('main')"
                                   class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                                   active-class="text-blue-600 font-semibold">
                    Главная
                </DropdownLink>
                <DropdownLink :href="route('rooms')" :active="route().current('rooms')"
                                   class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                                   active-class="text-blue-600 font-semibold">
                    Номера
                </DropdownLink>
                <DropdownLink v-if="$page.props.auth.user"
                                   :href="route('booking', $page.props.auth.user.id)"
                                   :active="route().current('booking')"
                                   class="block py-2 px-4 hover:bg-gray-50 rounded-lg"
                                   active-class="text-blue-600 font-semibold">
                    Мои бронирования
                </DropdownLink>
            </div>
        </div>
    </nav>
</template>

<script>
import {Head, Link, router} from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {route} from "ziggy-js";
import ApplicationMark from "@/Components/ApplicationMark.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

export default {
    name: 'SpaHeader',
    components: {
        DropdownLink,
        Dropdown,
        ApplicationMark,
        ApplicationLogo,
        Link,
        NavLink,
        ResponsiveNavLink
    },
    data() {
        return {
            isMobileMenuOpen: false,
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
