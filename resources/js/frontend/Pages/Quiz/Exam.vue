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
            <!-- Mobile Header -->
            <div class="exam-header">
                <div class="container-fluid px-3">
                    <div class="row align-items-center">
                        <!-- Mobile: Stack vertically -->
                        <div class="col-12 col-md-4 order-1 text-center text-md-start mb-2 mb-md-0">
                            <div class="exam-info">
                                <h5 class="exam-title mb-1">{{ quiz.title }}</h5>
                                <small class="text-muted">{{ studentInfo.name }}</small>
                            </div>
                        </div>

                        <!-- Mobile: Question counter in center -->
                        <div class="col-12 col-md-4 order-2 text-center mb-2 mb-md-0">
                            <div class="question-counter">
                                <i class="fas fa-question-circle me-2"></i>
                                {{ currentQuestionIndex + 1 }} / {{ questions.length }}
                            </div>
                        </div>

                        <!-- Mobile: Timer at bottom -->
                        <div class="col-12 col-md-4 order-3 text-center text-md-end">
                            <div class="timer d-inline-flex align-items-center"
                                :class="{ 'timer-warning': isWarningTime, 'timer-critical': isCriticalTime }">
                                <i class="fas fa-clock me-2"></i>
                                <span class="fw-bold">{{ formatTime(remainingTime) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Content -->
            <div class="question-container">
                <div class="container-fluid px-3">
                    <div class="row">
                        <!-- Main Question Area -->
                        <div class="col-12 col-lg-8">
                            <div class="question-card">
                                <div class="question-header">
                                    <div
                                        class="d-flex flex-row flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                        <span class="question-number">প্রশ্ন {{ currentQuestionIndex + 1 }}</span>
                                        <span class="question-type">{{ currentQuestion.is_multiple ? 'একাধিক উত্তর' :
                                            'একক উত্তর' }}</span>
                                        <span class="question-marks">নম্বর: {{ currentQuestion.mark }}</span>
                                    </div>
                                </div>

                                <div class="question-content">
                                    <h4 class="question-text" v-html="currentQuestion.title"></h4>
                                </div>

                                <!-- Options -->
                                <div class="options-container">
                                    <div v-for="(option, index) in currentQuestion.quiz_question_options"
                                        :key="option.id" class="option-item"
                                        :class="{ 'selected': isOptionSelected(option.id) }"
                                        @click="selectOption(option.id)">
                                        <div class="option-selector">
                                            <div class="option-label">
                                                {{ String.fromCharCode(65 + index) }}
                                            </div>
                                            <div class="option-radio">
                                                <input :type="currentQuestion.is_multiple ? 'checkbox' : 'radio'"
                                                    :name="'question_' + currentQuestion.id" :value="option.id"
                                                    :checked="isOptionSelected(option.id)"
                                                    @change="selectOption(option.id)">
                                            </div>

                                        </div>
                                        <div class="option-content">
                                            <span v-if="option.title" class="option-text">{{ option.title }}</span>
                                            <div v-if="option.image" class="option-image-wrapper">
                                                <img :src="option.image" alt="Option" class="option-image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Sidebar -->
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="question-grid-sidebar sticky-top">
                                <div class="grid-header">
                                    <h6><i class="fas fa-th me-2"></i>প্রশ্ন নেভিগেশন</h6>
                                </div>
                                <div class="question-grid">
                                    <button v-for="(question, index) in questions" :key="question.id" class="grid-item"
                                        :class="{
                                            'current': index === currentQuestionIndex,
                                            'answered': hasAnswer(question.id),
                                            'unanswered': !hasAnswer(question.id)
                                        }" @click="goToQuestion(index)">
                                        {{ index + 1 }}
                                    </button>
                                </div>
                                <div class="grid-legend">
                                    <div class="legend-item">
                                        <span class="legend-color answered"></span>
                                        <small>উত্তর দেওয়া ({{ answeredQuestions }})</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color current"></span>
                                        <small>বর্তমান প্রশ্ন</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color unanswered"></span>
                                        <small>অনুত্তরিত ({{ questions.length - answeredQuestions }})</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Question Navigation -->
            <div class="mobile-question-nav d-block d-lg-none">
                <div class="container-fluid px-3">
                    <div class="mobile-nav-header">
                        <h6><i class="fas fa-th-large me-2"></i>প্রশ্ন নেভিগেশন</h6>
                        <div class="nav-stats">
                            <span class="badge bg-success me-1">উত্তরিত: {{ answeredQuestions }}</span>
                            <span class="badge bg-warning">বাকি: {{ questions.length - answeredQuestions }}</span>
                        </div>
                    </div>
                    <div class="mobile-question-grid">
                        <button v-for="(question, index) in questions" :key="question.id" class="mobile-grid-item"
                            :class="{
                                'current': index === currentQuestionIndex,
                                'answered': hasAnswer(question.id),
                                'unanswered': !hasAnswer(question.id)
                            }" @click="goToQuestion(index)">
                            {{ index + 1 }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="exam-navigation">
                <div class="container-fluid px-3">
                    <div class="row align-items-center">
                        <div class="col-6 col-sm-4">
                            <button class="btn btn-outline-secondary btn-nav" @click="previousQuestion"
                                :disabled="currentQuestionIndex === 0">
                                <i class="fas fa-chevron-left me-2"></i>
                                <span class="d-none d-sm-inline">পূর্ববর্তী</span>
                                <span class="d-inline d-sm-none">পূর্ব</span>
                            </button>
                        </div>

                        <div class="col-12 col-sm-4 order-3 order-sm-2 text-center mt-2 mt-sm-0">
                            <div class="question-progress">
                                <div class="progress-bar-wrapper">
                                    <div class="progress-bar"
                                        :style="{ width: ((currentQuestionIndex + 1) / questions.length) * 100 + '%' }">
                                    </div>
                                </div>
                                <small class="progress-text">{{ Math.round(((currentQuestionIndex + 1) /
                                    questions.length) * 100) }}% সম্পন্ন</small>
                            </div>
                        </div>

                        <div class="col-6 col-sm-4 order-2 order-sm-3 text-end">
                            <button v-if="currentQuestionIndex < questions.length - 1" class="btn btn-primary btn-nav"
                                @click="nextQuestion">
                                <span class="d-none d-sm-inline">পরবর্তী</span>
                                <span class="d-inline d-sm-none">পর</span>
                                <i class="fas fa-chevron-right ms-2"></i>
                            </button>
                            <button v-else class="btn btn-success btn-nav" @click="finishExam">
                                <i class="fas fa-check-circle me-2"></i>
                                <span class="d-none d-sm-inline">পরীক্ষা শেষ</span>
                                <span class="d-inline d-sm-none">শেষ</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Completed -->
        <div class="exam-completed" v-if="examCompleted">
            <div class="container-fluid px-3">
                <div class="row justify-content-center align-items-center min-vh-100">
                    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                        <div class="completion-card text-center">
                            <div class="completion-icon mb-4">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                            <h2 class="completion-title">পরীক্ষা সম্পন্ন!</h2>
                            <p class="completion-message">{{ completionMessage || 'আপনার উত্তরপত্র সফলভাবে জমাদেওয়াহয়েছে।' }}</p>

                            <div class="exam-summary">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-4">
                                        <div class="summary-item">
                                            <div class="summary-icon">
                                                <i class="fas fa-question-circle"></i>
                                            </div>
                                            <div class="summary-info">
                                                <div class="summary-value">{{ questions.length }}</div>
                                                <div class="summary-label">মোট প্রশ্ন</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="summary-item">
                                            <div class="summary-icon bg-success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="summary-info">
                                                <div class="summary-value">{{ answeredQuestions }}</div>
                                                <div class="summary-label">উত্তর দেওয়া</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="summary-item">
                                            <div class="summary-icon bg-warning">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div class="summary-info">
                                                <div class="summary-value">{{ formatDuration(examDuration) }}</div>
                                                <div class="summary-label">সময় লেগেছে</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary btn-lg mt-4 w-100 w-sm-auto" @click="goToHome">
                                <i class="fas fa-home me-2"></i>হোম পেজে যান
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
import axios from 'axios';

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
            // Security tracking with persistence
            tabSwitchCount: 0,
            maxTabSwitches: 3,
            fullscreenExitCount: 0,
            maxFullscreenExits: 2,
            screenshotAttempts: 0,
            maxScreenshotAttempts: 2,
            keyboardViolations: 0,
            maxKeyboardViolations: 5,
            // Tab absence tracking
            tabAwayStartTime: null,
            tabAwayTimer: null,
            maxTabAwayTime: 5000, // 5 seconds
            isTabAway: false,
            // Blur tracking
            windowBlurCount: 0,
            maxWindowBlurs: 3,
            // Console detection
            consoleCheckInterval: null,
            // Network monitoring
            networkRequestCount: 0,
            maxNetworkRequests: 10,
            // Copy/paste attempts
            copyPasteAttempts: 0,
            maxCopyPasteAttempts: 3,
            // Security state key for persistence
            securityStateKey: null,
            // Performance optimization
            saveStateTimeout: null
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
        this.initializeSecurityState();
        this.setupSecurityMeasures();
    },

    mounted() {
        this.enterFullscreen();
        this.setupEventListeners();

        // Delayed initialization of heavy security features
        setTimeout(() => {
            this.startConsoleDetection();
            this.detectDevTools();
            this.monitorNetworkRequests();
        }, 1000); // Delay heavy security by 1 second
    },

    beforeUnmount() {
        this.cleanupExam();
    },

    methods: {
        async initializeExam() {
            try {
                const quizId = JSON.parse(localStorage.getItem('selectedQuiz'))?.id;
                const sessionToken = sessionStorage.getItem('examSession');

                if (!sessionToken) {
                    this.$router.push(`/quiz-register`);
                    return;
                }

                // Create unique security state key for this exam session
                this.securityStateKey = `examSecurity_${quizId}_${sessionToken.substring(0, 10)}`;

                const response = await axios.get(`/quizzes/quiz_questions?quiz_id=${quizId}`, {
                    headers: {
                        'Authorization': `Bearer ${sessionToken}`
                    }
                });

                if (response.data.statusCode === 200) {
                    const data = response.data.data;
                    this.quiz = data.quiz || data;
                    this.questions = data.questions || data.quiz_questions || [];
                    this.studentInfo = data.student_info[0] || { name: 'Student', email: 'student@example.com' };
                    this.completionMessage = data.completion_message || 'পরীক্ষা সম্পন্ন হয়েছে।';

                    this.startExam();
                } else {
                    window.s_error(response.data.message || 'পরীক্ষা শুরু করতে সমস্যা');
                    this.$router.push('/');
                }
            } catch (error) {
                console.error('Exam initialization error:', error);
                window.s_error('সংযোগে সমস্যা');
            }
        },

        initializeSecurityState() {
            try {
                const savedState = localStorage.getItem(this.securityStateKey);
                if (savedState) {
                    const securityData = JSON.parse(savedState);
                    this.tabSwitchCount = securityData.tabSwitchCount || 0;
                    this.fullscreenExitCount = securityData.fullscreenExitCount || 0;
                    this.screenshotAttempts = securityData.screenshotAttempts || 0;
                    this.keyboardViolations = securityData.keyboardViolations || 0;
                    this.windowBlurCount = securityData.windowBlurCount || 0;
                    this.copyPasteAttempts = securityData.copyPasteAttempts || 0;
                    this.networkRequestCount = securityData.networkRequestCount || 0;
                }
            } catch (error) {
                console.warn('Failed to load security state:', error);
            }
        },

        saveSecurityState() {
            // Throttle security state saving to reduce localStorage writes
            if (this.saveStateTimeout) {
                clearTimeout(this.saveStateTimeout);
            }

            this.saveStateTimeout = setTimeout(() => {
                try {
                    const securityData = {
                        tabSwitchCount: this.tabSwitchCount,
                        fullscreenExitCount: this.fullscreenExitCount,
                        screenshotAttempts: this.screenshotAttempts,
                        keyboardViolations: this.keyboardViolations,
                        windowBlurCount: this.windowBlurCount,
                        copyPasteAttempts: this.copyPasteAttempts,
                        networkRequestCount: this.networkRequestCount,
                        lastUpdate: Date.now()
                    };
                    localStorage.setItem(this.securityStateKey, JSON.stringify(securityData));
                } catch (error) {
                    console.warn('Failed to save security state:', error);
                }
            }, 100); // Throttle to max 10 writes per second
        },

        setupEventListeners() {
            // Essential event listeners only
            document.addEventListener('visibilitychange', this.handleVisibilityChange);
            document.addEventListener('keydown', this.preventKeyboardShortcuts);
            document.addEventListener('fullscreenchange', this.handleFullscreenChange);

            // Optional listeners with throttling
            let blurTimeout;
            window.addEventListener('blur', () => {
                clearTimeout(blurTimeout);
                blurTimeout = setTimeout(() => {
                    this.handleWindowBlur();
                }, 100); // Throttle blur events
            });

            // Basic security events
            document.addEventListener('contextmenu', this.preventContextMenu);
            document.addEventListener('selectstart', this.preventSelection);
            document.addEventListener('dragstart', this.preventDrag);

            // Copy/paste with throttling
            let copyPasteTimeout;
            const throttledCopyPaste = (handler) => (e) => {
                clearTimeout(copyPasteTimeout);
                copyPasteTimeout = setTimeout(() => handler(e), 50);
            };

            document.addEventListener('copy', throttledCopyPaste(this.handleCopyAttempt));
            document.addEventListener('paste', throttledCopyPaste(this.handlePasteAttempt));
            document.addEventListener('cut', throttledCopyPaste(this.handleCutAttempt));

            // Page unload warning
            window.addEventListener('beforeunload', this.handleBeforeUnload);
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
            // Additional security hardening
            this.disableContextMenu();
            this.preventTextSelection();
            this.blockKeyboardShortcuts();
            this.hideScrollbars();
            this.preventZoom();
        },

        disableContextMenu() {
            document.addEventListener('contextmenu', this.preventContextMenu);
        },

        preventContextMenu(e) {
            e.preventDefault();
            e.stopPropagation();
            this.logSecurityViolation('Context menu access attempt');
            return false;
        },

        preventTextSelection() {
            document.addEventListener('selectstart', this.preventSelection);
            document.addEventListener('dragstart', this.preventDrag);
        },

        preventSelection(e) {
            if (!['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        },

        preventDrag(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        },

        blockKeyboardShortcuts() {
            document.addEventListener('keydown', this.preventKeyboardShortcuts);
        },

        hideScrollbars() {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        },

        preventZoom() {
            const self = this; // Store reference to component instance

            document.addEventListener('wheel', (e) => {
                if (e.ctrlKey) {
                    e.preventDefault();
                    self.logSecurityViolation('Zoom attempt detected');
                }
            }, { passive: false });

            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '-' || e.key === '0')) {
                    e.preventDefault();
                    self.logSecurityViolation('Keyboard zoom attempt');
                }
            });
        },

        preventKeyboardShortcuts(e) {
            // Prevent common shortcuts
            if (e.ctrlKey || e.metaKey) {
                const forbiddenKeys = ['c', 'v', 'x', 'a', 's', 'p', 'u', 'r', 'f', 'h', 'i', 'j', 'k', 'l', 'd', 't', 'w', 'n', 'e', 'o', 'z', 'y'];
                if (forbiddenKeys.includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    e.stopPropagation();

                    this.keyboardViolations++;
                    this.saveSecurityState();
                    this.logSecurityViolation(`Keyboard shortcut blocked: Ctrl+${e.key}`);

                    if (this.keyboardViolations >= this.maxKeyboardViolations) {
                        this.autoSubmitExam('অতিরিক্ত কীবোর্ড শর্টকাট ব্যবহার');
                    }
                    return false;
                }
            }

            // Prevent F-keys
            if (e.keyCode >= 112 && e.keyCode <= 123) {
                e.preventDefault();
                e.stopPropagation();

                this.keyboardViolations++;
                this.saveSecurityState();
                this.logSecurityViolation(`Function key blocked: F${e.keyCode - 111}`);

                // F12 is particularly suspicious (DevTools)
                if (e.keyCode === 123) {
                    this.autoSubmitExam('DevTools অ্যাক্সেসের চেষ্টা');
                }
                return false;
            }

            // Prevent Alt+Tab, Alt+F4
            if (e.altKey && ['Tab', 'F4'].includes(e.key)) {
                e.preventDefault();
                e.stopPropagation();

                this.keyboardViolations++;
                this.saveSecurityState();
                this.logSecurityViolation(`Alt combination blocked: Alt+${e.key}`);
                return false;
            }

            // Prevent Escape key
            if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();

                this.keyboardViolations++;
                this.saveSecurityState();
                this.logSecurityViolation('Escape key blocked');
                return false;
            }
        },

        preventScreenshot(e) {
            // Detect common screenshot shortcuts
            if ((e.ctrlKey && e.shiftKey && e.key === 'S') ||
                (e.key === 'PrintScreen') ||
                (e.metaKey && e.shiftKey && ['3', '4', '5'].includes(e.key))) {

                e.preventDefault();
                e.stopPropagation();

                this.screenshotAttempts++;
                this.saveSecurityState();
                this.logSecurityViolation('Screenshot attempt detected');

                if (this.screenshotAttempts >= this.maxScreenshotAttempts) {
                    this.autoSubmitExam('স্ক্রিনশট নেওয়ার চেষ্টা করা হয়েছে');
                } else {
                    this.showWarning(`স্ক্রিনশট নিষিদ্ধ! আরও ${this.maxScreenshotAttempts - this.screenshotAttempts} বার চেষ্টা করলে পরীক্ষা জমা হয়ে যাবে।`);
                }
                return false;
            }
        },

        handleVisibilityChange() {
            if (document.hidden) {
                // Tab became hidden
                this.isTabAway = true;
                this.tabAwayStartTime = Date.now();

                // Start the 5-second timer
                this.tabAwayTimer = setTimeout(() => {
                    if (this.isTabAway && this.examStarted && !this.examCompleted) {
                        this.autoSubmitExam('৫ সেকেন্ডের বেশি ট্যাব থেকে দূরে থাকা');
                    }
                }, this.maxTabAwayTime);

                this.tabSwitchCount++;
                this.saveSecurityState();
                this.logSecurityViolation('Tab switch detected');

                if (this.tabSwitchCount >= this.maxTabSwitches) {
                    this.autoSubmitExam('অনুমোদিত সংখ্যক ট্যাব পরিবর্তনের সীমা অতিক্রম');
                }
            } else {
                // Tab became visible
                this.isTabAway = false;

                // Clear the timer if user comes back before 5 seconds
                if (this.tabAwayTimer) {
                    clearTimeout(this.tabAwayTimer);
                    this.tabAwayTimer = null;
                }

                // Calculate how long they were away
                if (this.tabAwayStartTime) {
                    const awayDuration = Date.now() - this.tabAwayStartTime;
                    this.logSecurityViolation(`Tab returned after ${awayDuration}ms`);

                    // If they were away for less than 5 seconds, show warning
                    if (awayDuration < this.maxTabAwayTime && this.examStarted && !this.examCompleted) {
                        const remainingAttempts = this.maxTabSwitches - this.tabSwitchCount;
                        if (remainingAttempts > 0) {
                            this.showWarning(`ট্যাব পরিবর্তন সনাক্ত! আরো ${remainingAttempts} বার পরিবর্তন করলে পরীক্ষা স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে।`);
                        }
                    }
                }

                this.tabAwayStartTime = null;
            }
        },

        handleWindowBlur() {
            this.windowBlurCount++;
            this.saveSecurityState();
            this.logSecurityViolation('Window lost focus');

            if (this.windowBlurCount >= this.maxWindowBlurs) {
                this.autoSubmitExam('উইন্ডো ফোকাস হারানোর সীমা অতিক্রম');
            }
        },

        handleWindowFocus() {
            this.logSecurityViolation('Window regained focus');
        },

        handleMouseLeave() {
            this.logSecurityViolation('Mouse left window area');
        },

        handleCopyAttempt(e) {
            e.preventDefault();
            e.stopPropagation();
            this.copyPasteAttempts++;
            this.saveSecurityState();
            this.logSecurityViolation('Copy attempt blocked');

            if (this.copyPasteAttempts >= this.maxCopyPasteAttempts) {
                this.autoSubmitExam('কপি/পেস্ট চেষ্টার সীমা অতিক্রম');
            }
            return false;
        },

        handlePasteAttempt(e) {
            e.preventDefault();
            e.stopPropagation();
            this.copyPasteAttempts++;
            this.saveSecurityState();
            this.logSecurityViolation('Paste attempt blocked');

            if (this.copyPasteAttempts >= this.maxCopyPasteAttempts) {
                this.autoSubmitExam('কপি/পেস্ট চেষ্টার সীমা অতিক্রম');
            }
            return false;
        },

        handleCutAttempt(e) {
            e.preventDefault();
            e.stopPropagation();
            this.copyPasteAttempts++;
            this.saveSecurityState();
            this.logSecurityViolation('Cut attempt blocked');

            if (this.copyPasteAttempts >= this.maxCopyPasteAttempts) {
                this.autoSubmitExam('কপি/পেস্ট চেষ্টার সীমা অতিক্রম');
            }
            return false;
        },

        handleBeforeUnload(e) {
            if (this.examStarted && !this.examCompleted) {
                e.preventDefault();
                e.returnValue = 'পরীক্ষা চলমান। পেজ ছেড়ে গেলে পরীক্ষা স্বয়ংক্রিয়ভাবে জমা হয়ে যাবে।';

                // Auto-submit on page unload attempt
                setTimeout(() => {
                    this.autoSubmitExam('পেজ আনলোডের চেষ্টা');
                }, 100);

                return e.returnValue;
            }
        },

        enterFullscreen() {
            try {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.warn('Could not enter fullscreen:', err);
                    });
                }
                document.addEventListener('fullscreenchange', this.handleFullscreenChange);
            } catch (error) {
                console.warn('Fullscreen not supported or failed:', error);
            }
        },

        handleFullscreenChange() {
            if (!document.fullscreenElement && this.examStarted && !this.examCompleted) {
                this.fullscreenExitCount++;
                this.saveSecurityState();
                this.logSecurityViolation('Fullscreen mode exited');

                if (this.fullscreenExitCount >= this.maxFullscreenExits) {
                    this.autoSubmitExam('ফুলস্ক্রিন মোড থেকে বের হওয়া');
                } else {
                    this.showWarning(`ফুলস্ক্রিন মোড বজায় রাখুন! আরও ${this.maxFullscreenExits - this.fullscreenExitCount} বার বের হলে পরীক্ষা জমা হয়ে যাবে।`);

                    // Try to re-enter fullscreen after a short delay
                    setTimeout(() => {
                        try {
                            this.enterFullscreen();
                        } catch (err) {
                            console.warn('Could not re-enter fullscreen:', err);
                        }
                    }, 1000);
                }
            }
        },

        startConsoleDetection() {
            const self = this; // Store reference to component instance

            // Detect DevTools opening - reduced frequency
            this.consoleCheckInterval = setInterval(() => {
                const threshold = 160;
                if (window.outerHeight - window.innerHeight > threshold ||
                    window.outerWidth - window.innerWidth > threshold) {
                    self.logSecurityViolation('DevTools possibly opened (size detection)');
                    self.autoSubmitExam('DevTools খোলার সন্দেহ');
                }
            }, 3000); // Reduced from 1000ms to 3000ms

            // Simplified console override - only override log
            const originalLog = console.log;
            console.log = function (...args) {
                self.logSecurityViolation('Console.log accessed');
                // Don't call original to prevent console usage
            };

            // Remove the problematic console object override
            // This was causing performance issues
        },

        monitorNetworkRequests() {
            const self = this; // Store reference to component instance

            // Simplified network monitoring - only monitor fetch, not XMLHttpRequest
            // XMLHttpRequest override was causing conflicts

            // Override fetch only
            const originalFetch = window.fetch;
            window.fetch = function (url, options = {}) {
                // Allow more URLs to prevent blocking legitimate requests
                const allowedPatterns = ['/quizzes/', '/api/exam/', '/api/', 'localhost', '127.0.0.1'];
                const isAllowed = allowedPatterns.some(pattern =>
                    typeof url === 'string' && url.includes(pattern)
                );

                if (!isAllowed && typeof url === 'string' && url.startsWith('http')) {
                    self.networkRequestCount++;
                    self.saveSecurityState();
                    self.logSecurityViolation(`Unauthorized fetch request: ${url}`);

                    if (self.networkRequestCount >= self.maxNetworkRequests) {
                        self.autoSubmitExam('অননুমোদিত নেটওয়ার্ক রিকোয়েস্ট');
                    }
                    return Promise.reject(new Error('Unauthorized network request blocked'));
                }

                return originalFetch.apply(this, arguments);
            };
        },

        detectDevTools() {
            // Simplified DevTools detection with better performance
            const self = this; // Store reference to component instance

            let devtools = {
                open: false
            };

            // Method 1: Window size detection - reduced frequency
            const threshold = 160;
            setInterval(() => {
                if (window.outerHeight - window.innerHeight > threshold ||
                    window.outerWidth - window.innerWidth > threshold) {
                    if (!devtools.open) {
                        devtools.open = true;
                        self.logSecurityViolation('DevTools detected - window size method');
                        self.autoSubmitExam('ডেভেলপার টুলস খোলা হয়েছে');
                    }
                } else {
                    devtools.open = false;
                }
            }, 2000); // Reduced from 500ms to 2000ms

            // Method 2: Simplified debugger detection - less frequent
            setInterval(() => {
                const before = Date.now();
                // debugger;
                const after = Date.now();
                if (after - before > 100) {
                    self.logSecurityViolation('DevTools detected - debugger method');
                    self.autoSubmitExam('ডেভেলপার টুলস সনাক্ত');
                }
            }, 5000); // Reduced from 1000ms to 5000ms

            // Remove problematic property detection as it causes performance issues
        },

        logSecurityViolation(violation) {
            try {
                const timestamp = new Date().toISOString();
                console.warn(`[SECURITY VIOLATION] ${timestamp}: ${violation}`);

                // Store violation in session for potential reporting
                const violations = JSON.parse(sessionStorage.getItem('securityViolations') || '[]');
                violations.push({
                    timestamp,
                    violation,
                    userAgent: navigator.userAgent,
                    url: window.location.href
                });
                sessionStorage.setItem('securityViolations', JSON.stringify(violations));
            } catch (error) {
                console.error('Failed to log security violation:', error);
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
            const confirmed = await window.s_confirm(
                'আপনি কি নিশ্চিত যে পরীক্ষা শেষ করতে চান?',
                'হ্যাঁ, শেষ করুন',
                'question'
            );

            if (confirmed) {
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

                // Include security violations in submission
                const violations = JSON.parse(sessionStorage.getItem('securityViolations') || '[]');
                const securityData = {
                    tabSwitchCount: this.tabSwitchCount,
                    fullscreenExitCount: this.fullscreenExitCount,
                    screenshotAttempts: this.screenshotAttempts,
                    keyboardViolations: this.keyboardViolations,
                    windowBlurCount: this.windowBlurCount,
                    copyPasteAttempts: this.copyPasteAttempts,
                    networkRequestCount: this.networkRequestCount,
                    violations: violations
                };

                const response = await axios.post('/quizzes/submit_exam', {
                    quiz_id: this.quiz.id,
                    answers: this.answers,
                    duration: this.examDuration,
                    submit_reason: reason,
                    security_data: securityData
                }, {
                    headers: {
                        'Authorization': `Bearer ${sessionToken}`
                    }
                });

                if (response.data.statusCode === 200) {
                    this.examCompleted = true;
                    this.cleanupExam();
                    window.s_alert('পরীক্ষা সফলভাবে জমা দেওয়া হয়েছে', 'success');
                } else {
                    window.s_error(response.data.message || 'উত্তরপত্র জমা দিতে সমস্যা');
                }
            } catch (error) {
                console.error('Submit error:', error);
                let errorMessage = 'সংযোগে সমস্যা';

                if (error.response) {
                    // Server responded with error status
                    if (error.response.status === 400) {
                        errorMessage = error.response.data?.message || 'অবৈধ ডেটা পাঠানো হয়েছে';
                    } else if (error.response.status === 401) {
                        errorMessage = 'অনুমোদন প্রয়োজন';
                        sessionStorage.removeItem('examSession');
                        this.$router.push('/quiz-register');
                        return;
                    } else if (error.response.data.status === "already_submitted") {
                        errorMessage = 'আপনি ইতিমধ্যে এই পরীক্ষাটি জমা দিয়েছেন।';
                        this.examCompleted = true;
                        this.cleanupExam();
                    } else {
                        errorMessage = error.response.data?.message || 'সার্ভার ত্রুটি';
                    }
                }

                window.s_error(errorMessage);
            }
        },

        cleanupExam() {
            // Clear all timers
            if (this.timer) {
                clearInterval(this.timer);
            }
            if (this.warningTimer) {
                clearInterval(this.warningTimer);
            }
            if (this.tabAwayTimer) {
                clearTimeout(this.tabAwayTimer);
            }
            if (this.consoleCheckInterval) {
                clearInterval(this.consoleCheckInterval);
            }
            if (this.saveStateTimeout) {
                clearTimeout(this.saveStateTimeout);
            }

            // Remove all event listeners
            document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            document.removeEventListener('keydown', this.preventKeyboardShortcuts);
            document.removeEventListener('fullscreenchange', this.handleFullscreenChange);
            document.removeEventListener('contextmenu', this.preventContextMenu);
            document.removeEventListener('selectstart', this.preventSelection);
            document.removeEventListener('dragstart', this.preventDrag);
            window.removeEventListener('beforeunload', this.handleBeforeUnload);

            // Restore normal page behavior
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';

            // Clear security state from localStorage
            if (this.securityStateKey) {
                localStorage.removeItem(this.securityStateKey);
            }

            // Exit fullscreen safely
            try {
                if (document.exitFullscreen && document.fullscreenElement) {
                    document.exitFullscreen().catch(err => {
                        console.warn('Could not exit fullscreen:', err);
                    });
                }
            } catch (error) {
                console.warn('Fullscreen exit failed:', error);
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
            this.$inertia.visit(`/`);
        }
    }
}
</script>

