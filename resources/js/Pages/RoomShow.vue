<template>
    <SpaLayout :title="roomCur.name">
        <div class="container mx-auto px-4 py-8 max-w-6xl">

            <!--                         Галерея номера-->
            <!--                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">-->
            <!--                                            <div class="md:col-span-2">-->
            <!--                                                <img :src="roomCur.imageUrl" alt="Основное фото" class="w-full h-96 object-cover rounded-lg shadow-md">-->
            <!--                                            </div>-->
            <!--                                            <div class="grid grid-cols-2 gap-4">-->
            <!--                                                <img-->
            <!--                                                    v-for="(img, index) in roomCur.gallery"-->
            <!--                                                    :key="index"-->
            <!--                                                    :src="img"-->
            <!--                                                    :alt="'Фото номера ' + (index + 1)"-->
            <!--                                                    class="w-full h-48 object-cover rounded-lg shadow-md"-->
            <!--                                                >-->
            <!--                                            </div>-->
            <!--                                        </div>-->

            <!-- Основная информация -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Левая колонка - описание -->
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-4 mb-8">
                        <div class="md:col-span-3">
                            <img :src="roomCur.imageUrl" alt="Основное фото"
                                 class="w-full h-96 object-cover rounded-lg shadow-md"
                                 @click="openLightbox(0)">
                        </div>
                        <div class="previews grid grid-cols-1 md:grid-cols-5 gap-2">
                            <div v-for="(item, index) in roomCur.gallery" class="gallery-item">
                                <img :src="item.imageUrl" :alt="item.imageUrl"
                                     class="preview-image rounded-lg"
                                     @click="openLightbox(index+1)">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-3xl font-bold">{{ roomCur.name }}</h1>
                        <div class="flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                            <i class="fas fa-star h-5 w-5 mr-1"></i>
                            <span class="font-semibold">{{ Number(roomCur.rating).toFixed(1) }}</span>
                        </div>
                    </div>

                    <p class="text-gray-700 mb-8">{{ roomCur.description }}</p>

                    <!-- Удобства -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Удобства в номере</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="(amenity, index) in roomCur.amenities" :key="index" class="flex items-center">
                                <i class="fas fa-check-circle h-5 w-5 text-green-500 mr-2"></i>
                                <span>{{ amenity }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Отзывы -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold">Отзывы ({{ reviews.length }})</h2>
                            <!--                            <button-->
                            <!--                                v-if="$page.props.auth.user"-->
                            <!--                                @click="showReviewForm = true"-->
                            <!--                                class="text-blue-600 hover:text-blue-800 font-medium"-->
                            <!--                            >-->
                            <!--                                Написать отзыв-->
                            <!--                            </button>-->
                        </div>

                        <!-- Форма отзыва -->
                        <div v-if="showReviewForm" class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold mb-4">Оставьте ваш отзыв</h3>
                            <form @submit.prevent="submitReview($page.props.auth.user)">
                                <div class="mb-4">
                                    <label class="block text-gray-700 mb-2">Оценка</label>
                                    <div class="flex">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            @click="newReview.rating = star"
                                            class="mr-2 focus:outline-none"
                                        >

                                            <i class="fas fa-star"
                                               :class="[ 'h-6 w-6', star <= newReview.rating ? 'text-yellow-400 fill-current' : 'text-gray-300']"
                                            ></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="review-text" class="block text-gray-700 mb-2">Комментарий</label>
                                    <textarea
                                        id="review-text"
                                        v-model="newReview.text"
                                        rows="4"
                                        class="w-full px-3 py-2 border rounded-md"
                                        placeholder="Поделитесь вашими впечатлениями..."
                                    ></textarea>
                                </div>

                                <div class="flex justify-end space-x-4">
                                    <button
                                        type="button"
                                        @click="showReviewForm = false"
                                        class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100"
                                    >
                                        Отмена
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                        :disabled="isSubmittingReview"
                                    >
                                        <span v-if="isSubmittingReview">Отправка...</span>
                                        <span v-else>Отправить отзыв</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Список отзывов -->
                        <div v-if="reviews.length > 0" class="space-y-6">
                            <div v-for="(review, index) in reviews" :key="index" class="border-b pb-6">
                                <div class="flex items-center mb-2">
                                    <div class="flex">
                                        <i class="fas fa-star"
                                           v-for="star in 5"
                                           :key="star"
                                           :class="['h-5 w-5', star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300']"
                                        />
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-2">
                                    Автор: {{ review.user.name }} • {{ formatDate(review.created_at) }}
                                </p>
                                <p class="text-gray-700">{{ review.comment }}</p>
                                <p v-if="review.answer" style="text-align: right;" class="text-gray-600 mt-3 mb-2">
                                    Администратор • {{ formatDate(review.updated_at) }}
                                </p>
                                <p v-if="review.answer" style="text-align: right;" class="text-gray-700">
                                    {{ review.answer }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            Пока нет отзывов. Будьте первым!
                        </div>
                    </div>
                </div>

                <!-- Правая колонка - бронирование -->
                <div>
                    <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-2xl font-bold">{{ formatPrice(currentPrice) }}</span>
                            <span class="text-gray-600">за ночь</span>
                        </div>

                        <div class="flex items-center text-green-600 mb-6">
                            <i class="fas fa-check-circle h-5 w-5 mr-2"/>
                            <span>Доступно для бронирования</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 mb-2">
                                    Даты проживания <span class="required">*</span>
                                </label>
                                <div class="flex py-2">
                                    <VueDatePicker
                                        v-model="checkIn"
                                        :disabled-dates="disabledDates"
                                        :min-date="minDate"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @on-change="fetchPricePeriods"
                                    />
                                </div>

                                <div class="flex py-2">
                                    <VueDatePicker
                                        v-model="checkOut"
                                        :disabled-dates="disabledDates"
                                        :min-date="minDateForCheckOut"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @on-change="fetchPricePeriods"
                                    />
                                </div>

                                <div class="input-group">
                                    <label class="block text-gray-700 mb-2">
                                        Способ оплаты <span class="required">*</span>
                                    </label>
                                    <select v-model="paymentMethod" required>
                                        <option
                                            v-for="item in paymentMethods"
                                            :key="item.item"
                                            :value="item.key"
                                        >
                                            {{ item.value }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="roomCur.is_available_extra_bed" class="flex py-2">
                                    <label class="flex items-center space-x-2">
                                        <input
                                            v-model="extraBed"
                                            type="checkbox"
                                            class="form-checkbox h-5 w-5 text-blue-600"
                                            @change.prevent="calculateLocally"
                                        >
                                        <span class="text-gray-700">Дополнительное место</span>
                                    </label>
                                </div>
                            </div>

                            <button
                                @click="handleBooking($page.props.auth.user)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-lg font-medium text-lg mt-6 transition-all"
                                :disabled="isProcessing"
                            >
                                <span v-if="isProcessing">Обработка...</span>
                                <span v-else>Забронировать за {{ formatPrice(calculation?.total ?? 0) }}</span>
                            </button>
                        </div>

                        <!-- Детализация стоимости -->
                        <div v-if="calculation">
                            <div v-for="day in calculation.daily_breakdown" class="mt-2 pt-2 border-b">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">{{ day.date }}:</span>
                                    <span class="text-gray-600">{{ formatPrice(day.price) }}</span>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-b">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Дополнительное место:</span>
                                    <span class="text-gray-600">
                                    {{ formatPrice(500) }}/ночь
                                </span>
                                </div>
                            </div>
                            <div class="pt-2">
                                <div class="flex justify-between font-bold text-lg mt-2 pt-2">
                                    <span>Итого</span>
                                    <span class="text-gray-600 font-normal">
                                    {{ calculation.nights }} {{ pluralizeNights(calculation.nights) }}
                                </span>
                                    <span>{{ formatPrice(calculation.total) }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Модальное окно для просмотра фотографий -->
            <Lightbox
                v-if="lightboxVisible"
                :images="[
                    {
                        imageUrl: roomCur.imageUrl,
                    },
                 ...roomCur.gallery]"
                :initial-index="currentImageIndex"
                @close="closeLightbox"
            />
        </div>
    </SpaLayout>
</template>

<script setup>
import {ref, computed, watch, onMounted, watchEffect} from 'vue'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import SpaLayout from "@/Layouts/SpaLayout.vue";
import {route} from "ziggy-js";
import Lightbox from '@/Components/Lightbox.vue';

const disabledDates = ref(null)
const currentPrice = ref(null)
const roomId = route().params.room
const roomCur = ref({})
const isProcessing = ref(false)
const errors = ref({})

// Минимальная дата для заезда - текущий день
const minDate = ref(new Date());
minDate.value.setHours(0, 0, 0, 0); // Обнуляем время

// Минимальная дата для выезда:
// - Если выбрана дата заезда, то дата заезда
// - Иначе текущий день
const minDateForCheckOut = computed(() => {
    return checkIn.value || minDate.value;
});

// Загрузка данных номера
const loadRoomData = async () => {
    try {
        const {data} = await axios.get(route('rooms.show', roomId))

        roomCur.value = data.data
        currentPrice.value = roomCur.value.base_price
    } catch (error) {
        console.error('Ошибка загрузки данных:', error)
    }
}

// Состояния бронирования
const paymentMethod = ref([])
const extraBed = ref(false)
const checkIn = ref(null)
const checkOut = ref(null)
const paymentMethods = [
    {key: 'online', value: 'Онлайн'},
    {key: 'on_site', value: 'На месте'}
];
const pricePeriods = ref(null)
const calculation = ref(null);

// Отзывы
const reviews = ref([])

// Загрузка данных
const loadReviews = async () => {
    try {
        const params = {
            roomId: roomId
        }

        const response = await axios.get(route('reviews'), {params})
        reviews.value = response.data
    } catch (error) {
        console.error('Ошибка загрузки отзывов:', error)
    }
}

const showReviewForm = ref(false)
const isSubmittingReview = ref(false)
const newReview = ref({
    rating: 5,
    text: ''
})

const fetchPricePeriods = async () => {
    try {
        const params = {
            roomId: roomId,
            checkInDate: checkIn.value,
            checkOutDate: checkOut.value
        }

        if (checkIn.value && checkOut.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.length) {
                pricePeriods.value = response.data
            }
            calculateLocally()
        }
    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

const calculateLocally = () => {
    const start = new Date(checkIn.value);
    const end = new Date(checkOut.value);
    const dailyBreakdown = [];
    let total = 0;

    for (let date = new Date(start); date < end; date.setDate(date.getDate() + 1)) {
        const price = getPriceForDate(date);
        dailyBreakdown.push({
            date: formatDate(date),
            price: price
        });
        total += price;
    }

    const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24))

    if (extraBed.value) {
        total += 500 * nights;
    }

    calculation.value = {
        total: total,
        nights: nights,
        daily_breakdown: dailyBreakdown
    };
};

const getPriceForDate = (date) => {
    const dateStr = date;
    if (pricePeriods.value) {
        const period = pricePeriods.value.find(p =>
            dateStr >= new Date(p.start_date) && dateStr <= new Date(p.end_date)
        );
        return period ? period.price : currentPrice.value;
    } else {
        return currentPrice.value;
    }
};

const getDisabledDatesForRoom = async () => {
    try {
        const response1 = await axios.get(route('blocked_dates.by_room', roomId))
        const response2 = await axios.get(route('booking_dates.by_room', roomId))

        disabledDates.value = [...response1.data, ...response2.data]
    } catch (error) {
        console.error('Ошибка загрузки дат:', error)
    }
}

// Методы
const handleBooking = async (user) => {
    if (!calculation.value) return alert('Заполните все поля!')

    try {
        isProcessing.value = true
        errors.value = {}

        const payload = {
            user_id: user.id,
            room_id: roomId,
            payment_method: paymentMethod.value,
            total_price: calculation.value.total,
            check_in: checkIn.value.toISOString().slice(0, 10),
            check_out: checkOut.value.toISOString().slice(0, 10),
            status: 'confirmed',
            extra_bed: extraBed.value,
            stripe_payment_id: 'qwerty'
        }

        await axios.post(route('booking.store'), payload)
        window.location.href = route('booking', user.id)

    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.log(error);
            alert('Ошибка сохранения')
        }
    } finally {
        isProcessing.value = false
    }
}

const submitReview = async (user) => {
    isSubmittingReview.value = true

    try {
        const payload = {
            user_id: user.id,
            room_id: roomId,
            rating: newReview.value.rating,
            comment: newReview.value.text,
        }

        await axios.post(route('reviews.store'), payload)

        // Сброс формы
        newReview.value = {
            rating: 5,
            text: ''
        }
        showReviewForm.value = false
        isSubmittingReview.value = false

    } catch (error) {
        console.error('Ошибка добавления отзыва:', error)
    }
}

const pluralizeNights = (nights) => {
    if (nights % 10 === 1 && nights % 100 !== 11) return 'ночь'
    if (nights % 10 >= 2 && nights % 10 <= 4 && (nights % 100 < 10 || nights % 100 >= 20)) return 'ночи'
    return 'ночей'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long'
    })
}

