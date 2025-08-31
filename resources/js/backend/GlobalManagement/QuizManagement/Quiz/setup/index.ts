
import app_config from "@config/app_config";
import setup_type from "./setup_type";

const prefix: string = "Quiz";

const setup: setup_type = {
    prefix,
    permission: ["admin", "super_admin"],

    api_host: app_config.api_host,
    api_version: app_config.api_version,
    api_end_point: "quizzes",

    module_name: "quiz",
    store_prefix: "quiz",
    route_prefix: "Quiz",
    route_path: "quiz",

    select_fields: [
        "id",
        "title",
            "description",
            "total_question",
            "exam_start_datetime",
            "exam_end_datetime",
            "total_mark",
            "pass_mark",
            "is_negative_marking",
            "negative_value",
        "status",
        "slug",
        "created_at",
        'deleted_at'
    ],

    sort_by_cols: [
        "id",
        "title",
            "description",
            "total_question",
            "exam_start_datetime",
            "exam_end_datetime",
            "total_mark",
            "pass_mark",
            "is_negative_marking",
            "negative_value",
        "status",
        "created_at",
    ],
    table_header_data: [
        "id",
        "title",
            "description",
            "total_question",
            "exam_start_datetime",
            "exam_end_datetime",
            "total_mark",
            "pass_mark",
            "is_negative_marking",
            "negative_value",
        "status",
        "created_at",
    ],
    table_row_data: [
        "id",
        "title",
            "description",
            "total_question",
            "exam_start_datetime",
            "exam_end_datetime",
            "total_mark",
            "pass_mark",
            "is_negative_marking",
            "negative_value",
        "status",
        "created_at",
    ],
    quick_view_data: [
        "id",
        "title",
            "description",
            "total_question",
            "exam_start_datetime",
            "exam_end_datetime",
            "total_mark",
            "pass_mark",
            "is_negative_marking",
            "negative_value",
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
