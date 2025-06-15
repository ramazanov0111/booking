<template>
    <AppLayout title="Форма пользователя">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Пользователи
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">
                        {{ isEditMode ? 'Редактирование пользователя' : 'Добавление нового пользователя' }}
                    </h1>
                    <Link :href="route('users.list')"
                          class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Назад к списку
                    </Link>
                </div>

                <!-- Форма -->
                <form @submit.prevent="handleSubmit" class="bg-white p-6 rounded-lg shadow-md">
                    <!-- Основные параметры -->
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <InputLabel for="name" value="Имя"/>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="errors.name"/>
                        </div>
                        <div>
                            <InputLabel for="lastname" value="Фамилия"/>
                            <TextInput
                                id="lastname"
                                v-model="form.lastname"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="errors.lastname"/>
                        </div>
                        <!-- E-mail -->
                        <div class="mt-4">
                            <InputLabel for="email" value="Email"/>
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="errors.email"/>
                        </div>

                        <div v-if="!isEditMode" class="mt-4">
                            <InputLabel for="password" value="Пароль"/>
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="errors.password"/>
                        </div>

                        <div v-if="isEditMode" class="mt-4">
                            <InputLabel for="new_password" value="Новый пароль"/>
                            <TextInput
                                id="new_password"
                                v-model="form.new_password"
                                type="password"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="errors.new_password"/>
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Подтверждение пароля" />
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="errors.password_confirmation" />
                        </div>

                        <!-- Номер телефона -->
                        <div class="mt-4">
                            <InputLabel for="phone" value="Телефон"/>
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="+7 (___) ___ __ __"
                                @input="change"
                            />
                            <InputError class="mt-2" :message="errors.phone"/>
                        </div>

                        <div class="mt-4">
                            <template v-for="role in roles">
                                <InputLabel :for="role.key" :value="role.value"/>
                                <input :id="role.key" type="radio" v-bind:value="role.key" v-model="form.role">
                            </template>
                        </div>

                        <!-- Удален -->
                        <div class="mt-4">
                            <InputLabel for="deleted" value="Удален"/>
                            <input
                                id="deleted"
                                v-model="form.deleted"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <InputError class="mt-2" :message="errors.deleted"/>
                        </div>

                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Link :href="route('users.list')"
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
import {ref, computed, onMounted} from 'vue'
import AppLayout from "../../Layouts/AppLayout.vue";
import {Link} from "@inertiajs/vue3";
import {route} from "ziggy-js";
import InputLabel from "../../Components/InputLabel.vue";
import InputError from "../../Components/InputError.vue";
import TextInput from "../../Components/TextInput.vue";
import Checkbox from "../../Components/Checkbox.vue";
import Inputmask from "inputmask";

const isEditMode = computed(() => !!route().params.user)
const loading = ref(false)

// Данные формы
const form = ref({
    name: '',
    lastname: '',
    email: '',
    password: '',
    new_password: '',
    password_confirmation: '',
    role: 'user',
    phone: '',
    deleted: false,
});

const errors = ref({});

const roles = [
    {key: 'admin', value: 'Администратор'},
    {key: 'user', value: 'Пользователь'}
];

const roleText = (role) => {
    return roles[role] || role
}

const user = route().params.user

// Загрузка данных для редактирования
const loadUserData = async () => {
    try {
        const {data} = await axios.get(route('users.show', user))

        form.value = {
            ...data.data
        }

    } catch (error) {
        console.error('Ошибка загрузки данных:', error)
        // window.location.href = route('users.list');
    }
}

// Отправка формы
const handleSubmit = async () => {

    loading.value = true
    const payload = {
        ...form.value
    }

    try {
        if (isEditMode.value) {
            await axios.put(route('users.update', user), payload)
        } else {
            await axios.post(route('users.store'), payload)
        }

        window.location.href = route('users.list');
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.log(error);
            alert('Ошибка сохранения')
        }
    } finally {
        loading.value = false
    }
}

const change = async (value) => {
    if (!value) return;
    let val = value;
    if (val[0] === '8') {
        val = val.replace('8', '+7');
    }
    if (val.replace(/[^0-9]+/g, '') === '789') {
        val = '79';
    }
    this.$emit('input', val);
}

const bindPhoneMask = async (inputElement, regexMask = null, placeholder = null) => {
    if (!regexMask) regexMask = '^\\+7 \\([3489]\\d\\d\\) \\d\\d\\d \\d\\d \\d\\d$';
    if (!placeholder) placeholder = '+7 (___) ___ __ __';

    Inputmask({
        regex: regexMask,
        placeholder: placeholder,
        postValidation: buffer => {
            let nums = buffer.join('').replace(/[^0-9]+/g, '');
            let hasSevenNumbersInARow = (/(\d)\1{6}/g).test(nums);
            return !hasSevenNumbersInARow;
        }
    }).mask(inputElement);
}

// Инициализация
onMounted(() => {
    if (isEditMode.value) {
        loadUserData()
    }
    let selector = document.getElementById('phone');
    bindPhoneMask(selector);
})
</script>

<style scoped>
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
