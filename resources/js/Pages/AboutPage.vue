<template>
    <SpaLayout title="Главная">
        <div class="about-page">
            <!-- Секция с основным описанием -->
            <section class="about-section">
                <h1 class="title">Добро пожаловать в наш гостевой дом!</h1>
                <div class="content">
                    <img
                        src="../../../public/images/dom1.jpg"
                        alt="Наш гостевой дом"
                        class="main-image"
                    >
                    <div class="description">
                        <p>
                            {{ contacts.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Контактная информация -->
            <section class="contacts-section">
                <h2 class="section-title">Контакты</h2>
                <div class="contacts-grid">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Адрес: {{ contacts.address }}</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <p>Телефон: {{ contacts.phone }}</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <p>Email: {{ contacts.email }}</p>
                    </div>
                </div>
            </section>

            <!-- Карта -->
            <section class="map-section">
                <h2 class="section-title">Мы находимся здесь</h2>
                <div class="map-wrapper">
                    <!--                    <iframe-->
                    <!--                        :src="mapUrl"-->
                    <!--                        width="100%"-->
                    <!--                        height="450"-->
                    <!--                        style="border:0;"-->
                    <!--                        allowfullscreen=""-->
                    <!--                        loading="lazy"-->
                    <!--                        referrerpolicy="no-referrer-when-downgrade"-->
                    <!--                    ></iframe>-->
                    <!--                    <a href="https://yandex.ru/maps/org/astrakhanskiy_gosudarstvenny_tekhnicheskiy_universitet/1102893439/?utm_medium=mapframe&utm_source=maps"-->
                    <!--                        style="color:#eee;font-size:12px;position:absolute;top:0px;">Астраханский государственный технический университет</a>-->
                    <!--                    <a href="https://yandex.ru/maps/37/astrahan/category/university/184106140/?utm_medium=mapframe&utm_source=maps"-->
                    <!--                    style="color:#eee;font-size:12px;position:absolute;top:14px;">ВУЗ в Астрахани</a>-->
                    <iframe :src="data.mapUrl" width="100%" height="450" frameborder="1" allowfullscreen="true"
                            loading="lazy"
                            style="position:relative; border:0;" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>
        </div>
    </SpaLayout>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";
import {route} from "ziggy-js";
import SpaLayout from "@/Layouts/SpaLayout.vue";

const data = ref({
    mapUrl: "https://yandex.ru/map-widget/v1/?ll=39.024380%2C44.138614&mode=search&ol=geo&ouri=ymapsbm1%3A%2F%2Fgeo%3Fdata%3DCgozMTUzNjg2Mjk3ErQB0KDQvtGB0YHQuNGPLCDQmtGA0LDRgdC90L7QtNCw0YDRgdC60LjQuSDQutGA0LDQuSwg0KLRg9Cw0L_RgdC40L3RgdC60LjQuSDQvNGD0L3QuNGG0LjQv9Cw0LvRjNC90YvQuSDQvtC60YDRg9CzLCDRgdC10LvQviDQkNCz0L7QuSwg0LrQstCw0YDRgtCw0Lsg0JvQsNCz0YPQvdCwLCAyLCDQv9C-0LTRitC10LfQtCA3IgoNBxkcQhXwjTBCMIqrz5EQ&z=15.51",
})

const loading = ref(true)
const contacts = ref([])

const loadContacts = async () => {
    try {
        loading.value = true

        const {data} = await axios.get(route('contacts'))

        contacts.value = {
            address: data.find(contact => contact.key === 'Адрес')?.value ?? "ул. Примерная, д. 11",
            phone: data.find(contact => contact.key === 'Телефон')?.value ?? "+7 (999) 123-45-67",
            email: data.find(contact => contact.key === 'Email')?.value ?? "contact@guesthouse.ru",
            location: data.find(contact => contact.key === 'Город')?.value ?? "Астрахань",
            description: data.find(contact => contact.key === 'Описание')?.value ?? "Мы предлагаем уютные номера в самом сердце города Астрахань.\n" +
                "                        Наш гостевой дом работает с 2010 года и за это время мы приняли более 5000 гостей!",
        }
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    await loadContacts()
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
