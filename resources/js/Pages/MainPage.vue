<script>
import SpaLayout from '@/Layouts/SpaLayout.vue';
import FlatPickr from 'vue-flatpickr-component'
import RoomCard from '@/components/RoomCard.vue'

export default {
    components: {
        SpaLayout,
        FlatPickr,
        RoomCard
    },
    data() {
        return {
            checkIn: null,
            checkOut: null,
            guests: 2,
            featuredRooms: [
                {
                    id: 1,
                    name: 'Лесной люкс',
                    price: 4500,
                    image: 'public/build/assets/dom1.jpg',
                    amenities: ['Wi-Fi', 'Джакузи', 'Завтрак'],
                    capacity: 2
                },
                // ... другие номера
            ],
            amenities: [
                {
                    icon: 'fas fa-wifi',
                    title: 'Бесплатный Wi-Fi',
                    description: 'Высокоскоростной интернет во всех номерах'
                },
                // ... другие преимущества
            ],
            testimonials: [
                {
                    author: 'Мария Иванова',
                    rating: 5,
                    text: 'Прекрасное место для отдыха! Обслуживание на высшем уровне.'
                },
                // ... другие отзывы
            ]
        }
    },
    methods: {
        pluralize(n, forms) {
            return forms[n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
        },
        searchRooms() {
            this.$router.push({
                path: '/rooms',
                query: {
                    checkIn: this.checkIn,
                    checkOut: this.checkOut,
                    guests: this.guests
                }
            })
        }
    }
}
</script>

<template>
    <SpaLayout>
        <div class="home">
            <!-- Hero Section -->
            <section class="hero bg-blue-50 py-20">
                <div class="container mx-auto px-4 text-center">
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-800 mb-6">
                        Уютный гостевой дом в сердце природы
                    </h1>
                    <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                        Отдохните от городской суеты в наших комфортабельных номерах с видом на лес
                    </p>

                    <!-- Booking Widget -->
                    <div class="booking-widget bg-white p-6 rounded-2xl shadow-lg max-w-4xl mx-auto">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Заезд</label>
                                <flat-pickr
                                    v-model="checkIn"
                                    placeholder="Выберите дату"
                                    class="w-full p-2 border rounded-lg"
                                />
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Выезд</label>
                                <flat-pickr
                                    v-model="checkOut"
                                    placeholder="Выберите дату"
                                    class="w-full p-2 border rounded-lg"
                                />
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Гости</label>
                                <select v-model="guests" class="w-full p-2 border rounded-lg">
                                    <option v-for="n in 6" :value="n">{{ n }} {{ pluralize(n, ['гость', 'гостя', 'гостей']) }}</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    @click="searchRooms"
                                    class="w-full bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition"
                                >
                                    Найти номер
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Featured Rooms -->
            <section class="py-16 container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Наши номера</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <RoomCard
                        v-for="room in featuredRooms"
                        :key="room.id"
                        :room="room"
                        class="transform hover:scale-105 transition duration-300"
                    />
                </div>
            </section>

            <!-- Amenities -->
            <section class="bg-gray-50 py-16">
                <div class="container mx-auto px-4">
                    <h2 class="text-3xl font-bold text-center mb-12">Наши преимущества</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div
                            v-for="amenity in amenities"
                            :key="amenity.title"
                            class="text-center p-6 bg-white rounded-xl shadow-sm"
                        >
                            <i :class="amenity.icon" class="text-blue-600 text-4xl mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">{{ amenity.title }}</h3>
                            <p class="text-gray-600">{{ amenity.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials -->
            <section class="py-16 container mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-12">Отзывы гостей</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div
                        v-for="review in testimonials"
                        :key="review.author"
                        class="p-8 bg-white rounded-xl shadow-sm border-l-4 border-blue-600"
                    >
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                            <div class="ml-4">
                                <p class="font-semibold">{{ review.author }}</p>
                                <div class="flex text-yellow-400 mt-1">
                                    <i
                                        v-for="star in 5"
                                        :key="star"
                                        class="fas fa-star"
                                        :class="{ 'opacity-30': star > review.rating }"
                                    ></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">"{{ review.text }}"</p>
                    </div>
                </div>
            </section>
        </div>
    </SpaLayout>
</template>

<style scoped>
.hero {
    background-image: url('public/build/assets/hero_back.jpg');
    background-size: cover;
    background-position: center;
}

.booking-widget {
    backdrop-filter: blur(10px);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.room-card {
    animation: fadeIn 0.6s ease-out;
}
</style>
