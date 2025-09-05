<template>
    <div class="quiz-registration">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Quiz Info Card -->
                    <div class="card shadow-lg mb-4" v-if="quiz">
                        <div class="card-header bg-primary text-white text-center">
                            <img v-if="quiz.image" :src="quiz.image" alt="Quiz Image" class="img-fluid mb-3"
                                style="max-height: 250px; object-fit: cover; border-radius: 10px;">
                            <h3 class="mb-0">{{ quiz.title }}</h3>
                            <small>পরীক্ষা নিবন্ধন</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-clock text-primary"></i>
                                        <span>শুরুর সময়: {{ formatDateTime(quiz.exam_start_datetime) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-clock text-danger"></i>
                                        <span>শেষের সময়: {{ formatDateTime(quiz.exam_end_datetime) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-question-circle text-info"></i>
                                        <span>প্রশ্ন সংখ্যা: {{ quiz.total_question }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-star text-warning"></i>
                                        <span>পূর্ণমান: {{ quiz.total_mark }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Countdown Timer -->
                            <div class="countdown-timer text-center mt-3" v-if="timeToStart > 0">
                                <h5 class="text-muted">পরীক্ষা শুরু হতে বাকি</h5>
                                <div class="countdown-display">
                                    <div class="countdown-item">
                                        <span class="countdown-number">{{ countdown.hours }}</span>
                                        <span class="countdown-label">ঘন্টা</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number">{{ countdown.minutes }}</span>
                                        <span class="countdown-label">মিনিট</span>
                                    </div>
                                    <div class="countdown-item">
                                        <span class="countdown-number">{{ countdown.seconds }}</span>
                                        <span class="countdown-label">সেকেন্ড</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-user-edit"></i> আপনার তথ্য পূরণ করুন
                            </h4>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submitRegistration">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">
                                            <i class="fas fa-user"></i> পূর্ণ নাম
                                        </label>
                                        <input type="text" class="form-control" v-model="studentInfo.name"
                                            placeholder="আপনার পূর্ণ নাম লিখুন" required>
                                    </div>

                                    <!-- <div class="col-md-6 mb-3">
                                        <label class="form-label required">
                                            <i class="fas fa-users"></i> গ্রুপ/বিভাগ
                                        </label>
                                        <select class="form-control" v-model="studentInfo.group" required>
                                            <option value="">নির্বাচন করুন</option>
                                            <option value="science">বিজ্ঞান</option>
                                            <option value="commerce">ব্যবসায় শিক্ষা</option>
                                            <option value="arts">মানবিক</option>
                                            <option value="others">অন্যান্য</option>
                                        </select>
                                    </div> -->

                                    <!-- <div class="col-md-6 mb-3">
                                        <label class="form-label required">
                                            <i class="fas fa-graduation-cap"></i> শ্রেণি
                                        </label>
                                        <select class="form-control" v-model="studentInfo.class" required>
                                            <option value="">নির্বাচন করুন</option>
                                            <option value="6">ষষ্ঠ</option>
                                            <option value="7">সপ্তম</option>
                                            <option value="8">অষ্টম</option>
                                            <option value="9">নবম</option>
                                            <option value="10">দশম</option>
                                            <option value="11">একাদশ</option>
                                            <option value="12">দ্বাদশ</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">
                                            <i class="fas fa-school"></i> শিক্ষা প্রতিষ্ঠান
                                        </label>
                                        <input type="text" class="form-control" v-model="studentInfo.institution"
                                            placeholder="আপনার শিক্ষা প্রতিষ্ঠানের নাম" required>
                                    </div> -->

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label required">
                                            <i class="fas fa-phone"></i> মোবাইল নম্বর
                                        </label>
                                        <input type="tel" class="form-control" v-model="studentInfo.phone"
                                            placeholder="01XXXXXXXXX" pattern="[0-9]{11}" required>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-envelope"></i> ইমেইল (ঐচ্ছিক)
                                        </label>
                                        <input type="email" class="form-control" v-model="studentInfo.email"
                                            placeholder="your@email.com">
                                    </div>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="card bg-light border-warning mt-4">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> পরীক্ষার নিয়মাবলী
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success"></i> পরীক্ষার সময় অন্য ট্যাব খোলা
                                                যাবে না</li>
                                            <li><i class="fas fa-check text-success"></i> ২ বারের বেশি অন্য ট্যাব খুললে স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে</li>
                                            <li><i class="fas fa-check text-success"></i> ৫ সেকেন্ডের বেশি বাইরে থাকলে
                                                স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে
                                            </li>
                                            <li><i class="fas fa-check text-success"></i> স্ক্রিনশট নেওয়া যাবে না</li>
                                            <li><i class="fas fa-check text-success"></i> টেক্সট কপি করা যাবে না</li>
                                            <li><i class="fas fa-check text-success"></i> সময় শেষ হলে স্বয়ংক্রিয়ভাবে
                                                জমা হয়ে যাবে</li>
                                            <li><i class="fas fa-check text-success"></i> ৫ মিনিট আগে সতর্কতা দেওয়া হবে
                                            </li>
                                        </ul>

                                        <div class="form-check mt-3">
                                            <input type="checkbox" class="form-check-input" id="agreeTerms"
                                                v-model="agreedToTerms" required>
                                            <label class="form-check-label" for="agreeTerms">
                                                আমি উপরের সকল নিয়মাবলী মেনে চলতে সম্মত
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-5"
                                        :disabled="!canStartExam || loading">
                                        <i class="fas fa-play-circle" v-if="!loading"></i>
                                        <i class="fas fa-spinner fa-spin" v-else></i>
                                        {{ loading ? 'অপেক্ষা করুন...' : 'পরীক্ষা শুরু করুন' }}
                                    </button>

                                    <div class="mt-3" v-if="!canStartExam && timeToStart > 0">
                                        <small class="text-warning">
                                            <i class="fas fa-clock"></i>
                                            পরীক্ষা এখনও শুরু হয়নি। অপেক্ষা করুন...
                                        </small>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';

export default {
    data() {
        return {
            quiz: null,
            studentInfo: {
                name: '',
                group: '',
                class: '',
                institution: '',
                phone: '',
                email: ''
            },
            agreedToTerms: false,
            loading: false,
            timeToStart: 0,
            countdown: {
                hours: 0,
                minutes: 0,
                seconds: 0
            },
            countdownInterval: null
        }
    },

    async created() {
        await this.loadQuizInfo();
        this.startCountdown();
    },

    beforeUnmount() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    },

    computed: {
        canStartExam() {
            if (!this.quiz) return false;
            const now = moment();
            const startTime = moment(this.quiz.exam_start_datetime);
            const endTime = moment(this.quiz.exam_end_datetime);
            return now.isAfter(startTime) && now.isBefore(endTime);
        }
    },

    methods: {
        async loadQuizInfo() {
            try {
                const quizId = JSON.parse(localStorage.getItem('selectedQuiz'))?.slug;
                const response = await axios.get(`/quizzes/${quizId}`);

                if (response.data.statusCode == 200) {
                    const data = await response.data.data;
                    this.quiz = data;
                } else {
                    window.s_error('পরীক্ষার তথ্য লোড করতে সমস্যা');
                    this.$router.push('/');
                }
            } catch (error) {
                console.error('Error loading quiz:', error);
                window.s_error('সংযোগে সমস্যা হয়েছে');
            }
        },

        startCountdown() {
            this.updateCountdown();
            this.countdownInterval = setInterval(this.updateCountdown, 1000);
        },

        updateCountdown() {
            if (!this.quiz) return;

            const now = moment();
            const startTime = moment(this.quiz.exam_start_datetime);

            if (now.isBefore(startTime)) {
                const duration = moment.duration(startTime.diff(now));
                this.timeToStart = duration.asMilliseconds();

                this.countdown = {
                    hours: Math.floor(duration.asHours()),
                    minutes: duration.minutes(),
                    seconds: duration.seconds()
                };
            } else {
                this.timeToStart = 0;
                if (this.countdownInterval) {
                    clearInterval(this.countdownInterval);
                }
            }
        },

        formatDateTime(datetime) {
            return moment(datetime).format('DD/MM/YYYY hh:mm A');
        },

        async submitRegistration() {
            if (!this.canStartExam) {
                window.s_alert('পরীক্ষা এখনও শুরু হয়নি', 'warning');
                return;
            }

            try {
                this.loading = true;

                const response = await axios.post('/public-quizzes/register', {
                    quiz_id: this.quiz.id,
                    name: this.studentInfo.name,
                    email: this.studentInfo.email,
                    phone: this.studentInfo.phone,
                });

                if (response.data.statusCode == 200) {
                    const result = await response.data;
                    sessionStorage.setItem('examSession', result.data.session_token);
                    this.$inertia.visit(`/quiz`);
                } else {
                    console.log('fdsfds', response);
                    window.s_error(response.data.data.message || 'নিবন্ধনে সমস্যা হয়েছে');
                }
            } catch (error) {
                console.error('Registration error:', error);
                window.s_error('সংযোগে সমস্যা হয়েছে');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style scoped>
.quiz-registration {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 2rem 0;
}

.card {
    border-radius: 15px;
    border: none;
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.5rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
}

.info-item i {
    margin-right: 0.5rem;
    width: 20px;
    text-align: center;
}

.countdown-display {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1rem;
}

.countdown-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.countdown-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: #dc3545;
}

.countdown-label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-label.required::after {
    content: ' *';
    color: #dc3545;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    padding: 1rem 3rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.border-warning {
    border-color: #ffc107 !important;
    border-width: 2px !important;
}

@media (max-width: 768px) {
    .countdown-display {
        gap: 1rem;
    }

    .countdown-item {
        padding: 0.5rem;
    }

    .countdown-number {
        font-size: 1.5rem;
    }

    .info-item {
        font-size: 0.9rem;
    }
}
</style>
