<template>
    <AppLayout title="Room Form">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Номера
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">
                        {{ isEditMode ? 'Редактирование номера' : 'Создание нового номера' }}
                    </h1>
                    <Link :href="route('rooms.list')"
                          class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Назад к списку
                    </Link>
                </div>

                <!-- Форма -->
                <form @submit.prevent="handleSubmit" class="bg-white p-6 rounded-lg shadow-md"
                      enctype="multipart/form-data">
                    <!-- Название -->
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">Название номера</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full p-2 border rounded-lg"
                            :class="{ 'border-red-500': errors.name }"
                        >
                        <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
                    </div>

                    <!-- Описание -->
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">Описание</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full p-2 border rounded-lg"
                            :class="{ 'border-red-500': errors.description }"
                        ></textarea>
                        <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description }}</div>
                    </div>

                    <!-- Основные параметры -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
                        <!-- Вместимость -->
                        <div>
                            <label class="block text-gray-700 mb-2">Вместимость</label>
                            <input
                                v-model.number="form.capacity"
                                type="number"
                                min="1"
                                class="w-full p-2 border rounded-lg"
                                :class="{ 'border-red-500': errors.capacity }"
                            >
                            <div v-if="errors.capacity" class="text-red-500 text-sm mt-1">{{ errors.capacity }}</div>
                        </div>

                        <!-- Цена -->
                        <div>
                            <label class="block text-gray-700 mb-2">Базовая цена</label>
                            <div class="relative">
                                <input
                                    v-model.number="form.base_price"
                                    type="number"
                                    min="0"
                                    step="100"
                                    class="w-full p-2 border rounded-lg pl-8"
                                    :class="{ 'border-red-500': errors.base_price }"
                                >
                                <span class="absolute left-2 top-3 text-gray-500">₽</span>
                            </div>
                            <div v-if="errors.base_price" class="text-red-500 text-sm mt-1">
                                {{ errors.base_price }}
                            </div>
                        </div>

                        <!-- Статус -->
                        <div>
                            <label class="block text-gray-700 mb-2">Статус</label>
                            <label class="flex items-center space-x-2">
                                <input
                                    v-model="form.is_available"
                                    type="checkbox"
                                    class="form-checkbox h-5 w-5 text-blue-600"
                                >
                                <span class="text-gray-700">Доступен для бронирования</span>
                            </label>
                        </div>

                        <!-- Изображение -->
                        <div>
                            <label class="block text-gray-700 mb-2">Текущее изображение</label>
                            <div class="preview-item">
                                <img :src="form.imageUrl" alt="Текущее изображение" class="preview-image">
                                <button @click.prevent="deletePreview" class="remove-btn">×</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Новое изображение</label>
                            <input
                                type="file"
                                class="w-full p-2 border rounded-lg"
                                @change="uploadFile"
                            >
                            <div v-if="previewImage" class="preview-item">
                                <img :src="previewImage" alt="Новое изображение" class="preview-image">
                            </div>
                        </div>
                    </div>

                    <!-- Галерея -->
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">Галерея</label>
                        <div v-if="form.gallery.length" class="previews grid grid-cols-1 md:grid-cols-4 gap-2">
                            <div v-for="item in form.gallery" :key="index" class="gallery-item">
                                <img :src="item.imageUrl" :alt="item.imageUrl" class="preview-image">
                                <button @click.prevent="deleteImage(item.id)" class="remove-btn">×</button>
                            </div>
                        </div>
                        <div class="mb-6">
                            <input
                                type="file"
                                class="p-2 border rounded-lg"
                                @change="uploadGallery"
                                multiple
                            >
                        </div>
                        <div v-if="previewUrls.length" class="previews grid grid-cols-1 md:grid-cols-5 gap-2">
                            <div v-for="(image, index) in previewUrls" :key="index" class="gallery-item">
                                <img :src="image" alt="Preview" class="preview-image">
                                <button @click.prevent="removeImage(index)" class="remove-btn">×</button>
                            </div>
                        </div>
                    </div>

                    <!-- Удобства -->
                    <div class="mb-6">
                        <label class="block text-gray-700 mb-2">Удобства</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label
                                v-for="amenity in amenitiesList"
                                :key="amenity"
                                class="flex items-center space-x-2 p-2 border rounded-lg hover:bg-gray-50"
                            >
                                <input
                                    v-model="form.amenities"
                                    type="checkbox"
                                    :value="amenity"
                                    class="form-checkbox h-5 w-5 text-blue-600"
                                >
                                <span class="text-gray-700">{{ amenity }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Link :href="route('rooms.list')"
                              class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                            Отмена
                        </Link>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ loading ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Динамика цен по текущему номеру</h1>
                </div>
                <!-- Таблица цен -->
                <div class="bg-white p-12 rounded-lg shadow-md">
                    <table>
                        <thead>
                        <tr>
                            <th>Период</th>
                            <th>Цена</th>
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
                        <tr v-for="price in prices" :key="price.id">

                            <td>{{ formatDate(price.start_date) }} - {{ formatDate(price.end_date) }}</td>

                            <td>{{ price.price }}</td>

                            <td>{{ formatDate(price.updated_at) }}</td>

                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <Link :href="route('prices.edit', price.id)"
                                      class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </Link>
                                <button
                                    @click="deletePrice(price.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

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
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {ref, computed, onMounted, onBeforeUnmount} from 'vue'
import {useRouter} from 'vue-router'
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link, useForm} from "@inertiajs/vue3";
import {route} from "ziggy-js";

const router = useRouter()

const isEditMode = computed(() => !!route().params.room)
const loading = ref(false)
const savedRoomId = ref(null)

// Данные формы
const form = ref({
    name: '',
    description: '',
    capacity: 1,
    base_price: 0,
    is_available: true,
    amenities: [],
    imageUrl: '',
    gallery: [],
})

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


