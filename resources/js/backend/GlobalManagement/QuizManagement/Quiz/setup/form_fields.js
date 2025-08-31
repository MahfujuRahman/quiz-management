import { readonly } from "vue";

const getMinDateTimeLocal = () => {
  const d = new Date();
  d.setSeconds(0, 0); // remove seconds for datetime-local input
  const pad = (n) => String(n).padStart(2, "0");
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(
    d.getHours()
  )}:${pad(d.getMinutes())}`;
};

const minDateTime = getMinDateTimeLocal();

export default [
  {
    name: "title",
    label: "Enter your title",
    type: "text",
    value: "",
  },
  
  {
    name: "pass_mark",
    label: "Enter your pass mark",
    type: "number",
    step: "1",
    value: "",
  },

  {
    name: "total_question",
    label: "Enter your total question",
    type: "number",
    value: "",
    readonly: true,
  },
  
  {
    name: "total_mark",
    label: "Enter your total mark",
    type: "number",
    step: "0.01",
    value: "",
    readonly: true,
  },

  {
    name: "exam_start_datetime",
    label: "Enter your exam start datetime",
    type: "datetime-local",
    value: "",
    min: minDateTime,
  },

  {
    name: "exam_end_datetime",
    label: "Enter your exam end datetime",
    type: "datetime-local",
    value: "",
    min: minDateTime,
  },


  {
    name: "is_negative_marking",
    label: "Select is negative marking",
    type: "select",
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
