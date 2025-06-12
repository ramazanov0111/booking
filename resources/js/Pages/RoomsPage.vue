<template>
    <SpaLayout title="Номер">
        <div class="catalog-page">
            <div class="max-w-7xl mx-auto p-6 mb-6 bg-white sm:px-6 lg:px-8 rounded-xl shadow-lg">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Найти идеальный номер</h2>
                    <p class="text-gray-500">Отфильтруйте варианты по вашим предпочтениям</p>
                </div>
                <!-- Фильтры и сортировка -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Фильтр по датам -->
                    <div class="relative">
                        <div class="absolute -top-3 left-4 bg-white px-2 text-indigo-600 font-medium">Даты проживания
                        </div>
                        <div class="border border-gray-200 rounded-xl p-5 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 mt-4 text-sm text-gray-600">
                                <div class="date-range-picker">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Заезд</label>
                                    <VueDatePicker
                                        v-model="filters.checkIn"
                                        :enable-time-picker="false"
                                        placeholder="Выберите период"
                                        format="yyy-MM-dd"
                                        auto-apply
                                    />
                                </div>
                                <div class="date-range-picker">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Выезд</label>
                                    <VueDatePicker
                                        v-model="filters.checkOut"
                                        :enable-time-picker="false"
                                        placeholder="Выберите период"
                                        format="yyy-MM-dd"
                                        auto-apply
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Фильтр по гостям -->
                    <div class="relative">
                        <div class="absolute -top-3 left-4 bg-white px-2 text-indigo-600 font-medium">Гости</div>
                        <div class="border border-gray-200 rounded-xl p-5 pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex items-center space-x-3">
                                    <button
                                        class="counter-btn"
                                        :class="{ 'opacity-50 cursor-not-allowed': filters.guests <= guestsParams.min }"
                                        :disabled="filters.guests <= guestsParams.min"
                                        @click="decrement"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                  d="M20 12H4"></path>
                                        </svg>
                                    </button>

                                    <span class="counter-value">{{ filters.guests }}</span>

                                    <button
                                        class="counter-btn"
                                        :class="{ 'opacity-50 cursor-not-allowed': filters.guests >= guestsParams.max }"
                                        :disabled="filters.guests >= guestsParams.max"
                                        @click="increment"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                  d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Список номеров -->
            <div class="room-list">
                <div v-if="loading" class="loading-overlay">
                    <div class="loader"></div>
                </div>

                <div v-if="error" class="error-message">
                    {{ error }}
                </div>

                <div
                    v-for="room in filteredRooms"
                    :key="room.id"
                    class="room-card"
                >
                    <div class="room-image">
                        <img v-if="room.imageUrl" :src="room.imageUrl" :alt="room.name">
                        <div class="price-badge">{{ formatPrice(room.base_price) }} / ночь</div>
                    </div>

                    <div class="room-content">
                        <h3>
                            <Link :href="route('room.show', room.id)" class="flex items-center space-x-2">
                                {{ room.name }}
                            </Link>
                        </h3>
                        <div class="rating">
                            <span class="stars">{{ renderStars(room.rating) }}</span>
                            <span class="reviews">
                                ({{ room.reviewsCnt }} {{ pluralize(room.reviewsCnt, ['отзыв', 'отзыв', 'отзывов']) }})
                            </span>
                        </div>
                        <p class="description">{{ room.description }}</p>

                        <div class="amenities">
                            <span
                                v-for="amenity in room.amenities"
                                :key="amenity"
                                class="amenity-tag"
                            >
                              {{ amenity }}
                            </span>
                        </div>

                        <button
                            class="book-button"
                            id="book-button"
                            @click="openModal(room, $page.props.auth.user)"
                        >
                            Забронировать
                        </button>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="pagination">
                <button
                    v-for="page in totalPages"
                    :key="page"
                    :class="{ active: currentPage === page }"
                    @click="changePage(page)"
                >
                    {{ page }}
                </button>
            </div>

            <!-- Модальное окно бронирования -->
            <BookingModal
                :show="selectedRoom"
                :room="selectedRoom"
                :disabledDates="disabledDates"
                :user="$page.props.auth.user"

                @close="selectedRoom = null"
            />
        </div>
    </SpaLayout>
