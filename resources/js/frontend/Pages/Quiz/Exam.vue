<template>
    <div class="quiz-exam" @contextmenu.prevent @selectstart.prevent @dragstart.prevent>
        <!-- Warning Modal -->
        <div class="modal fade" id="warningModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-danger">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle"></i> সতর্কতা!
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <h4 class="text-danger mb-3">{{ warningMessage }}</h4>
                        <p>আপনার পরীক্ষা স্বয়ংক্রিয়ভাবে জমা দেওয়া হবে!</p>
                        <div class="countdown-warning">
                            <span class="countdown-number">{{ warningCountdown }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Interface -->
        <div class="exam-container" v-if="examStarted && !examCompleted">
            <!-- Header -->
            <div class="exam-header">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="exam-title">{{ quiz.title }}</h5>
                            <small class="text-muted">{{ studentInfo.name }}</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="question-counter">
                                প্রশ্ন {{ currentQuestionIndex + 1 }} / {{ questions.length }}
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <!-- Timer -->
                            <div class="timer" :class="{ 'timer-warning': isWarningTime, 'timer-critical': isCriticalTime }">
                                <i class="fas fa-clock"></i>
                                {{ formatTime(remainingTime) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Content -->
            <div class="question-container">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="question-card">
                                <div class="question-header">
                                    <span class="question-number">প্রশ্ন {{ currentQuestionIndex + 1 }}</span>
                                    <span class="question-marks">নম্বর: {{ currentQuestion.mark }}</span>
                                </div>
                                
                                <div class="question-content">
                                    <h4 v-html="currentQuestion.title"></h4>
                                </div>
                                
                                <!-- Options -->
                                <div class="options-container">
                                    <div 
                                        v-for="option in currentQuestion.quiz_question_options" 
                                        :key="option.id"
                                        class="option-item"
                                        :class="{ 'selected': isOptionSelected(option.id) }"
                                        @click="selectOption(option.id)"
                                    >
                                        <div class="option-radio">
                                            <input 
                                                :type="currentQuestion.is_multiple ? 'checkbox' : 'radio'"
                                                :name="'question_' + currentQuestion.id"
                                                :value="option.id"
                                                :checked="isOptionSelected(option.id)"
                                                @change="selectOption(option.id)"
                                            >
                                        </div>
                                        <div class="option-content">
                                            <span v-if="option.title">{{ option.title }}</span>
                                            <img v-if="option.image" :src="option.image" alt="Option" class="option-image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="exam-navigation">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <button 
                                class="btn btn-outline-secondary" 
                                @click="previousQuestion"
                                :disabled="currentQuestionIndex === 0"
                            >
                                <i class="fas fa-chevron-left"></i> পূর্ববর্তী
                            </button>
                        </div>
                        <div class="col-md-6 text-right">
                            <button 
                                v-if="currentQuestionIndex < questions.length - 1"
                                class="btn btn-primary" 
                                @click="nextQuestion"
                            >
                                পরবর্তী <i class="fas fa-chevron-right"></i>
                            </button>
                            <button 
                                v-else
                                class="btn btn-success" 
                                @click="finishExam"
                            >
                                <i class="fas fa-check-circle"></i> পরীক্ষা শেষ করুন
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Grid -->
            <div class="question-grid-sidebar">
                <div class="grid-header">
                    <h6>প্রশ্ন নেভিগেশন</h6>
                </div>
                <div class="question-grid">
                    <button
                        v-for="(question, index) in questions"
                        :key="question.id"
                        class="grid-item"
                        :class="{
                            'current': index === currentQuestionIndex,
                            'answered': hasAnswer(question.id),
                            'unanswered': !hasAnswer(question.id)
                        }"
                        @click="goToQuestion(index)"
                    >
                        {{ index + 1 }}
                    </button>
                </div>
                <div class="grid-legend">
                    <div class="legend-item">
                        <span class="legend-color answered"></span>
                        <small>উত্তর দেওয়া</small>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color current"></span>
                        <small>বর্তমান</small>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color unanswered"></span>
                        <small>অনুত্তরিত</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Completed -->
        <div class="exam-completed" v-if="examCompleted">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 text-center">
                        <div class="completion-card">
                            <div class="completion-icon">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <h2>পরীক্ষা সম্পন্ন!</h2>
                            <p class="lead">{{ completionMessage || 'আপনার উত্তরপত্র সফলভাবে জমা দেওয়া হয়েছে।' }}</p>
                            
                            <div class="exam-summary">
                                <div class="summary-item">
                                    <span class="label">মোট প্রশ্ন:</span>
                                    <span class="value">{{ questions.length }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">উত্তর দেওয়া:</span>
                                    <span class="value">{{ answeredQuestions }}</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">সময় লেগেছে:</span>
                                    <span class="value">{{ formatDuration(examDuration) }}</span>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary btn-lg mt-4" @click="goToHome">
                                <i class="fas fa-home"></i> হোম পেজে যান
                            </button>
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
            questions: [],
            studentInfo: {},
            currentQuestionIndex: 0,
            answers: {},
            examStarted: false,
            examCompleted: false,
            examStartTime: null,
            examDuration: 0,
            remainingTime: 0,
            timer: null,
            warningShown: false,
            criticalWarningShown: false,
            warningMessage: '',
            warningCountdown: 0,
            warningTimer: null,
            completionMessage: '',
            tabSwitchCount: 0,
            maxTabSwitches: 3,
            fullscreenExitCount: 0,
            maxFullscreenExits: 2
        }
    },
    
    computed: {
        currentQuestion() {
            return this.questions[this.currentQuestionIndex] || {};
        },
        
        isWarningTime() {
            return this.remainingTime <= 300; // 5 minutes
        },
        
        isCriticalTime() {
            return this.remainingTime <= 60; // 1 minute
        },
        
        answeredQuestions() {
            return Object.keys(this.answers).length;
        }
    },
    
    async created() {
        await this.initializeExam();
        this.setupSecurityMeasures();
    },
    
    mounted() {
        this.enterFullscreen();
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
        document.addEventListener('keydown', this.preventKeyboardShortcuts);
    },
    
    beforeUnmount() {
        this.cleanupExam();
    },
    
    methods: {
        async initializeExam() {
            try {
                const quizId = this.$route.params.id;
                const sessionToken = sessionStorage.getItem('examSession');
                
                if (!sessionToken) {
                    this.$router.push(`/quiz/${quizId}/register`);
                    return;
                }
                
                const response = await fetch(`/api/v1/quizzes/quiz_questions?quiz_id=${quizId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${sessionToken}`
                    }
                });
             
                
                if (response.ok) {
                    const data = await response.json();
                    this.quiz = data.quiz;
                    this.questions = data.questions;
                    this.studentInfo = data.student_info;
                    this.completionMessage = data.completion_message;
                    
                    this.startExam();
                } else {
                    this.$toast.error('পরীক্ষা শুরু করতে সমস্যা');
                    this.$router.push('/');
                }
            } catch (error) {
                console.error('Exam initialization error:', error);
                this.$toast.error('সংযোগে সমস্যা');
            }
        },
        
        startExam() {
            this.examStarted = true;
            this.examStartTime = moment();
            
            // Calculate remaining time
            const endTime = moment(this.quiz.exam_end_datetime);
            const now = moment();
            this.remainingTime = Math.max(0, Math.floor(endTime.diff(now) / 1000));
            
            this.startTimer();
        },
        
        startTimer() {
            this.timer = setInterval(() => {
                this.remainingTime--;
                
                // 5 minute warning
                if (this.remainingTime === 300 && !this.warningShown) {
                    this.showWarning('পরীক্ষা শেষ হতে ৫ মিনিট বাকি!');
                    this.warningShown = true;
                }
                
                // 1 minute critical warning
                if (this.remainingTime === 60 && !this.criticalWarningShown) {
                    this.showWarning('পরীক্ষা শেষ হতে ১ মিনিট বাকি!');
                    this.criticalWarningShown = true;
                }
                
                // Time up
                if (this.remainingTime <= 0) {
                    this.autoSubmitExam('সময় শেষ হয়ে গেছে');
                }
            }, 1000);
        },
        
        showWarning(message) {
            this.warningMessage = message;
            this.warningCountdown = 5;
            $('#warningModal').modal('show');
            
            this.warningTimer = setInterval(() => {
                this.warningCountdown--;
                if (this.warningCountdown <= 0) {
                    $('#warningModal').modal('hide');
                    clearInterval(this.warningTimer);
                }
            }, 1000);
        },
        
        setupSecurityMeasures() {
            // Prevent right-click context menu
            document.addEventListener('contextmenu', e => e.preventDefault());
            
            // Prevent text selection and drag
            document.addEventListener('selectstart', e => e.preventDefault());
            document.addEventListener('dragstart', e => e.preventDefault());
            
            // Prevent screenshots (partial prevention)
            document.addEventListener('keydown', this.preventScreenshot);
            
            // Disable copy, paste, cut
            document.addEventListener('copy', e => e.preventDefault());
            document.addEventListener('paste', e => e.preventDefault());
            document.addEventListener('cut', e => e.preventDefault());
        },
        
        preventKeyboardShortcuts(e) {
            // Prevent common shortcuts
            if (e.ctrlKey || e.metaKey) {
                if (['c', 'v', 'x', 'a', 's', 'p', 'u', 'r', 'f', 'h', 'i', 'j', 'k', 'l', 'd', 't', 'w', 'n'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                }
            }
            
            // Prevent F12, F5, etc.
            if ([112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123].includes(e.keyCode)) {
                e.preventDefault();
            }
            
            // Prevent Alt+Tab, Alt+F4
            if (e.altKey && ['Tab', 'F4'].includes(e.key)) {
                e.preventDefault();
            }
        },
        
        preventScreenshot(e) {
            // Detect common screenshot shortcuts
            if ((e.ctrlKey && e.shiftKey && e.key === 'S') || 
                (e.key === 'PrintScreen') ||
                (e.metaKey && e.shiftKey && ['3', '4', '5'].includes(e.key))) {
                e.preventDefault();
                this.autoSubmitExam('স্ক্রিনশট নেওয়ার চেষ্টা করা হয়েছে');
            }
        },
        
        handleVisibilityChange() {
            if (document.hidden && this.examStarted && !this.examCompleted) {
                this.tabSwitchCount++;
                
                if (this.tabSwitchCount >= this.maxTabSwitches) {
                    this.autoSubmitExam('অনুমোদিত সংখ্যক ট্যাব পরিবর্তনের সীমা অতিক্রম');
                } else {
                    this.showWarning(`ট্যাব পরিবর্তন সনাক্ত! আরো ${this.maxTabSwitches - this.tabSwitchCount} বার পরিবর্তন করলে পরীক্ষা স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে।`);
                }
            }
        },
        
        enterFullscreen() {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            }
            
            document.addEventListener('fullscreenchange', this.handleFullscreenChange);
        },
        
        handleFullscreenChange() {
            if (!document.fullscreenElement && this.examStarted && !this.examCompleted) {
                this.fullscreenExitCount++;
                
                if (this.fullscreenExitCount >= this.maxFullscreenExits) {
                    this.autoSubmitExam('ফুলস্ক্রিন মোড থেকে বের হওয়া');
                } else {
                    this.showWarning('ফুলস্ক্রিন মোড বজায় রাখুন!');
                    setTimeout(() => this.enterFullscreen(), 1000);
                }
            }
        },
        
        selectOption(optionId) {
            const questionId = this.currentQuestion.id;
            
            if (this.currentQuestion.is_multiple) {
                // Multiple choice
                if (!this.answers[questionId]) {
                    this.answers[questionId] = [];
                }
                
                const index = this.answers[questionId].indexOf(optionId);
                if (index > -1) {
                    this.answers[questionId].splice(index, 1);
                } else {
                    this.answers[questionId].push(optionId);
                }
            } else {
                // Single choice
                this.answers[questionId] = [optionId];
            }
            
            this.$forceUpdate();
        },
        
        isOptionSelected(optionId) {
            const questionId = this.currentQuestion.id;
            const answer = this.answers[questionId];
            return answer && answer.includes(optionId);
        },
        
        hasAnswer(questionId) {
            return this.answers[questionId] && this.answers[questionId].length > 0;
        },
        
        nextQuestion() {
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
            }
        },
        
        previousQuestion() {
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
            }
        },
        
        goToQuestion(index) {
            this.currentQuestionIndex = index;
        },
        
        async finishExam() {
            if (confirm('আপনি কি নিশ্চিত যে পরীক্ষা শেষ করতে চান?')) {
                await this.submitExam('স্বাভাবিক সমাপ্তি');
            }
        },
        
        async autoSubmitExam(reason) {
            await this.submitExam(reason);
        },
        
        async submitExam(reason) {
            try {
                this.examDuration = moment().diff(this.examStartTime, 'seconds');
                
                const sessionToken = sessionStorage.getItem('examSession');
                const response = await fetch('/api/v1/quiz/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${sessionToken}`
                    },
                    body: JSON.stringify({
                        quiz_id: this.quiz.id,
                        answers: this.answers,
                        duration: this.examDuration,
                        submit_reason: reason
                    })
                });
                
                if (response.ok) {
                    this.examCompleted = true;
                    this.cleanupExam();
                } else {
                    this.$toast.error('উত্তরপত্র জমা দিতে সমস্যা');
                }
            } catch (error) {
                console.error('Submit error:', error);
                this.$toast.error('সংযোগে সমস্যা');
            }
        },
        
        cleanupExam() {
            if (this.timer) {
                clearInterval(this.timer);
            }
            if (this.warningTimer) {
                clearInterval(this.warningTimer);
            }
            
            document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            document.removeEventListener('keydown', this.preventKeyboardShortcuts);
            document.removeEventListener('fullscreenchange', this.handleFullscreenChange);
            
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        },
        
        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
        
        formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            let duration = '';
            if (hours > 0) duration += `${hours} ঘন্টা `;
            if (minutes > 0) duration += `${minutes} মিনিট `;
            duration += `${secs} সেকেন্ড`;
            
            return duration;
        },
        
        goToHome() {
            sessionStorage.removeItem('examSession');
            this.$router.push('/');
        }
    }
}
</script>

