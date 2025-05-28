<template>
    <article class="room-card">
        <div class="image-wrapper">
            <img
                :src="room.image"
                :alt="'Фото номера ' + room.name"
                class="room-image"
                loading="lazy"
            >
            <div v-if="room.isPopular" class="badge">Популярный</div>
        </div>

        <div class="content">
            <div class="header">
                <h3 class="title">{{ room.name }}</h3>
                <div class="rating" v-if="room.rating">
                    <i
                        v-for="star in 5"
                        :key="star"
                        class="fas fa-star"
                        :class="{ 'active': star <= room.rating }"
                    ></i>
                    <span class="reviews">({{ room.reviewsCount }} отзывов)</span>
                </div>
            </div>

            <ul class="amenities">
                <li v-for="(amenity, index) in room.amenities" :key="index">
                    <i class="fas fa-check"></i>
                    {{ amenity }}
                </li>
            </ul>

            <div class="footer">
                <div class="price">
                    <span class="amount">{{ formattedPrice }}</span>
                    <span class="period">/ ночь</span>
                </div>
                <button
                    class="book-button"
                    @click="$emit('book-now', room.id)"
                >
                    <i class="fas fa-calendar-alt"></i>
                    Забронировать
                </button>
            </div>
        </div>
    </article>
</template>

<script>
export default {
    props: {
        room: {
            type: Object,
            required: true,
            validator: (value) => {
                return [
                    'id',
                    'name',
                    'price',
                    'image',
                    'amenities'
                ].every(key => key in value)
            }
        }
    },
    computed: {
        formattedPrice() {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                maximumFractionDigits: 0
            }).format(this.room.price)
        }
    }
}
</script>

<style scoped>
.room-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}

.room-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
}

.image-wrapper {
    position: relative;
    aspect-ratio: 16/9;
}

.room-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px 12px 0 0;
}

.badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #ff4757;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.content {
    padding: 1.5rem;
}

.header {
    margin-bottom: 1rem;
}

.title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3436;
    margin-bottom: 0.5rem;
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fa-star {
    color: #dfe6e9;
    font-size: 0.9rem;
}

.fa-star.active {
    color: #fdcb6e;
}

.reviews {
    color: #636e72;
    font-size: 0.85rem;
}

.amenities {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
    display: grid;
    gap: 0.75rem;
}

.amenities li {
    color: #636e72;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.fa-check {
    color: #00b894;
    font-size: 0.8rem;
}

.footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price {
    line-height: 1.2;
}

.amount {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3436;
}

.period {
    font-size: 0.9rem;
    color: #636e72;
}

.book-button {
    background: #0984e3;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.2s ease;
}

.book-button:hover {
    background: #0873c4;
}

.book-button i {
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .book-button {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }

    .amount {
        font-size: 1.25rem;
    }
}
</style>
