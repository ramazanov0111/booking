<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import 'flatpickr/dist/flatpickr.min.css';
import {route} from "ziggy-js";

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
    window.location.href = route('main')
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('main')" class="flex items-center space-x-2">
                                    <ApplicationMark class="block h-12 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Дашборд
                                </NavLink>
                                <NavLink :href="route('users.list')" :active="route().current('users.list')">
                                    Пользователи
                                </NavLink>
                                <NavLink :href="route('configs.list')" :active="route().current('configs.list')">
                                    Конфигурации
                                </NavLink>
                                <NavLink :href="route('rooms.list')" :active="route().current('rooms.list')">
                                    Номера
                                </NavLink>
                                <NavLink :href="route('prices.list')" :active="route().current('prices.list')">
                                    Цены
                                </NavLink>
                                <NavLink :href="route('booking.list')" :active="route().current('booking.list')">
                                    Бронирования
                                </NavLink>
                                <NavLink :href="route('reviews.list')" :active="route().current('reviews.list')">
                                    Отзывы
                                </NavLink>
                                <NavLink :href="route('blocked_dates.list')" :active="route().current('blocked_dates.list')">
                                    Заблокированные даты
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Управление аккаунтом
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Профиль
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Выйти
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }} {{ $page.props.auth.user.lastname }}
                                </div>
                            </div>
                        </div>

                        <div class="border-t mt-3 border-gray-200" />

                        <div class="ml-1 mt-3 space-y-1">
                            <DropdownLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Профиль
                            </DropdownLink>
                            <DropdownLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Дашборд
                            </DropdownLink>
                            <DropdownLink :href="route('users.list')" :active="route().current('users.list')">
                                Пользователи
                            </DropdownLink>
                            <NavLink :href="route('configs.list')" :active="route().current('configs.list')">
                                Конфигурации
                            </NavLink>
                            <DropdownLink :href="route('rooms.list')" :active="route().current('rooms.list')">
                                Номера
                            </DropdownLink>
                            <DropdownLink :href="route('rooms.list')" :active="route().current('rooms.list')">
                                Цены
                            </DropdownLink>
                            <DropdownLink :href="route('rooms.list')" :active="route().current('rooms.list')">
                                Бронирования
                            </DropdownLink>
                            <DropdownLink :href="route('rooms.list')" :active="route().current('rooms.list')">
                                Отзывы
                            </DropdownLink>
                            <DropdownLink :href="route('blocked_dates.list')" :active="route().current('blocked_dates.list')">
                                Заблокированные даты
                            </DropdownLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <DropdownLink as="button">
                                    Выйти
                                </DropdownLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
