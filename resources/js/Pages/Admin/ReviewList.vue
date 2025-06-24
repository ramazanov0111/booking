<template>
    <AppLayout title="Reviews">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Отзывы
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Список отзывов</h1>
                </div>
                <!-- Фильтры и кнопка создания -->
                <div class="controls">
                    <div class="filters">
                        <select v-model="filters.user_id" class="filter-select" @change="loadData">
                            <option value="">Все пользователи</option>
                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }} {{ user.lastname }}
                            </option>
                        </select>

                        <select v-model="filters.room_id" class="filter-select" @change="loadData">
                            <option value="">Все номера</option>
                            <option
                                v-for="room in rooms"
                                :key="room.id"
                                :value="room.id"
                            >
                                {{ room.name }}
                            </option>
                        </select>

                        <flat-pickr
                            v-model="filters.date_range"
                            :config="dateConfig"
                            placeholder="Выберите период"
                            class="date-picker"
                            @on-change="loadData"
                        />
                    </div>
                </div>

                <!-- Таблица цен -->
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="auto-height-table">
                        <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Номер</th>
                            <th>Оценка</th>
                            <th>Комментарий</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                        </thead>

                        <tbody v-if="loading">
                        <tr>
                            <td colspan="6" class="loading">
                                <div class="loader"></div>
                            </td>
                        </tr>
                        </tbody>

                        <tbody v-else>
                        <tr v-for="review in reviews" :key="review.id">

                            <td>{{ review.user.name }} {{ review.user.lastname }}</td>

                            <td>{{ review.room.name }}</td>

                            <td>{{ review.rating }}</td>

                            <td>{{ review.comment }}</td>

                            <td>{{ getStatusPublish(review.published) }}</td>

                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <button v-if="!review.published"
                                    @click="publishReview(review.id)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    <i class="fas fa-share-square"></i>
                                </button>
                                <button
                                    @click="deleteReview(review.id)"
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
import {ref, onMounted} from 'vue'
import flatPickr from 'vue-flatpickr-component'
import {Russian} from 'flatpickr/dist/l10n/ru'
import AppLayout from "@/Layouts/AppLayout.vue";
import {route} from "ziggy-js";
import flatpickr from "flatpickr";
import {Link} from "@inertiajs/vue3";

flatpickr.localize(Russian)

// Данные
const reviews = ref([])
const rooms = ref([])
const users = ref([])
const filters = ref({
    user_id: '',
    room_id: '',
    date_range: ''
})

const meta = ref({})
const loading = ref(false)

// Конфигурация календаря
const dateConfig = {
    mode: 'range',
    dateFormat: 'd-m-Y',
    locale: Russian
}

// Загрузка данных
const loadData = async () => {
    try {
        loading.value = true
        const params = {
            page: meta.value.current_page,
            userId: filters.value.user_id,
            roomId: filters.value.room_id,
            // Разбиваем диапазон дат на start_date и end_date
            ...(filters.value.date_range && {
                start_date: filters.value.date_range.split(" — ")[0],
                end_date: filters.value.date_range.split(" — ")[1]
            })
        }

        const response = await axios.get(route('reviews.index'), {params})
        reviews.value = response.data.data

        const { data, ...rest } = response.data;
        meta.value = {...rest}
    } finally {
        loading.value = false
    }
}

// Загрузка номеров
const loadRooms = async () => {
    try {
        const response = await axios.get(route('rooms.index'))
        rooms.value = response.data.data
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    }
}

// Загрузка пользователей
const loadUsers = async () => {
    try {
        const response = await axios.get(route('users.index'))
        users.value = response.data.data
    } catch (error) {
        console.error('Ошибка загрузки пользователей:', error)
    }
}

// Удаление отзыва
const deleteReview = async (review) => {
    if (!confirm('Удалить этот отзыв?')) return
    try {
        await axios.delete(route('reviews.destroy', review))
        await loadData()
    } catch (error) {
        console.error('Ошибка удаления:', error)
    }
}

// Публикация отзыва
const publishReview = async (review) => {
    if (!confirm('Опубликовать этот отзыв?')) return
    try {
        await axios.patch(route('review.publish', review))
        await loadData()
    } catch (error) {
        console.error('Ошибка публикации:', error)
    }
}

// Форматирование даты
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU')
}

const getStatusPublish = (status) => {
    if (status) return 'Опубликовано'
    else return 'Не опубликовано';
}

// Смена страницы
const changePage = (page) => {
    meta.value.current_page = page;
}

// Инициализация
onMounted(async () => {
    await loadUsers()
    await loadRooms()
    await loadData()
})
</script>

<style scoped>

.auto-height-table {
    width: 100%;
    border-collapse: collapse;
}

.auto-height-table td {
    //border: 1px solid #ddd;
    padding: 8px;
    word-wrap: break-word; /* Перенос длинных слов */
    white-space: normal; /* Разрешить перенос строк */
    overflow-wrap: break-word; /* Современный аналог word-wrap */
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

input {
    width: 25%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

select {
    width: 25%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    gap: 1rem;
    flex-grow: 1;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
    min-width: 150px;
}

th {
    background: #f8f9fa;
    cursor: pointer;
    user-select: none;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    justify-content: center;
}

.pagination button {
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.pagination button.active {
    background: #2196f3;
    color: white;
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
