<template>
    <div>
        <form @submit.prevent="submitHandler">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="text-capitalize">
                        {{ param_id ? `${setup.edit_page_title}` : `${setup . create_page_title}` }}

                    </h5>
                    <div>
                        <router-link v-if="item.slug" class="btn btn-outline-info mr-2 btn-sm" :to="{
                            name: `Details${setup.route_prefix}`,
                            params: { id: item.slug },
                        }">
                            {{ setup.details_page_title }}
                        </router-link>
                        <router-link class="btn btn-outline-warning btn-sm" :to="{ name: `All${setup.route_prefix}` }">
                            {{ setup.all_page_title }}
                        </router-link>
                    </div>
                </div>
                <div class="card-body card_body_fixed_height">
                    <div class="row">
                    <quiz-question-topic-drop-down-el 
                        ref="topicDropdown"
                        :name="'quiz_question_topic_id'" 
                        :multiple="false" 
                        :value="formData.quiz_question_topic_id"
                    />
                        <!-- Direct Form Fields -->
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Title</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="formData.title" 
                                placeholder="Enter your title"
                            />
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Question Level</label>
                            <select class="form-control" v-model="formData.question_level">
                                <option value="">Select question level</option>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Mark</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                class="form-control" 
                                v-model="formData.mark" 
                                placeholder="Enter your mark"
                            />
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Is Multiple</label>
                            <select class="form-control" v-model="formData.is_multiple">
                                <option value="">Select is multiple</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Session Year</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                v-model="formData.session_year" 
                                placeholder="Enter your session year"
                            />
                        </div>
                        <!-- Quiz Question Options -->
                        <div class="col-12 mt-3">
                            <label class="form-label font-weight-bold">Options</label>
                            <div v-for="(option, idx) in quizOptions" :key="idx" class="input-group mb-2 align-items-center">
                                <!-- Option Type Selector -->
                                <select v-model="option.type" class="form-control mr-2" style="max-width: 110px;">
                                    <option value="text">Text</option>
                                    <option value="image">Image</option>
                                </select>
                                <!-- Text Input -->
                                <input
                                    v-if="option.type === 'text'"
                                    type="text"
                                    class="form-control"
                                    v-model="option.value"
                                    :placeholder="`Option ${idx+1}`"
                                />
                                <!-- Image Input -->
                                <input
                                    v-if="option.type === 'image'"
                                    type="file"
                                    class="form-control"
                                    @change="onImageChange($event, idx)"
                                    accept="image/*"
                                />
                                <!-- Image Preview -->
                                <img
                                    v-if="option.type === 'image' && option.preview"
                                    :src="option.preview"
                                    alt="Preview"
                                    style="max-width: 60px; max-height: 40px; margin-left: 8px;"
                                />
                                <!-- Is Correct Selector -->
                                <div class="ml-3">
                                    <!-- Checkbox for multiple selection -->
                                    <input
                                        v-if="formData.is_multiple === '1' || formData.is_multiple === 1 || formData.is_multiple === true"
                                        type="checkbox"
                                        :name="'is_correct_option_' + idx"
                                        v-model="option.is_correct"
                                        :id="'is_correct_option_' + idx"
                                        @change="handleCorrectOptionChange(idx)"
                                        @click="console.log('Clicked checkbox', idx, 'current value:', option.is_correct, 'will become:', !option.is_correct)"
                                    />
                                    <!-- Radio for single selection -->
                                    <input
                                        v-else
                                        type="radio"
                                        name="is_correct_option"
                                        v-model="option.is_correct"
                                        :value="true"
                                        :id="'is_correct_option_' + idx"
                                        @change="handleCorrectOptionChange(idx)"
                                        @click="console.log('Clicked radio', idx, 'current value:', option.is_correct, 'will become:', true)"
                                    />
                                    <label class="ml-1" :for="'is_correct_option_' + idx">Correct</label>
                                </div>
                                <!-- Add/Remove Buttons -->
                                <div class="input-group-append ml-2">
                                    <button v-if="quizOptions.length > 1" type="button" class="btn btn-danger" @click="removeOption(idx)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button v-if="idx === quizOptions.length-1" type="button" class="btn btn-success" @click="addOption">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-light btn-square px-5">
                        <i class="icon-lock"></i>
                        Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { store } from "../store";
