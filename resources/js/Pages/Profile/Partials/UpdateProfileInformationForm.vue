<script setup>
import {onMounted, ref} from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Inputmask from "inputmask/dist/inputmask.es6.js";

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    lastname: props.user.lastname,
    login: props.user.login,
    email: props.user.email,
    phone: props.user.phone,
});

// Ошибки валидации
const errors = ref({
    name: '',
    lastname: '',
    login: '',
    phone: '',
})

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (!validateForm()) return

    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

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

const validateForm = () => {
    let isValid = true
    const latinRegex = /^[a-zA-Z0-9]+$/;
    const passRegex = /(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{8,}/g;

    errors.value = {
        name: '',
        lastname: '',
        login: '',
        phone: '',
    }

    if (!form.name.trim()) {
        errors.value.name = 'Введите имя пользователя'
        isValid = false
    }

    if (!form.lastname.trim()) {
        errors.value.lastname = 'Введите фамилию пользователя'
        isValid = false
    }

    if (!form.login.trim()) {
        errors.value.login = 'Введите логин пользователя'
        isValid = false
    } else if (!latinRegex.test(form.login)) {
        errors.value.login = 'Логин должен содержать только латиницу и цифры!'
        isValid = false
    }

    if (form.phone.length < 18 || form.phone.toString().includes('_')) {
        errors.value.phone = 'Длина номер телефона не соответствует формату!'
        isValid = false
    }

    return isValid
}

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

// Инициализация
onMounted(() => {
    let selector = document.getElementById('phone');
    bindPhoneMask(selector);
})
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Учетные данные
        </template>

        <template #description>
            Обновите ваши данные.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Имя -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Имя" required="1" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                />
                <InputError :message="errors.name" class="mt-2" />
            </div>

            <!-- Фамилия -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="lastname" value="Фамилия" required="1" />
                <TextInput
                    id="lastname"
                    v-model="form.lastname"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="lastname"
                />
                <InputError :message="errors.lastname" class="mt-2" />
            </div>

            <!-- Логин -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="login" value="Логин" required="1" />
                <TextInput
                    id="login"
                    v-model="form.login"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError :message="errors.login" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" required="1" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="email"
                />
                <InputError :message="errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <!-- Телефон -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="phone" value="Телефон" required="1" />
                <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="+7 (___) ___ __ __"
                    @input="change"
                    required
                />
                <InputError :message="errors.phone" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Сохранено.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Сохранить
            </PrimaryButton>
        </template>
    </FormSection>
</template>