<style scoped>
/* Mobile-First Responsive Design */
.quiz-exam {
    min-height: 100vh;
    background: #f8f9fa;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header - Mobile First */
.exam-header {
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    border-bottom: 1px solid #e9ecef;
}

.exam-info {
    text-align: center;
}

.exam-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.3;
}

.question-counter {
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-block;
}

.timer {
    background: #28a745;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
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

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }
}

/* Question Container - Mobile First */
.question-container {
    padding: 1rem 0;
    min-height: calc(100vh - 200px);
}

.question-card {
    background: white;
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    margin-bottom: 1rem;
}

.question-header {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.question-number {
    background: #007bff;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-weight: 600;
    font-size: 0.8rem;
}

.question-marks {
    background: #28a745;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.question-type {
    background: #16bebf;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.question-text {
    color: #2c3e50;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-size: 1rem;
    font-weight: 500;
}

/* Options - Mobile First */
.options-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.option-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.option-item:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.option-item.selected {
    border-color: #28a745;
    background: #d4edda;
}

.option-selector {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 0.75rem;
    gap: 0.5rem;
    flex-shrink: 0;
}

.option-radio input {
    width: 18px;
    height: 18px;
    margin: 0;
}

.option-label {
    background: #007bff;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
}

.option-content {
    flex: 1;
    min-width: 0;
}

.option-text {
    color: #2c3e50;
    font-size: 0.9rem;
    line-height: 1.4;
    word-wrap: break-word;
}

.option-image-wrapper {
    margin-top: 0.5rem;
}

.option-image {
    max-width: 100%;
    max-height: 120px;
    object-fit: contain;
    border-radius: 6px;
}

/* Mobile Question Navigation */
.mobile-question-nav {
    background: white;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 0;
    margin: 1rem;
}

.mobile-nav-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.mobile-nav-header h6 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.9rem;
}

