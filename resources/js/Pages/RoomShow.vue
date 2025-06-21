<template>
    <SpaLayout title="{{roomCur.name}}">
        <div class="container mx-auto px-4 py-8 max-w-6xl">

            <!--             Галерея номера-->
            <!--                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">-->
            <!--                                <div class="md:col-span-2">-->
            <!--                                    <img :src="room.images[0]" alt="Основное фото" class="w-full h-96 object-cover rounded-lg shadow-md">-->
            <!--                                </div>-->
            <!--                                <div class="grid grid-cols-2 gap-4">-->
            <!--                                    <img-->
            <!--                                        v-for="(img, index) in room.images.slice(1, 5)"-->
            <!--                                        :key="index"-->
            <!--                                        :src="img"-->
            <!--                                        :alt="'Фото номера ' + (index + 2)"-->
            <!--                                        class="w-full h-48 object-cover rounded-lg shadow-md"-->
            <!--                                    >-->
            <!--                                </div>-->
            <!--                            </div>-->

            <!-- Основная информация -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Левая колонка - описание -->
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-4 mb-8">
                        <div class="md:col-span-3">
                            <img :src="roomCur.imageUrl" alt="Основное фото"
                                 class="w-full h-96 object-cover rounded-lg shadow-md">
                        </div>
                        <div class="previews grid grid-cols-1 md:grid-cols-5 gap-2">
                            <div v-for="item in roomCur.gallery" class="gallery-item">
                                <img :src="item.imageUrl" :alt="item.imageUrl"
                                     class="preview-image rounded-lg">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-3xl font-bold">{{ roomCur.name }}</h1>
                        <div class="flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                            <i class="fas fa-star h-5 w-5 mr-1"></i>
                            <span class="font-semibold">{{ roomCur.rating }}</span>
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
                            <button
                                v-if="$page.props.auth.user"
                                @click="showReviewForm = true"
                                class="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Написать отзыв
                            </button>
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
                                           :class="[
                      'h-5 w-5',
                      star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                    ]"
                                        />
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-2">Автор: {{ review.user.name }} • {{
                                        formatDate(review.created_at)
                                    }}</p>
                                <p class="text-gray-700">{{ review.comment }}</p>
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
                                <label class="block text-gray-700 mb-2">Даты проживания</label>
                                <div class="flex py-2">
                                    <VueDatePicker
                                        v-model="checkIn"
                                        :disabled-dates="disabledDates"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @on-change="loadPrice"
                                    />
                                </div>

                                <div class="flex py-2">
                                    <VueDatePicker
                                        v-model="checkOut"
                                        :disabled-dates="disabledDates"
                                        :enable-time-picker="false"
                                        format="dd-MM-yyy"
                                        auto-apply
                                        @on-change="loadPrice"
                                    />
                                </div>

                                <div class="input-group">
                                    <label class="block text-gray-700 mb-2">Способ оплаты <span class="required">*</span></label>
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
                            </div>

                            <button
                                @click="handleBooking($page.props.auth.user)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-lg font-medium text-lg mt-6 transition-all"
                                :disabled="isProcessing"
                            >
                                <span v-if="isProcessing">Обработка...</span>
                                <span v-else>Забронировать за {{ totalPrice }} ₽</span>
                            </button>
                        </div>

                        <div class="mt-6 pt-6 border-t">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">{{ currentPrice }} ₽ × {{ nightsCount }} ночей</span>
                                <span>{{ totalPrice }} ₽</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg mt-4 pt-4 border-t">
                                <span>Итого</span>
                                <span>{{ totalPrice }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SpaLayout>
</template>

<script setup>
import {ref, computed, onMounted, watch} from 'vue'
import SpaLayout from "@/Layouts/SpaLayout.vue";
import {route} from "ziggy-js";
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const disabledDates = ref(null)
const currentPrice = ref(null)
const roomId = route().params.room
const roomCur = ref({})
const isProcessing = ref(false)
const errors = ref({})

// Загрузка данных номера
const loadRoomData = async () => {
    try {
        const {data} = await axios.get(route('rooms.show', roomId))

        roomCur.value = data.data
    } catch (error) {
        console.error('Ошибка загрузки данных:', error)
    }
}

// Состояния бронирования
const paymentMethod = ref([])
const checkIn = ref(null)
const checkOut = ref(null)
const paymentMethods = [
    { key: 'online', value: 'Онлайн' },
    { key: 'on_site', value: 'На месте' }
];

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

// Вычисляемые свойства
const nightsCount = computed(() => {
    loadPrice()
    if (!checkIn.value || !checkOut.value) return 0
    const diff = checkOut.value - checkIn.value
    return Math.ceil(diff / (1000 * 60 * 60 * 24))
})

const loadPrice = async () => {
    try {
        const params = {
            roomId: roomId,
            checkInDate: checkIn.value,
            checkOutDate: checkOut.value
        }

        if (checkIn.value && checkOut.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.price) {
                currentPrice.value = response.data.price
            } else {
                currentPrice.value = roomCur.value.base_price
            }
        } else {
            currentPrice.value = roomCur.value.base_price
        }
    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

const getDisabledDatesForRoom = async () =>  {
    try {
        const response1 = await axios.get(route('blocked_dates.by_room', roomId))
        const response2 = await axios.get(route('booking_dates.by_room', roomId))

        disabledDates.value = [...response1.data, ...response2.data]
    } catch (error) {
        console.error('Ошибка загрузки дат:', error)
    }
}

const totalPrice = computed(() =>
    currentPrice.value * nightsCount.value
)

// Методы
const handleBooking = async (user) => {
    try {
        isProcessing.value = true
        errors.value = {}

        const payload = {
            user_id: user.id,
            room_id: roomId,
            payment_method: paymentMethod.value,
            total_price: totalPrice.value,
            check_in: checkIn.value,
            check_out: checkOut.value,
            status: 'pending',
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

// Валидация при изменении дат
watch([checkIn.value, checkOut.value], () => {
    if (checkOut.value && checkIn.value && checkOut.value <= checkIn.value) {
        checkOut.value = null
    }
    loadPrice()
})

// Инициализация
onMounted(async () => {
    await loadRoomData()
    await loadPrice()
    await getDisabledDatesForRoom()
    await loadReviews()
})

</script>

<style scoped>

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
