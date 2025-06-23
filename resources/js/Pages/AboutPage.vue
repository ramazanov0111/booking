<template>
    <SpaLayout title="">
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
                    <p>Мы предлагаем уютные номера в самом сердце {{ location }}.</p>
                    <p>Наш гостевой дом работает с 2010 года и за это время мы приняли более 5000 гостей!</p>
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
                <iframe
                    :src="mapUrl"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        </section>
    </div>
    </SpaLayout>
</template>

<script>
import SpaLayout from "@/Layouts/SpaLayout.vue";

export default {
    components: {SpaLayout},
    data() {
        return {
            location: "Санкт-Петербург",
            contacts: {
                address: "ул. Примерная, д. 10",
                phone: "+7 (999) 123-45-67",
                email: "contact@guesthouse.ru"
            },
            mapUrl: "https://www.google.com/maps/embed/v1/place?key=API_KEY&q=Санкт-Петербург",
            reviews: [
                {
                    author: "Иван Петров",
                    rating: 5,
                    text: "Прекрасное место! Очень уютные номера и дружелюбный персонал.",
                    date: "2024-03-15"
                },
                // ...другие отзывы
            ],
            feedbackForm: {
                name: "",
                email: "",
                rating: 0,
                message: ""
            },
            isSubmitting: false
        };
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('ru-RU', options);
        },
        async submitFeedback() {
            this.isSubmitting = true;
            try {
                // Отправка данных на бэкенд
                await this.$axios.post('/api/feedback', this.feedbackForm);
                alert('Спасибо за ваш отзыв!');
                this.feedbackForm = { name: '', email: '', rating: 0, message: '' };
            } catch (error) {
                console.error('Ошибка отправки:', error);
                alert('Произошла ошибка при отправке формы');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
};
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
