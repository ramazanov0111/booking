<template>
    <SpaLayout title="Номер">
        <div class="catalog-page">
            <div class="max-w-7xl mx-auto p-6 mb-6 bg-white sm:px-6 lg:px-8 rounded-xl shadow-lg">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Найти идеальный номер</h2>
                    <p class="text-gray-500">Отфильтруйте варианты по вашим предпочтениям</p>
                </div>
                <!-- Фильтры -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <!-- Фильтр по датам -->
                    <div class="relative">
                        <div class="absolute -top-3 left-4 bg-white px-2 text-black-600 font-medium">Дата заезда</div>
                        <div class="border border-gray-200 rounded-xl p-5 pt-6">
                            <div class="flex justify-between items-center">
                                <div class="date-range-picker mr-3">
                                    <VueDatePicker
                                        ref="checkInPicker"
                                        v-model="filters.checkIn"
                                        :min-date="minDate"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @update:model-value="handleCheckInSelected"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute -top-3 left-4 bg-white px-2 text-black-600 font-medium">Дата выезда</div>
                        <div class="border border-gray-200 rounded-xl p-5 pt-6">
                            <div class="flex justify-between items-center">
                                <div class="date-range-picker">
                                    <VueDatePicker
                                        ref="checkOutPicker"
                                        v-model="filters.checkOut"
                                        :min-date="minDateForCheckOut"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @update:model-value="handleCheckOutSelected"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Фильтр по гостям -->
                    <div class="relative">
                        <div class="absolute -top-3 left-4 bg-white px-2 text-black-600 font-medium">Гости</div>
                        <div class="border border-gray-200 rounded-xl p-5 pt-6">
                            <div class="flex justify-between items-center">
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

                    <div class="relative filter-button">
                        <button @click.prevent="loadRooms"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Фильтровать
                        </button>
                    </div>
                </div>

                <!-- Фильтр по удобствам -->
                <div class="relative mt-5 amenities-filter">
                    <div class="absolute -top-3 left-4 bg-white px-2 text-black-600 font-medium">Удобства</div>
                    <div class="border border-gray-200 rounded-xl p-2 pt-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-3">
                                <label
                                    v-for="amenity in amenitiesList"
                                    :key="amenity"
                                    class="flex items-center space-x-2 rounded-lg hover:bg-gray-50 amenities-filter-item"
                                >
                                    <input
                                        v-model="filters.amenities"
                                        type="checkbox"
                                        :value="amenity"
                                        class="form-checkbox h-5 w-5 text-blue-600"
                                    >
                                    <span class="text-gray-700">{{ amenity }}</span>
                                </label>
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

                <div class="room-card" v-for="room in filteredRooms"
                     :key="room.id">
                    <!-- Основной контент карточки -->
                    <div class="room-card-content">
                        <Link :href="route('room.show', room.id)" class="grid">
                            <div class="room-image">
                                <img v-if="room.imageUrl" :src="room.imageUrl" :alt="room.name">
                                <div class="price-badge" v-if="room.price < room.base_price || room.price.price < room.base_price" :class="{ 'strikethrough': room.price.price > 0 && room.price.price < room.base_price }">{{ formatPrice(room.base_price) }} / ночь</div>
                                <div class="new-price" v-if="room.price.price > 0">{{ formatPrice(room.price.price) }} / ночь</div>
                            </div>

                            <div class="room-content">
                                <h3>
                                    {{ room.name }}
                                </h3>
                                <div class="rating">
                                    <span class="stars">{{ renderStars(room.rating) }}</span>
                                    <span class="reviews">
                                        ({{
                                            room.reviewsCnt
                                        }} {{ pluralize(room.reviewsCnt, ['отзыв', 'отзыв', 'отзывов']) }})
                                    </span>
                                </div>

                                <div class="amenities">
                                    <span
                                        v-for="amenity in room.amenities"
                                        :key="amenity"
                                        class="amenity-tag"
                                    >
                                      {{ amenity }}
                                    </span>
                                </div>

                            </div>
                        </Link>
                    </div>
                    <!-- Кнопка бронирования -->
                    <div class="book-button">
                        <button @click="openModal(room, $page.props.auth.user)">
                            Забронировать
                        </button>
                    </div>
                </div>
            </div>

            <!-- Модальное окно бронирования -->
            <BookingModal
                :show="selectedRoom"
                :room="selectedRoom"
                :user="$page.props.auth.user"

                @close="selectedRoom = null"
            />
        </div>
    </SpaLayout>