import setup from "../setup";
import form_fields from "../setup/form_fields";
           
import QuizQuestionTopicDropDownEl from "../../QuizQuestionTopic/components/dropdown/DropDownEl.vue";
        export default {
        components: {QuizQuestionTopicDropDownEl,        },

    data: () => ({
        setup,
        form_fields,
        param_id: null,
        formData: {
            title: "",
            question_level: "",
            mark: "",
            is_multiple: "",
            session_year: "",
            quiz_question_topic_id: ""
        },
        quizOptions: [
            { type: "text", value: "", is_correct: false, preview: null },
            { type: "text", value: "", is_correct: false, preview: null },
            { type: "text", value: "", is_correct: false, preview: null },
            { type: "text", value: "", is_correct: false, preview: null }
        ],
    }),
    created: async function () {
        let id = (this.param_id = this.$route.params.id);
        this.reset_fields();
        if (id) {
            this.set_fields(id);
        }
    },
    methods: {
        ...mapActions(store, {
            create: "create",
            update: "update",
            details: "details",
            get_all: "get_all",
            set_only_latest_data: "set_only_latest_data",
        }),
        reset_fields: function () {
            this.formData = {
                title: "",
                question_level: "",
                mark: "",
                is_multiple: "",
                session_year: "",
                quiz_question_topic_id: ""
            };
            // Reset options
            this.quizOptions = [
                { type: "text", value: "", is_correct: false, preview: null },
                { type: "text", value: "", is_correct: false, preview: null },
                { type: "text", value: "", is_correct: false, preview: null },
                { type: "text", value: "", is_correct: false, preview: null }
            ];
        },
        set_fields: async function (id) {
            this.param_id = id;
            await this.details(id);
            if (this.item) {
                // Load form data
                this.formData.title = this.item.title || "";
                this.formData.question_level = this.item.question_level || "";
                this.formData.mark = this.item.mark || "";
                // Ensure is_multiple is a string for select binding
                if (typeof this.item.is_multiple === 'number') {
                    this.formData.is_multiple = String(this.item.is_multiple);
                } else {
                    this.formData.is_multiple = this.item.is_multiple || "";
                }
                this.formData.session_year = this.item.session_year || "";
                // quiz_question_topic_id may be an object
                if (this.item.quiz_question_topic_id && typeof this.item.quiz_question_topic_id === 'object') {
                    this.formData.quiz_question_topic_id = this.item.quiz_question_topic_id.id || "";
                } else {
                    this.formData.quiz_question_topic_id = this.item.quiz_question_topic_id || "";
                }

                // Load options from item.quiz_question_options if available
                if (Array.isArray(this.item.quiz_question_options)) {
                    this.quizOptions = this.item.quiz_question_options.length
                        ? this.item.quiz_question_options.map(opt => ({
                            id: opt.id, // Include the ID for updates
                            type: opt.image ? "image" : "text",
                            value: opt.image ? opt.image : opt.title,
                            is_correct: opt.is_correct === 1 || opt.is_correct === "1" || opt.is_correct === true,
                            preview: opt.image ? opt.image : null
                        }))
                        : [
                            { type: "text", value: "", is_correct: false, preview: null },
                            { type: "text", value: "", is_correct: false, preview: null },
                            { type: "text", value: "", is_correct: false, preview: null },
                            { type: "text", value: "", is_correct: false, preview: null }
                        ];
                }
            }
        },
        submitHandler: async function ($event) {
            this.set_only_latest_data(true);
            this.setSummerEditor();

            // Build FormData for file upload
            let formData = new FormData();
            formData.append('title', this.formData.title);
            formData.append('question_level', this.formData.question_level);
            formData.append('mark', this.formData.mark);
            formData.append('is_multiple', this.formData.is_multiple);
            formData.append('session_year', this.formData.session_year);
            formData.append('quiz_question_topic_id', this.$refs.topicDropdown?.selected_ids || this.formData.quiz_question_topic_id);

            // Debug log the options before FormData creation
            console.log('Quiz Options before submission:', this.quizOptions);

            // Attach options
            this.quizOptions.forEach((opt, idx) => {
                if (opt.id) {
                    formData.append(`options[${idx}][id]`, opt.id); // Include ID for existing options
                }
                formData.append(`options[${idx}][type]`, opt.type);
                formData.append(`options[${idx}][value]`, opt.value);
                formData.append(`options[${idx}][is_correct]`, opt.is_correct ? 1 : 0);
                if (opt.type === 'image' && opt.file) {
                    formData.append(`options[${idx}][image]`, opt.file);
                }
            });

            // Debug log
            for (let pair of formData.entries()) { console.log(pair[0]+ ': ' + pair[1]); }

            let response;
            if (this.param_id) {
                response = await this.update(formData);
                if ([200, 201].includes(response.status)) {
                    window.s_alert("Data successfully updated");
                    // Reload the data to show current state
                    await this.set_fields(this.param_id);
                    this.$router.push({ name: `Details${this.setup.route_prefix}` });
                }
            } else {
                response = await this.create(formData);
                if ([200, 201].includes(response.status)) {
                    $event.target.reset();
                    this.reset_fields();
                    window.s_alert("Data Successfully Created");
                }
            }
        },
        setCorrectOption(idx) {
            if (this.formData.is_multiple === '1' || this.formData.is_multiple === 1 || this.formData.is_multiple === true) {
                // Toggle the selected option
                this.quizOptions[idx].is_correct = !this.quizOptions[idx].is_correct;
            } else {
                // Single correct option
                this.quizOptions.forEach(opt => opt.is_correct = false);
                this.quizOptions[idx].is_correct = true;
            }
        },
        handleCorrectOptionChange(idx) {
            console.log('handleCorrectOptionChange called for idx:', idx);
            console.log('Current option state BEFORE:', this.quizOptions[idx]);
            console.log('is_multiple value:', this.formData.is_multiple);
            
            // For single selection (radio buttons), ensure only one is selected
            if (this.formData.is_multiple === '0' || this.formData.is_multiple === 0 || this.formData.is_multiple === false) {
                this.setCorrectOption(idx);
            }
            
            // Log the state after change
            setTimeout(() => {
                console.log('Current option state AFTER:', this.quizOptions[idx]);
                console.log('All options state:', this.quizOptions.map((opt, i) => ({idx: i, is_correct: opt.is_correct})));
            }, 0);
        },
        onImageChange(event, idx) {
            const file = event.target.files[0];
            if (file) {
                this.quizOptions[idx].value = file.name;
                this.quizOptions[idx].file = file; // Store File object for upload
                this.quizOptions[idx].preview = URL.createObjectURL(file); // For preview only
            }
        },
        addOption() {
            this.quizOptions.push({ type: "text", value: "", is_correct: false, preview: null });
        },
        removeOption(idx) {
            if (this.quizOptions.length > 1) {
                this.quizOptions.splice(idx, 1);
            }
        },
        setSummerEditor() {
            // Dynamically set summernote content for all textarea fields
            this.form_fields.forEach(field => {
                if (field.type === 'textarea' && $(`#${field . name}`).length) {
                    const markupStr = $(`#${field . name}`).summernote("code");
                    // Set the value in the form field object
                    field.value = markupStr;
                    // Optionally, update a hidden input if your backend expects it
                    let $input = $(`#${field . name}_hidden`);
                    if ($input.length === 0) {
                        $input = $(`<input type="hidden" id="${field . name}_hidden" name="${field . name}">`);
                        $(`#${field . name}`).parent().append($input);
                    }
                    $input.val(markupStr);
                }
            });
        },
    },

    computed: {
        ...mapState(store, {
            item: "item",
        }),
    },
};
</script>


