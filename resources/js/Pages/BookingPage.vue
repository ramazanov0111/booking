<template>
    <SpaLayout title="About">
        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <!-- Заголовок страницы -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Мои бронирования</h1>
                <p class="text-gray-600 mt-2">Здесь вы можете просмотреть все ваши текущие и прошлые бронирования</p>
            </div>

            <!-- Информация о бронированиях -->
            <div class="mb-4 flex items-center justify-between">
                <p class="text-gray-600">
                    Найдено бронирований: <span class="font-semibold">{{ bookings.length }}</span>
                </p>
            </div>

            <!-- Пустое состояние -->
            <div
                v-if="bookings.length === 0"
                class="bg-white rounded-xl shadow-sm p-12 text-center"
            >
                <i class="fas fa-calendar-days mx-auto h-16 w-16 text-gray-400"></i>
                <h3 class="mt-4 text-xl font-medium text-gray-900">Бронирования не найдены</h3>
            </div>

            <!-- Список бронирований -->
            <div v-else class="space-y-6">
                <div
                    v-for="booking in bookings"
                    :key="booking.id"
                    class="bg-white rounded-xl shadow-sm overflow-hidden"
                >
                    <div class="flex flex-col md:flex-row">
                        <!-- Изображение отеля -->
                        <div class="md:w-1/4">
                            <img
                                :src="booking.room.imageUrl"
                                :alt="booking.room.name"
                                class="w-full h-48 md:h-full object-cover"
                            >
                        </div>

                        <!-- Информация о бронировании -->
                        <div class="p-6 flex-1">
                            <div class="flex justify-between">
                                <div>
                                    <h2 class="text-xl font-bold">{{ booking.room.name }}</h2>
                                </div>

                                <div
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="statusClasses(booking.status)"
                                >
                                    {{ statusText(booking.status) }}
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Даты проживания</p>
                                    <p class="font-medium">
                                        {{ formatDate(booking.check_in) }} - {{ formatDate(booking.check_out) }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ booking.nights }} {{ pluralizeNights(booking.nights) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-500">Стоимость</p>
                                    <p class="font-medium text-lg">{{ formatPrice(booking.total_price) }} ₽</p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">

                                <button
                                    v-if="booking.status !== 'canceled'"
                                    @click="cancelBooking(booking)"
                                    class="px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50 flex items-center"
                                >
                                    <i class="fas fa-xmark-circle h-5 w-5 mr-2"></i>
                                    <span>Отменить</span>
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SpaLayout>
</template>

<script setup>
import SpaLayout from "@/Layouts/SpaLayout.vue";
import {ref, onMounted} from 'vue'
import {route} from "ziggy-js";

const props = defineProps({
    userId: Object,
});

// Данные бронирований (в реальном приложении загружаются с сервера)
const bookings = ref([])

// Загрузка данных
const loadData = async () => {
    try {
        const params = {userId: props.userId}
        const response = await axios.get(route('bookings'), {params})
        bookings.value = response.data
    } catch (e) {
        e.value = 'Не удалось загрузить бронирования. Попробуйте позже.'
    }
}

// Форматирование данных
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price)
}

const pluralizeNights = (nights) => {
    if (nights % 10 === 1 && nights % 100 !== 11) return 'ночь'
    if (nights % 10 >= 2 && nights % 10 <= 4 && (nights % 100 < 10 || nights % 100 >= 20)) return 'ночи'
    return 'ночей'
}

const statusText = (status) => {
    const statusMap = {
        'pending': 'В обработке',
        'confirmed': 'Подтверждено',
        'paid': 'Оплачено',
        'canceled': 'Отменено'
    }
    return statusMap[status] || status
}

const statusClasses = (status) => {
    const classes = {
        'pending': 'bg-blue-100 text-blue-800',
        'confirmed': 'bg-green-100 text-green-800',
        'paid': 'bg-gray-100 text-gray-800',
        'canceled': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const cancelBooking = async (booking) => {
    if (!confirm('Вы уверены, что хотите отменить бронирование?')) return
    try {
        await axios.patch(route('booking.cancel', booking.id))
        await loadData()
    } catch (error) {
        console.error('Ошибка отмены бронирования:', error)
    }
}

// Инициализация
onMounted(() => {
    // В реальном приложении здесь будет загрузка данных с сервера
    loadData()
})

</script>

<style scoped>
.about-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.title {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 2rem;
}

.content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    align-items: center;
}

.main-image {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.section-title {
    color: #34495e;
    border-bottom: 2px solid #3498db;
    padding-bottom: 0.5rem;
    margin: 2rem 0;
}

.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.fa-star.active {
    color: #f1c40f;
}

.review-card {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.feedback-form {
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.rating-input button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.5rem;
    color: #ddd;
    transition: color 0.2s;
}

.rating-input button.active {
    color: #f1c40f;
}

.submit-btn {
    background: #3498db;
    color: white;
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.2s;
}

.submit-btn:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .content {
        grid-template-columns: 1fr;
    }

    .contacts-grid {
        grid-template-columns: 1fr;
    }
}
</style>
