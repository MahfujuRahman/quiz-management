<template>
    <div class="results-export">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Header -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">
                                <i class="fas fa-download"></i> পরীক্ষার ফলাফল এক্সপোর্ট
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">পরীক্ষা নির্বাচন করুন</label>
                                    <select class="form-control" v-model="selectedQuizId" @change="loadResults">
                                        <option value="">সব পরীক্ষা</option>
                                        <option v-for="quiz in quizzes" :key="quiz.id" :value="quiz.id">
                                            {{ quiz.title }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">শুরুর তারিখ</label>
                                    <input type="date" class="form-control" v-model="startDate" @change="loadResults">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">শেষের তারিখ</label>
                                    <input type="date" class="form-control" v-model="endDate" @change="loadResults">
                                </div>
                                <div class="col-md-3">
                                    <button 
                                        class="btn btn-success btn-block" 
                                        @click="exportToExcel"
                                        :disabled="results.length === 0 || loading"
                                    >
                                        <i class="fas fa-file-excel"></i> এক্সেল এক্সপোর্ট
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4" v-if="results.length > 0">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-users fa-2x mr-3"></i>
                                        <div>
                                            <h5>মোট অংশগ্রহণকারী</h5>
                                            <h3>{{ results.length }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle fa-2x mr-3"></i>
                                        <div>
                                            <h5>সম্পূর্ণ করেছে</h5>
                                            <h3>{{ completedCount }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-star fa-2x mr-3"></i>
                                        <div>
                                            <h5>গড় নম্বর</h5>
                                            <h3>{{ averageMarks.toFixed(1) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-trophy fa-2x mr-3"></i>
                                        <div>
                                            <h5>সর্বোচ্চ নম্বর</h5>
                                            <h3>{{ maxMarks }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="card shadow">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">পরীক্ষার ফলাফল</h5>
                            <div>
                                <span class="badge badge-info mr-2">মোট: {{ results.length }} জন</span>
                                <button class="btn btn-sm btn-outline-secondary" @click="loadResults">
                                    <i class="fas fa-refresh"></i> রিফ্রেশ
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Loading -->
                            <div v-if="loading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">লোড হচ্ছে...</span>
                                </div>
                            </div>

                            <!-- Results Table -->
                            <div v-else-if="results.length > 0" class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>নাম</th>
                                            <th>পরীক্ষা</th>
                                            <th>গ্রুপ</th>
                                            <th>শ্রেণি</th>
                                            <th>শিক্ষা প্রতিষ্ঠান</th>
                                            <th>মোবাইল</th>
                                            <th>প্রাপ্ত নম্বর</th>
                                            <th>পূর্ণমান</th>
                                            <th>শতাংশ</th>
                                            <th>সময়কাল</th>
                                            <th>জমার সময়</th>
                                            <th>স্ট্যাটাস</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(result, index) in paginatedResults" :key="result.id">
                                            <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                            <td>
                                                <strong>{{ result.student_name }}</strong>
                                                <br>
                                                <small class="text-muted" v-if="result.student_email">{{ result.student_email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ result.quiz_title }}</span>
                                            </td>
                                            <td>{{ result.student_group || 'N/A' }}</td>
                                            <td>{{ result.student_class || 'N/A' }}</td>
                                            <td>{{ result.student_institution || 'N/A' }}</td>
                                            <td>{{ result.student_mobile || 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-success">{{ result.obtained_marks || 0 }}</span>
                                            </td>
                                            <td>{{ result.total_marks }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div 
                                                        class="progress-bar" 
                                                        :class="getProgressBarClass(result.percentage)"
                                                        :style="{ width: result.percentage + '%' }"
                                                    >
                                                        {{ result.percentage.toFixed(1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ formatDuration(result.duration) }}</td>
                                            <td>
                                                <small>{{ formatDateTime(result.submitted_at) }}</small>
                                            </td>
                                            <td>
                                                <span 
                                                    class="badge"
                                                    :class="result.is_completed ? 'badge-success' : 'badge-warning'"
                                                >
                                                    {{ result.is_completed ? 'সম্পূর্ণ' : 'অসম্পূর্ণ' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- No Results -->
                            <div v-else class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">কোনো ফলাফল পাওয়া যায়নি</h5>
                                <p class="text-muted">ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন</p>
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="d-flex justify-content-center mt-4">
                                <nav>
                                    <ul class="pagination">
                                        <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                            <a class="page-link" @click="changePage(currentPage - 1)">পূর্ববর্তী</a>
                                        </li>
                                        <li 
                                            v-for="page in visiblePages" 
                                            :key="page"
                                            class="page-item" 
                                            :class="{ active: page === currentPage }"
                                        >
                                            <a class="page-link" @click="changePage(page)">{{ page }}</a>
                                        </li>
                                        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                            <a class="page-link" @click="changePage(currentPage + 1)">পরবর্তী</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
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
            quizzes: [],
            results: [],
            loading: false,
            selectedQuizId: '',
            startDate: '',
            endDate: '',
            currentPage: 1,
            perPage: 50
        }
    },
    
    computed: {
        paginatedResults() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.results.slice(start, end);
        },
        
        totalPages() {
            return Math.ceil(this.results.length / this.perPage);
        },
        
        visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
        
        completedCount() {
            return this.results.filter(r => r.is_completed).length;
        },
        
        averageMarks() {
            if (this.results.length === 0) return 0;
            const total = this.results.reduce((sum, r) => sum + (r.obtained_marks || 0), 0);
            return total / this.results.length;
        },
        
        maxMarks() {
            if (this.results.length === 0) return 0;
            return Math.max(...this.results.map(r => r.obtained_marks || 0));
        }
    },
    
    async created() {
        await this.loadQuizzes();
        await this.loadResults();
        
        // Set default date range (last 30 days)
        this.endDate = moment().format('YYYY-MM-DD');
        this.startDate = moment().subtract(30, 'days').format('YYYY-MM-DD');
    },
    
    methods: {
        async loadQuizzes() {
            try {
                const response = await fetch('/api/v1/quizzes');
                if (response.ok) {
                    const data = await response.json();
                    this.quizzes = data.data || [];
                }
            } catch (error) {
                console.error('Error loading quizzes:', error);
            }
        },
        
        async loadResults() {
            try {
                this.loading = true;
                
                const params = new URLSearchParams();
                if (this.selectedQuizId) params.append('quiz_id', this.selectedQuizId);
                if (this.startDate) params.append('start_date', this.startDate);
                if (this.endDate) params.append('end_date', this.endDate);
                
                const response = await fetch(`/api/v1/quiz-results?${params.toString()}`);
                if (response.ok) {
                    const data = await response.json();
                    this.results = data.data || [];
                    this.currentPage = 1;
                }
            } catch (error) {
                console.error('Error loading results:', error);
                this.$toast.error('ফলাফল লোড করতে সমস্যা');
            } finally {
                this.loading = false;
            }
        },
        
        async exportToExcel() {
            try {
                const params = new URLSearchParams();
                if (this.selectedQuizId) params.append('quiz_id', this.selectedQuizId);
                if (this.startDate) params.append('start_date', this.startDate);
                if (this.endDate) params.append('end_date', this.endDate);
                
                const response = await fetch(`/api/v1/quiz-results/export?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    
                    const filename = `quiz_results_${moment().format('YYYY-MM-DD_HH-mm-ss')}.xlsx`;
                    a.download = filename;
                    
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    
                    this.$toast.success('এক্সেল ফাইল সফলভাবে ডাউনলোড হয়েছে');
                } else {
                    this.$toast.error('এক্সপোর্ট করতে সমস্যা');
                }
            } catch (error) {
                console.error('Export error:', error);
                this.$toast.error('এক্সপোর্ট করতে সমস্যা');
            }
        },
        
        formatDateTime(datetime) {
            return moment(datetime).format('DD/MM/YYYY hh:mm A');
        },
        
        formatDuration(seconds) {
            if (!seconds) return 'N/A';
            
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            
            let duration = '';
            if (hours > 0) duration += `${hours}ঘ `;
            if (minutes > 0) duration += `${minutes}ম `;
            duration += `${secs}সে`;
            
            return duration;
        },
        
        getProgressBarClass(percentage) {
            if (percentage >= 80) return 'bg-success';
            if (percentage >= 60) return 'bg-info';
            if (percentage >= 40) return 'bg-warning';
            return 'bg-danger';
        },
        
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        }
    }
}
</script>

<style scoped>
.results-export {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 2rem 0;
}

.card {
    border: none;
    border-radius: 10px;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    background: #2c3e50;
    color: white;
    text-align: center;
    vertical-align: middle;
}

.table td {
    vertical-align: middle;
    text-align: center;
}

.progress {
    margin: 0;
}

.progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
}

.pagination {
    margin: 0;
}

.page-link {
    cursor: pointer;
    color: #007bff;
    border-color: #dee2e6;
}

.page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.page-item.disabled .page-link {
    color: #6c757d;
    cursor: not-allowed;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

.form-control, .btn {
    border-radius: 5px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

@media (max-width: 768px) {
    .results-export {
        padding: 1rem 0;
    }
    
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .row.align-items-end > div {
        margin-bottom: 1rem;
    }
}

/* Custom scrollbar for table */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
