<template>
    <AppLayout title="Форма конфигурации">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Конфигурации
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">
                        {{ isEditMode ? 'Редактирование конфигурации' : 'Создание новой конфигурации' }}
                    </h1>
                    <Link :href="route('configs.list')"
                          class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Назад к списку
                    </Link>
                </div>

                <!-- Форма -->
                <form @submit.prevent="handleSubmit" class="bg-white p-6 rounded-lg shadow-md">
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6 mb-6">
                        <!-- Название -->
                        <div>
                            <label class="block text-gray-700 mb-2">Ключ конфигурации <span
                                class="required">*</span></label>
                            <input
                                v-model="form.key"
                                type="text"
                                class="w-full p-2 border rounded-lg"
                                :class="{ 'border-red-500': errors.key }"
                            >
                            <div v-if="errors.key" class="text-red-500 text-sm mt-1">{{ errors.key }}</div>
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-2">Массив</label>
                            <label class="flex items-center space-x-2">
                                <input
                                    v-model="form.is_array"
                                    @change="removeDetails"
                                    type="checkbox"
                                    class="form-checkbox h-5 w-5 text-blue-600"
                                >
                            </label>
                        </div>
                        <!-- Значение -->
                        <div v-if="!form.is_array">
                            <label class="block text-gray-700 mb-2">Значение <span class="required">*</span></label>
                            <textarea v-model="form.value"
                                      rows="4"
                                      class="w-full p-2 border rounded-lg"
                                      :class="{ 'border-red-500': errors.value }"
                            >
                            </textarea>
                        </div>
                        <div v-if="form.is_array">
                            <label class="block text-gray-700 mb-2">Значение <span class="required">*</span></label>
                            <div v-for="(item, index) in form.value"
                                 :key="index"
                                 class="auto-height detail-row">
                                <input
                                    v-model="form.value[index]"
                                    type="text"
                                    class="w-full p-2 border rounded-lg"
                                    :class="{ 'border-red-500': errors.value }"
                                >
                                <button
                                    v-if="index !== 0"
                                    @click.prevent="removeDetail(index)"
                                    class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-xmark-circle"></i>
                                </button>
                            </div>
                            <div v-if="errors.value" class="text-red-500 text-sm mt-2 mb-2">{{ errors.value }}</div>
                            <button
                                v-if="form.is_array"
                                @click.prevent="addDetail"
                                class="text-red-600 hover:text-red-900"
                            >
                                Добавить значение <i class="fas fa-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Link :href="route('configs.list')"
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

    </AppLayout>
</template>

<script setup>
import {ref, computed, onMounted, onBeforeUnmount} from 'vue'
import {useRouter} from 'vue-router'
import AppLayout from "@/Layouts/AppLayout.vue";
import {Link, useForm} from "@inertiajs/vue3";
import {route} from "ziggy-js";

const router = useRouter()

const isEditMode = computed(() => !!route().params.config)
const loading = ref(false)

// Данные формы
const form = ref({
    key: '',
    is_array: false,
    value: [''],
})

// Ошибки валидации
const errors = ref({
    key: '',
    value: '',
})

const config = route().params.config
// Загрузка данных для редактирования
const loadConfigData = async () => {
    try {
        const {data} = await axios.get(route('configs.show', config))

        form.value = {
            ...data.data
        }

    } catch (error) {
        console.error('Ошибка загрузки данных:', error)
    }
}

// Валидация формы
const validateForm = () => {
    let isValid = true
    errors.value = {key: '', value: ''}

    if (!form.value.key.trim()) {
        errors.value.key = 'Введите название конфигурации'
        isValid = false
    }

    if (form.value.value.length === 0) {
        errors.value.value = 'Добавьте хотя бы одно значение конфигурации'
        isValid = false
    } else if (!form.value.value[0]) {
        errors.value.value = 'Заполните значение конфигурации'
        isValid = false
    }

    return isValid
}

const addDetail = () => {
    form.value.value.push('');
}

const removeDetail = (index) => {
    form.value.value.splice(index, 1);
}

const removeDetails = () => {
    if (!form.value.is_array) {
        form.value.value.splice(0, form.value.value.length - 2);
    }
}

// Отправка формы
const handleSubmit = async () => {
    if (!validateForm()) return

    form.value.value = !form.value.is_array ? [form.value.value] : form.value.value
    loading.value = true
    const payload = {
        ...form.value,
    }

    try {
        if (isEditMode.value) {
            await axios.put(route('configs.update', config), payload)
        } else {
            await axios.post(route('configs.store'), payload)
        }

        window.location.href = route('configs.list');
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
        loadConfigData()
    }
})

</script>

<style scoped>

.detail-row {
    display: flex;
    margin-bottom: 10px;
    gap: 10px;
}

.required {
    color: #e53935;
}

.auto-height textarea {
    padding: 8px;
    word-wrap: break-word; /* Перенос длинных слов */
    white-space: normal; /* Разрешить перенос строк */
    overflow-wrap: break-word; /* Современный аналог word-wrap */
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