</template>

<script setup>
import {ref, computed, watch} from 'vue'
import SpaLayout from "@/Layouts/SpaLayout.vue";
import {route} from "ziggy-js";
import {Russian} from 'flatpickr/dist/l10n/ru'
import FlatPickr from "vue-flatpickr-component";
import BookingModal from "@/Components/BookingModal.vue";
import {Link} from "@inertiajs/vue3";
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// Данные
const filteredRooms = ref([])
// Список доступных удобств
const amenitiesList = ref([
    'Wi-Fi',
    'Телевизор',
    'Кондиционер',
    'Мини-бар',
    'Сейф',
    'Фен',
    'Тапочки'
])
const loading = ref(false)
const error = ref(null)
const errors = ref({})
const selectedRoom = ref(null)

const guestsParams = ref({
    min: 1,
    max: 6,
    step: 1
})

// Фильтры и сортировка
const filters = ref({
    checkIn: null,
    checkOut: null,
    guests: 2,
    amenities: []
})

const sortBy = ref('price_asc')
const currentPage = ref(1)
const totalPages = ref(1)

// Конфигурация календаря
const dateConfig = {
    mode: 'range',
    dateFormat: 'Y-m-d',
    locale: Russian
}

const increment = async () => {
    if (filters.value.guests < guestsParams.value.max) {
        filters.value.guests = filters.value.guests + guestsParams.value.step
    }
}
const decrement = async () => {
    if (filters.value.guests > guestsParams.value.min) {
        filters.value.guests = filters.value.guests - guestsParams.value.step
    }
}

// Загрузка данных
const loadRooms = async () => {
    try {
        loading.value = true
        error.value = null

        const params = {
            page: currentPage.value,
            guests: filters.value.guests,
            amenities: filters.value.amenities,
            sort: sortBy.value,
            checkIn: filters.value.checkIn,
            checkOut: filters.value.checkOut,
        }

        const response = await axios.get(route('api.rooms.list'), {params})


        filteredRooms.value = response.data.data
        totalPages.value = response.data.meta.last_page
    } catch (e) {
        error.value = 'Не удалось загрузить номера. Попробуйте позже.'
    } finally {
        loading.value = false
    }
}

// Форматирование цены
const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0
    }).format(price)
}

// Склонение числительных
const pluralize = (n, forms) => {
    return forms[n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
}

// Отображение рейтинга звездами
const renderStars = (rating) => {
    return '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating))
}

const openModal = (room, user) => {
    if (user) {
        selectedRoom.value = room
    } else {
        window.location.href = route('login')
    }
}

// Следим за изменениями фильтров
watch([filters, sortBy, currentPage], () => {
    loadRooms()
}, {deep: true})

// Инициализация
loadRooms()
</script>

<style scoped>
.catalog-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.catalog-controls {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.filter-group {
    margin-bottom: 1.5rem;
}

.amenities-checkboxes {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.room-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    position: relative;
}

.room-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.room-card:hover {
    transform: translateY(-5px);
}

.room-image {
    position: relative;
    height: 200px;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.price-badge {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.room-content {
    padding: 1.5rem;
}

.rating {
    margin: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stars {
    color: #ffd700;
}

.amenities {
    margin: 1rem 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.amenity-tag {
    background: #f0f4ff;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9em;
}

.book-button {
    width: 100%;
    padding: 0.75rem;
    background: #2196f3;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.book-button:hover {
    background: #1976d2;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.pagination button {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
}

.pagination button.active {
    background: #2196f3;
    color: white;
    border-color: #2196f3;
}

.counter-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background-color: #f9fafb;
    transition: all 0.2s ease;
}

.counter-btn:not(:disabled):hover {
    background-color: #f3f4f6;
}

.counter-btn:first-child {
    border-right: 1px solid #e5e7eb;
}

.counter-btn:last-child {
    border-left: 1px solid #e5e7eb;
}

.counter-value {
    display: inline-block;
    min-width: 40px;
    padding: 0 8px;
    text-align: center;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    transition: all 0.3s ease;
}

.counter-icon {
    width: 16px;
    height: 16px;
}
</style>
