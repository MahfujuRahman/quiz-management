
import app_config from "@config/app_config";
import setup_type from "./setup_type";

const prefix: string = "QuizSubmissionResult";

const setup: setup_type = {
    prefix,
    permission: ["admin", "super_admin"],

    api_host: app_config.api_host,
    api_version: app_config.api_version,
    api_end_point: "quiz-submission-results",

    module_name: "quiz-submission-result",
    store_prefix: "quiz_submission_result",
    route_prefix: "QuizSubmissionResult",
    route_path: "quiz-submission-result",

    select_fields: [
        "id",
        "user_id",
            "quiz_id",
            "submission_no",
            "course_module_class_id",
            "quiz_mark",
            "obtain_mark",
            "submission_datetime",
        "status",
        "slug",
        "created_at",
        'deleted_at'
    ],

    sort_by_cols: [
        "id",
        "user_id",
            "quiz_id",
            "submission_no",
            "course_module_class_id",
            "quiz_mark",
            "obtain_mark",
            "submission_datetime",
        "status",
        "created_at",
    ],
    table_header_data: [
        "id",
        "user_id",
            "quiz_id",
            "submission_no",
            "course_module_class_id",
            "quiz_mark",
            "obtain_mark",
            "submission_datetime",
        "status",
        "created_at",
    ],
    table_row_data: [
        "id",
        "user_id",
            "quiz_id",
            "submission_no",
            "course_module_class_id",
            "quiz_mark",
            "obtain_mark",
            "submission_datetime",
        "status",
        "created_at",
    ],
    quick_view_data: [
        "id",
        "user_id",
            "quiz_id",
            "submission_no",
            "course_module_class_id",
            "quiz_mark",
            "obtain_mark",
            "submission_datetime",
        "status",
        "created_at",
    ],

    layout_title: prefix + " Management",
    page_title: `${prefix} Management`,

    all_page_title: "All " + prefix,
    details_page_title: "Details " + prefix,
    create_page_title: "Create " + prefix,
    edit_page_title: "Edit " + prefix,
};

export default setup;
