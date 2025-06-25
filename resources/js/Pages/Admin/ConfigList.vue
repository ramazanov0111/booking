<template>
    <AppLayout title="Конфигурации">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Конфигурации
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок и кнопка добавления -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Управление конфигурациями</h1>
                    <Link :href="route('configs.create')"
                          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Добавить конфигурацию
                    </Link>
                </div>

                <!-- Таблица номеров -->
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Значение</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Состояние загрузки -->
                        <tr v-if="loading">
                            <td colspan="5" class="px-6 py-4 text-center">
                                <div class="animate-pulse flex space-x-4">
                                    <div class="flex-1 space-y-4 py-1">
                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Список номеров -->
                        <tr v-for="config in configs" :key="config.id" @click="handleRowClick(config)">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ config.key }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-for="value in config.value">
                                   {{ value }}<br />
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <Link :href="route('configs.edit', config)" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </Link>
                                <button
                                    @click="deleteConfig(config.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- Пагинация -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Показано с {{ meta.from }} по {{ meta.to }} из {{ meta.total }}
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-for="page in meta.last_page"
                                    :key="page"
                                    @click="changePage(page)"
                                    :class="page === meta.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                                    class="px-3 py-1 rounded-md border border-gray-300 hover:bg-gray-50"
                                >
                                    {{ page }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import {route} from "ziggy-js";

const configs = ref([])
const meta = ref({})
const loading = ref(true)

// Получение данных
const loadConfigs = async (page = 1) => {
    try {
        loading.value = true

        const {data} = await axios.get(route('configs.index'))

        configs.value = data.data
        meta.value = data.meta
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    } finally {
        loading.value = false
    }
}


// Удаление номера
const deleteConfig = async (id) => {
    if (confirm('Вы уверены, что хотите удалить конфигурацию?')) {
        try {
            await axios.delete(route('configs.destroy', id))

            configs.value = configs.value.filter(config => config.id !== id)
        } catch (error) {
            console.error('Ошибка удаления:', error)
        }
    }
}

const handleRowClick = async (config) => {
    // Переход с помощью Vue Router
    // route('users.edit', user);

    // Или обычная переадресация
    window.location.href = route('configs.edit', config.id);
}

// Смена страницы
const changePage = (page) => {
    loadConfigs(page)
}

// Склонение числительных
const pluralize = (n, forms) => {
    return forms[n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
}

onMounted(() => {
    loadConfigs()
})

</script>

<style scoped>

.filters {
    display: flex;
    gap: 1rem;
    flex-grow: 1;
}

.controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

input {
    width: 25%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.number {
    width: 140px;
}

select {
    width: 20%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

table {
    @apply min-w-full divide-y divide-gray-200;
}

th {
    @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

td {
    @apply px-6 py-4 whitespace-nowrap;
}

tr:hover td {
    @apply bg-gray-50;
}
</style>
