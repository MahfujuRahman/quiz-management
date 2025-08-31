export default [
  {
    name: "title",
    label: "Enter your title",
    type: "text",
    value: "",
  },

  {
    name: "total_question",
    label: "Enter your total question",
    type: "number",
    value: "",
  },

  {
    name: "exam_start_datetime",
    label: "Enter your exam start datetime",
    type: "datetime-local",
    value: "",
  },

  {
    name: "exam_end_datetime",
    label: "Enter your exam end datetime",
    type: "datetime-local",
    value: "",
  },

  {
    name: "total_mark",
    label: "Enter your total mark",
    type: "number",
    step: "0.01",
    value: "",
  },

  {
    name: "pass_mark",
    label: "Enter your pass mark",
    type: "number",
    step: "0.01",
    value: "",
  },

  {
    name: "is_negative_marking",
    label: "Enter your is negative marking",
    type: "select",
    label: "Select is negative marking",
    multiple: false,
    data_list: [
      {
        label: "Yes",
        value: "1",
      },
      {
        label: "No",
        value: "0",
      },
    ],
    value: "",
  },

  {
    name: "negative_value",
    label: "Enter your negative value",
    type: "text",
    value: "",
  },
  {
    name: "description",
    label: "Enter your description",
    type: "textarea",
    value: "",
	row_col_class: "col-12",
  },

];
