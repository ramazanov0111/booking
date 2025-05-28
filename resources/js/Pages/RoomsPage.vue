<template>
    <SpaLayout title="About">
        <div class="catalog-page">
            <!-- Фильтры и сортировка -->
            <div class="catalog-controls">
                <div class="filters">
                    <div class="filter-group">
                        <label>Даты проживания</label>
                        <flat-pickr
                            v-model="filters.dates"
                            :config="dateConfig"
                            placeholder="Выберите даты"
                        />
                    </div>

                    <div class="filter-group">
                        <label>Гости</label>
                        <select v-model="filters.guests">
                            <option v-for="n in 6" :value="n">
                                {{ n }} {{ pluralize(n, ['гость', 'гостя', 'гостей']) }}
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Удобства</label>
                        <div class="amenities-checkboxes">
                            <label v-for="amenity in allAmenities" :key="amenity">
                                <input
                                    type="checkbox"
                                    v-model="filters.amenities"
                                    :value="amenity"
                                >
                                {{ amenity }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="sorting">
                    <label>Сортировка:</label>
                    <select v-model="sortBy">
                        <option value="price_asc">Цена (по возрастанию)</option>
                        <option value="price_desc">Цена (по убыванию)</option>
                        <option value="rating">Рейтинг</option>
                    </select>
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
                        <img :src="room.main_image" :alt="room.name">
                        <div class="price-badge">{{ formatPrice(room.base_price) }} / ночь</div>
                    </div>

                    <div class="room-content">
                        <h3>{{ room.name }}</h3>
                        <div class="rating">
                                                        <span class="stars">{{ renderStars(room.rating) }}</span>
                                                        <span class="reviews">({{ room.reviews_count }} отзывов)</span>
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
                            @click="openModal(room)"
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
                :dates="filters.dates"
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

// Данные
const filteredRooms = ref([])
// Список доступных удобств
const allAmenities = ref([
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
const selectedRoom = ref(null)
const errors = ref({})

// Фильтры и сортировка
const filters = ref({
    dates: [],
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

// Загрузка данных
const loadRooms = async () => {
    try {
        loading.value = true
        error.value = null

        const params = {
            page: currentPage.value,
            guests: filters.value.guests,
            amenities: filters.value.amenities,
            sort: sortBy.value
        }

        if (filters.value.dates.length === 2) {
            params.start_date = filters.value.dates[0]
            params.end_date = filters.value.dates[1]
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

const openModal = (room) => {
    selectedRoom.value = room;
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

@media (max-width: 768px) {
    .catalog-controls {
        grid-template-columns: 1fr;
    }

    .amenities-checkboxes {
        grid-template-columns: 1fr;
    }
}
</style>