</template>

<script setup>
import {ref, computed, watch, watchEffect, nextTick, onMounted} from 'vue'
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
    'Холодильник',
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
    guests: 1,
    amenities: []
})

// Минимальная дата для заезда - текущий день
const minDate = ref(new Date());
minDate.value.setHours(0, 0, 0, 0); // Обнуляем время

// Минимальная дата для выезда:
// - Если выбрана дата заезда, то дата заезда
// - Иначе текущий день
const minDateForCheckOut = computed(() => {
    return filters.value.checkIn || minDate.value;
});


const checkInPicker = ref(null)
const checkOutPicker = ref(null)
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
            guests: filters.value.guests,
            amenities: filters.value.amenities,
            checkIn: filters.value.checkIn,
            checkOut: filters.value.checkOut,
        }

        const response = await axios.get(route('api.rooms.list'), {params})

        filteredRooms.value = response.data.data
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

const handleInput = (nextField) => {
    console.log(nextField)
    // Автоматический переход к следующему полю
    nextTick(() => {
        this.$refs.checkOutInput.focus();
    });
    nextTick(() => {
        this.$refs[nextField].focus();
    });
}

const handleCheckInSelected = () => {
    if (filters.value.checkIn) {
        nextTick(() => {
            // Альтернатива: если нужно открыть календарь сразу
            checkOutPicker.value.openMenu();
        });
    }
}

const handleCheckOutSelected = () =>  {
    if (filters.value.checkOut) {
        // После выбора даты выезда можно перейти к следующему блоку
        // Например, к полю "Гости"
        nextTick(() => {
            // Фокусируемся на следующем элементе
            document.querySelector('.counter-btn').focus();
        });
    }
}

// Инициализация
onMounted(() => {
    loadRooms()
    const now = new Date();
    const msUntilMidnight = new Date(now).setHours(24, 0, 0, 0) - now;

    setTimeout(() => {
        minDate.value = new Date();
        minDate.value.setHours(0, 0, 0, 0);
    }, msUntilMidnight);
});


</script>

<style scoped>

.strikethrough {
    text-decoration: line-through;
}

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

.filter-button {
    display: flex;
    justify-content: right;
    align-items: end;
}

.amenities-filter {
    margin: 1rem 0 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    flex: 1; /* Занимает все доступное пространство */
    flex-direction: column;
}
.amenities-filter-item {
    padding: 0.3rem 0.5rem;
    font-size: 1.1em;
}

.amenities {
    margin: 1rem 0 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.amenity-tag {
    background: #f0f4ff;
    padding: 0.25rem 0.4rem;
    border-radius: 4px;
    font-size: 0.8em;
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
    position: relative;
    display: flex; /* Включаем flex-контейнер */
    flex-direction: column; /* Элементы в колонку */
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
    justify-items: center;
    height: 470px; /* Фиксированная высота */
}

.room-card-content {
    flex: 1; /* Занимает все доступное пространство */
    display: flex;
    flex-direction: column;
}

.book-button {
    margin: 0 15px 15px; /* Отступы: сверху 0, по бокам 15px, снизу 15px */
    padding: 0;
    width: calc(100% - 30px); /* Ширина с учетом отступов */
}

.book-button button {
    width: 100%;
    padding: 0.75rem;
    background: #2196f3;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.book-button button:hover {
    background: #1976d2;
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
    left: 2rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.new-price {
    position: absolute;
    bottom: 1rem;
    right: 2rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.room-content {
    padding: 0.8rem;
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

.form-checkbox {
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
}

.form-checkbox:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}
</style>
