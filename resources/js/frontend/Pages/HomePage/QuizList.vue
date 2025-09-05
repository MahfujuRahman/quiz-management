<template>
    <div>
        <!-- Hero Section -->
        <section class="hero-section bg-gradient-primary py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12 text-center">
                        <h1 class="display-4 font-weight-bold text-white mb-4">
                            অনলাইন পরীক্ষা সিস্টেম
                        </h1>
                        <p class="lead text-white mb-4">
                            যেকোনো সময়, যেকোনো জায়গা থেকে নিরাপদ ও স্বচ্ছ পরীক্ষায় অংশগ্রহণ করুন
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Available Quizzes Section -->
        <section class="py-5">
            <div class="container">
                <h2 class="text-center mb-5">সক্রিয় পরীক্ষাসমূহ</h2>

                <div class="row" v-if="loading">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">লোড হচ্ছে...</span>
                        </div>
                    </div>
                </div>

                <div class="row" v-else-if="quizzes.length > 0">
                    <div class="col-lg-4 col-md-6 mb-4" v-for="quiz in quizzes" :key="quiz.id">
                        <div class="card h-100 shadow quiz-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">{{ quiz.title }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text" v-if="quiz.description">{{ quiz.description }}</p>

                                <div class="quiz-info">
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-clock"></i>
                                        শুরু: {{ formatDateTime(quiz.exam_start_datetime) }}
                                    </small>
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-clock-o"></i>
                                        শেষ: {{ formatDateTime(quiz.exam_end_datetime) }}
                                    </small>
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-question-circle"></i>
                                        প্রশ্ন সংখ্যা: {{ quiz.total_question }}
                                    </small>
                                    <small class="text-muted d-block mb-3">
                                        <i class="fas fa-star"></i>
                                        পূর্ণমান: {{ quiz.total_mark }}
                                    </small>
                                </div>

                                <div class="quiz-status mb-3">
                                    <span v-if="getQuizStatus(quiz) === 'upcoming'" class="badge badge-warning">
                                        <i class="fas fa-hourglass-start"></i> শীঘ্রই শুরু
                                    </span>
                                    <span v-else-if="getQuizStatus(quiz) === 'ongoing'" class="badge badge-success">
                                        <i class="fas fa-play-circle"></i> চলমান
                                    </span>
                                    <span v-else-if="getQuizStatus(quiz) === 'ended'" class="badge badge-danger">
                                        <i class="fas fa-stop-circle"></i> সমাপ্ত
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button v-if="getQuizStatus(quiz) === 'ongoing'" @click="joinQuiz(quiz)"
                                    class="btn btn-success btn-block">
                                    <i class="fas fa-play"></i> পরীক্ষায় অংশগ্রহণ করুন
                                </button>
                                <button v-else-if="getQuizStatus(quiz) === 'upcoming'" class="btn btn-warning btn-block"
                                    disabled>
                                    <i class="fas fa-clock"></i> {{ getCountdown(quiz.exam_start_datetime) }}
                                </button>
                                <button v-else class="btn btn-secondary btn-block" disabled>
                                    <i class="fas fa-times"></i> পরীক্ষা সমাপ্ত
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" v-else>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> বর্তমানে কোনো সক্রিয় পরীক্ষা নেই।
                        </div>
                    </div>
                </div>
            </div>
        </section>


    </div>
</template>

<script>
import moment from 'moment';
import axios from 'axios';

export default {
    data() {
        return {
            quizzes: [],
            loading: true,
            directQuizId: '',
            countdownIntervals: {}
        }
    },

    async created() {
        await this.fetchQuizzes();
        this.startCountdownUpdates();
    },

    beforeUnmount() {
        // Clear all intervals
        Object.values(this.countdownIntervals).forEach(interval => {
            clearInterval(interval);
        });
    },

    methods: {
        async fetchQuizzes() {
            try {
                this.loading = true;
                const response = await axios.get('/public-quizzes');
                if (response.status === 200) {
                    this.quizzes = response.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching quizzes:', error);
                window.s_error('পরীক্ষার তালিকা লোড করতে সমস্যা হয়েছে');
            } finally {
                this.loading = false;
            }
        },

        formatDateTime(datetime) {
            return moment(datetime).format('DD/MM/YYYY hh:mm A');
        },

        getQuizStatus(quiz) {
            const now = moment();
            const startTime = moment(quiz.exam_start_datetime);
            const endTime = moment(quiz.exam_end_datetime);

            if (now.isBefore(startTime)) {
                return 'upcoming';
            } else if (now.isAfter(endTime)) {
                return 'ended';
            } else {
                return 'ongoing';
            }
        },

        getCountdown(startDateTime) {
            const now = moment();
            const startTime = moment(startDateTime);
            const duration = moment.duration(startTime.diff(now));

            if (duration.asMilliseconds() <= 0) {
                return 'শুরু হয়েছে';
            }

            const days = Math.floor(duration.asDays());
            const hours = duration.hours();
            const minutes = duration.minutes();
            const seconds = duration.seconds();

            if (days > 0) {
                return `${days} দিন ${hours} ঘন্টা`;
            } else if (hours > 0) {
                return `${hours} ঘন্টা ${minutes} মিনিট`;
            } else if (minutes > 0) {
                return `${minutes} মিনিট ${seconds} সেকেন্ড`;
            } else {
                return `${seconds} সেকেন্ড`;
            }
        },

        startCountdownUpdates() {
            // Update countdowns every second
            setInterval(() => {
                this.$forceUpdate();
            }, 1000);
        },

        joinQuiz(quiz) {
            // Store quiz info and redirect to registration
            localStorage.setItem('selectedQuiz', JSON.stringify(quiz));
            this.$inertia.visit(`/quiz-register`);
        },

        async joinDirectQuiz() {
            if (!this.directQuizId.trim()) return;

            try {
                // Validate quiz ID
                const response = await axios.post('/public-quizzes/validate-code', {
                    quiz: this.directQuizId
                });
                if (response.data.statusCode == 200) {
                    const data = await response.data.data;
                    this.joinQuiz(data.quiz);
                } else {
                    window.s_error('পরীক্ষা খুঁজে পাওয়া যায়নি');
                }
            } catch (error) {
                console.error('Error validating quiz:', error);
                window.s_error(error.response.message || 'পরীক্ষা খুঁজে পাওয়া যায়নি');
            }
        }
    }
}
</script>

<style scoped>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* min-height: 500px; */
}

.quiz-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.quiz-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.quiz-info small {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quiz-info i {
    width: 16px;
    text-align: center;
}

.badge {
    padding: 8px 12px;
    font-size: 0.85rem;
}

.badge i {
    margin-right: 5px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.input-group {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 25px;
    overflow: hidden;
}

.input-group .form-control {
    border: none;
    padding: 12px 20px;
}

.input-group .btn {
    border: none;
    padding: 12px 20px;
}

.card {
    border-radius: 15px;
    overflow: hidden;
}

.card-header {
    border-radius: 0;
    border-bottom: none;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

@media (max-width: 768px) {
    .hero-section {
        text-align: center;
        padding: 3rem 0;
    }

    .display-4 {
        font-size: 2rem;
    }

    .quiz-card {
        margin-bottom: 2rem;
    }
}

/* Animation for quiz cards */
.quiz-card {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Status badges styling */
.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

/* Button styling */
.btn-block {
    border-radius: 25px;
    padding: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-primary:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}
</style>