<style scoped>
.quiz-exam {
    min-height: 100vh;
    background: #f8f9fa;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.exam-header {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.exam-title {
    margin: 0;
    color: #2c3e50;
}

.question-counter {
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
}

.timer {
    background: #28a745;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 1.1rem;
}

.timer-warning {
    background: #ffc107;
    color: #212529;
}

.timer-critical {
    background: #dc3545;
    color: white;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.question-container {
    padding: 2rem 0;
    min-height: 60vh;
}

.question-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.question-number {
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 600;
}

.question-marks {
    background: #28a745;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.9rem;
}

.question-content h4 {
    color: #2c3e50;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.options-container {
    display: grid;
    gap: 1rem;
}

.option-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.option-item:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.option-item.selected {
    border-color: #28a745;
    background: #d4edda;
}

.option-radio {
    margin-right: 1rem;
}

.option-radio input {
    transform: scale(1.2);
}

.option-content {
    flex: 1;
    display: flex;
    align-items: center;
}

.option-image {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
    border-radius: 5px;
}

.exam-navigation {
    background: white;
    padding: 1rem 0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    position: sticky;
    bottom: 0;
}

.question-grid-sidebar {
    position: fixed;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    padding: 1rem;
    max-width: 200px;
    z-index: 1000;
}

.grid-header h6 {
    margin: 0 0 1rem 0;
    text-align: center;
    color: #2c3e50;
}

.question-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.grid-item {
    width: 30px;
    height: 30px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.grid-item.current {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.grid-item.answered {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.grid-item.unanswered {
    background: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

.grid-legend {
    font-size: 0.75rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.25rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 0.5rem;
}

.legend-color.answered { background: #28a745; }
.legend-color.current { background: #007bff; }
.legend-color.unanswered { background: #ffc107; }

.exam-completed {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.completion-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

.completion-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.exam-summary {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 2rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.summary-item:last-child {
    border-bottom: none;
}

.label {
    font-weight: 600;
    color: #6c757d;
}

.value {
    font-weight: bold;
    color: #2c3e50;
}

.countdown-warning {
    font-size: 3rem;
    color: #dc3545;
    margin: 1rem 0;
}

.countdown-number {
    display: inline-block;
    width: 60px;
    height: 60px;
    line-height: 56px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .question-grid-sidebar {
        position: static;
        transform: none;
        margin: 1rem auto;
        max-width: 100%;
    }
    
    .exam-header .row {
        text-align: center;
    }
    
    .exam-header .col-md-4 {
        margin-bottom: 0.5rem;
    }
    
    .question-card {
        padding: 1rem;
    }
    
    .completion-card {
        margin: 1rem;
        padding: 2rem 1rem;
    }
}

/* Prevent text selection and drag */
* {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    -webkit-user-drag: none;
    -khtml-user-drag: none;
    -moz-user-drag: none;
    -o-user-drag: none;
}

/* Allow text selection only in input fields */
input, textarea {
    -webkit-user-select: text;
    -khtml-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}
</style>