const previewImage = ref('');
const file = ref(null);
const galleryFiles = ref([]);
const previewUrls = ref([]);

// Ошибки валидации
const errors = ref({
    name: '',
    description: '',
    capacity: '',
    base_price: ''
})

const room = route().params.room
const prices = ref([])

const uploadFile = async (e) => {
    if (e.target.files) {
        file.value = e.target.files[0];
        previewImage.value = URL.createObjectURL(file.value);
    }
}

const uploadGallery = async (e) => {
    const files = e.target.files;
    if (!files.length) return;

    galleryFiles.value = Array.from(files);
    generatePreviews();
}

const generatePreviews = () => {
    // Очищаем предыдущие превью
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
    previewUrls.value = [];

    // Создаем новые превью
    galleryFiles.value.forEach(file => {
        const url = URL.createObjectURL(file);
        previewUrls.value.push(url);
    });
}

const removeImage = async (index) => {
    // Освобождаем ресурсы превью
    URL.revokeObjectURL(previewUrls.value[index]);

    // Удаляем из массивов
    previewUrls.value.splice(index, 1);
    galleryFiles.value.splice(index, 1);

    // Обновляем input (для возможности повторного выбора тех же файлов)
    // this.$refs.fileInput.value = '';
}

// Удаление основного изображения
const deletePreview = async () => {
    if (!confirm('Удалить главное изображение?')) return
    try {
        await axios.delete(route('rooms_photo.destroy', room))
        await loadRoomData()
    } catch (error) {
        console.error('Ошибка удаления:', error)
    }
}

// Удаление изображения из галереи
const deleteImage = async (image) => {
    if (!confirm('Удалить это изображение?')) return
    try {
        await axios.delete(route('rooms_gallery.destroy', image))
        await loadRoomData()
    } catch (error) {
        console.error('Ошибка удаления:', error)
    }
}

// Загрузка данных для редактирования
const loadRoomData = async () => {
    try {
        const {data} = await axios.get(route('rooms.show', room))

        form.value = {
            ...data.data,
            amenities: data.data.amenities || [],
            gallery: data.data.gallery
        }

        prices.value = data.data.prices || [];
    } catch (error) {
        console.error('Ошибка загрузки данных:', error)
        window.location.href = route('rooms.list');
    }
}

// Валидация формы
const validateForm = () => {
    let isValid = true
    errors.value = {name: '', description: '', capacity: '', base_price: ''}

    if (!form.value.name.trim()) {
        errors.value.name = 'Введите название номера'
        isValid = false
    }

    if (!form.value.description.trim()) {
        errors.value.description = 'Введите описание номера'
        isValid = false
    }

    if (form.value.capacity < 1) {
        errors.value.capacity = 'Вместимость должна быть не менее 1'
        isValid = false
    }

    if (form.value.base_price <= 0) {
        errors.value.base_price = 'Цена должна быть больше нуля'
        isValid = false
    }

    return isValid
}

// Форматирование даты
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU')
}

const deletePrice = async (price) => {
    if (!confirm('Удалить эту цену?')) return
    try {
        await axios.delete(route('prices.destroy', price.id))
        await loadRoomData()
    } catch (error) {
        console.error('Ошибка удаления:', error)
    }
}

// Отправка формы
const handleSubmit = async () => {
    if (!validateForm()) return

    loading.value = true
    const payload = {
        ...form.value,
        amenities: form.value.amenities,
    }

    try {
        if (isEditMode.value) {
            await axios.put(route('rooms.update', room), payload)
        } else {
            const response = await axios.post(route('rooms.store'), payload)
            savedRoomId.value = response.data.data.id;
        }

        const roomId = isEditMode.value ? room : savedRoomId.value;
        const formData1 = new FormData();
        const formData2 = new FormData();

        formData1.append('room_image', file.value); // Ключ должен совпадать с именем в Laravel

        galleryFiles.value.forEach(galleryFile => {
            formData2.append('gallery[]', galleryFile);
        });

        try {
            const response1 = file.value ? await axios.post(route('room.upload_file', roomId), formData1) : '';
            const response2 = galleryFiles.value.length ? await axios.post(route('room.upload_gallery', roomId), formData2) : '';
            console.log('Успех:', [response1.data, response2.data]);
        } catch (error) {
            console.error('Ошибка:', error.response);
        }

        // Очищаем после успешной отправки
        previewUrls.value.forEach(url => URL.revokeObjectURL(url));
        previewUrls.value = [];
        galleryFiles.value = [];
        // this.$refs.fileInput.value = '';

        window.location.href = route('rooms.list');
    } catch (error) {
        console.error('Ошибка сохранения:', error)
        // Обработка ошибок сервера
        if (error.response?.data?.errors) {
            errors.value = {...errors.value, ...error.response.data.errors}
        }
    } finally {
        loading.value = false
    }
}

// Инициализация
onMounted(() => {
    if (isEditMode.value) {
        loadRoomData()
    }
})

// Очищаем ресурсы при уничтожении компонента
onBeforeUnmount(() => {
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
})

</script>

<style scoped>

.preview-item {
    position: relative;
    width: 175px;
    height: 175px;
    border: 1px solid #eee;
    border-radius: 4px;
    overflow: hidden;
}

.gallery-item {
    position: relative;
    width: 100px;
    height: 100px;
    border: 1px solid #eee;
    border-radius: 4px;
    overflow: hidden;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.previews {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 20px 0;
}

.remove-btn {
    position: absolute;
    top: 0;
    right: 0;
    background: rgba(255, 0, 0, 0.7);
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-weight: bold;
}

.gallery {
    width: 200px;
    height: 150px;
}

.form-checkbox {
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
}

.form-checkbox:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
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