// Форматирование цены
const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0
    }).format(price)
}

const lightboxVisible = ref(false);
const currentImageIndex = ref(0);

function openLightbox(index) {
    currentImageIndex.value = index;
    lightboxVisible.value = true;
    // Блокируем прокрутку страницы
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    lightboxVisible.value = false;
    // Восстанавливаем прокрутку
    document.body.style.overflow = '';
}

// Валидация при изменении дат
watch([checkIn, checkOut], () => {
    if (checkOut.value && checkIn.value && checkOut.value <= checkIn.value) {
        checkOut.value = null
    }
    fetchPricePeriods()
})

// Инициализация
onMounted(async () => {
    await loadRoomData()
    await loadReviews()
    // await fetchPricePeriods()
    await getDisabledDatesForRoom()

    const now = new Date();
    const msUntilMidnight = new Date(now).setHours(24, 0, 0, 0) - now;

    setTimeout(() => {
        minDate.value = new Date();
        minDate.value.setHours(0, 0, 0, 0);
    }, msUntilMidnight);
})

</script>

<style scoped>

.form-checkbox {
    width: 10%;
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
}

.form-checkbox:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

.previews {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin: 10px 5px 5px 0;
}

.gallery-item {
    position: relative;
    width: 150px;
    height: 150px;
    border: 1px solid #eee;
    border-radius: 4px;
    overflow: hidden;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.container {
    max-width: 1200px;
}

.sticky {
    position: sticky;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

input, select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.required {
    color: #e53935;
}

</style>