.nav-stats {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.mobile-question-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(40px, 0fr));
    gap: 0.5rem;
    overflow-y: auto;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.mobile-grid-item {
    width: 40px;
    height: 40px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-grid-item.current {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.mobile-grid-item.answered {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.mobile-grid-item.unanswered {
    background: white;
    border-color: #ffc107;
    color: #856404;
}

/* Desktop Question Sidebar */
.question-grid-sidebar {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}

.grid-header h6 {
    margin: 0 0 1rem 0;
    text-align: center;
    color: #2c3e50;
    font-weight: 600;
}

.question-grid {
    display: grid;
    /* grid-template-columns: repeat(14, 0fr); */
    grid-template-columns: repeat(auto-fill, 32px);
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.grid-item {
    width: 32px;
    height: 32px;
    border: 2px solid #dee2e6;
    background: white;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
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
    background: white;
    border-color: #ffc107;
    color: #856404;
}

.grid-legend {
    font-size: 0.75rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    flex-shrink: 0;
}

.legend-color.answered {
    background: #28a745;
}

.legend-color.current {
    background: #007bff;
}

.legend-color.unanswered {
    background: #ffc107;
}

/* Navigation - Mobile First */
.exam-navigation {
    background: white;
    padding: 1rem 0;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    border-top: 1px solid #e9ecef;
    z-index: 1050;
}

.btn-nav {
    min-width: 80px;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    font-weight: 600;
}

.question-progress {
    text-align: center;
}

.progress-bar-wrapper {
    width: 100%;
    max-width: 200px;
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    margin: 0 auto 0.25rem;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: #007bff;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.progress-text {
    color: #6c757d;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Completion Screen - Mobile First */
.exam-completed {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.completion-card {
    background: white;
    border-radius: 15px;
    padding: 2rem 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.completion-icon {
    font-size: 3rem;
}

.completion-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.completion-message {
    font-size: 1rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.exam-summary {
    margin: 1.5rem 0;
}

.summary-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    border: 1px solid #e9ecef;
}

.summary-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-size: 1rem;
}

.summary-icon.bg-success {
    background: #28a745;
}

.summary-icon.bg-warning {
    background: #ffc107;
}

.summary-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    display: block;
    margin-bottom: 0.25rem;
}

.summary-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

/* Countdown Warning */
.countdown-warning {
    font-size: 2rem;
    color: #dc3545;
    margin: 1rem 0;
    text-align: center;
}

.countdown-number {
    display: inline-block;
    width: 50px;
    height: 50px;
    line-height: 46px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

.text-right {
    text-align: right;
}

.option-radio {
    display: none;
}

/* Responsive Breakpoints */

/* Small devices (landscape phones, 576px and up) */
@media (max-width: 576px) {
    .question-container {
        min-height: unset;
    }
}

@media (min-width: 576px) {
    .exam-header {
        padding: 1rem 0;
    }

    .exam-title {
        font-size: 1.1rem;
    }

    .question-card {
        padding: 1.5rem;
    }

    .question-text {
        font-size: 1.1rem;
    }

    .option-text {
        font-size: 1rem;
    }

    .completion-card {
        padding: 2.5rem;
    }

    .mobile-question-grid {
        grid-template-columns: repeat(auto-fit, 45px);
    }

    .mobile-grid-item {
        width: 45px;
        height: 45px;
    }

    .question-container {
        min-height: unset;
    }

}

/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
    .exam-info {
        text-align: start;
    }

    .question-container {
        padding: 1.5rem 0;
    }

    .question-card {
        padding: 2rem;
    }

    .question-text {
        font-size: 1.2rem;
    }

    .timer {
        font-size: 1rem;
        padding: 0.75rem 1.25rem;
    }

    .question-counter {
        font-size: 1rem;
        padding: 0.75rem 1.25rem;
    }

    .completion-title {
        font-size: 2.25rem;
    }

    .completion-message {
        font-size: 1.125rem;
    }

    .btn-nav {
        min-width: 100px;
        padding: 0.75rem 1.25rem;
    }

    .exam-navigation {
        position: fixed;
    }

    .question-container {
        min-height: unset;
    }
}

/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
    .question-container {
        padding: 2rem 0;
    }

    .exam-header .timer {
        text-align: right;
    }

    .mobile-question-nav {
        display: none !important;
    }
}

/* Extra large devices (large desktops, 1200px and up) */
@media (min-width: 1200px) {
    .question-card {
        padding: 2.5rem;
    }

    .completion-card {
        padding: 3rem;
    }
}

/* Security & UX */
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

input,
textarea {
    -webkit-user-select: text;
    -khtml-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
}

/* Touch-friendly interactive elements */
@media (hover: none) and (pointer: coarse) {
    .option-item {
        min-height: 48px;
    }

    .grid-item,
    .mobile-grid-item {
        min-width: 44px;
        min-height: 44px;
    }

    .btn-nav {
        min-height: 44px;
    }
}
</style>
