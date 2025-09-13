export default [
	{
		name: "user_id",
		label: "Enter your user id",
		type: "number",
		value: "",
	},

	{
		name: "submission_no",
		label: "Enter your submission no",
		type: "select",
		label: "Select submission no",
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
		name: "quiz_mark",
		label: "Enter your quiz mark",
		type: "number",
		step: "0.01",
		value: "",
	},

	{
		name: "obtain_mark",
		label: "Enter your obtain mark",
		type: "number",
		step: "0.01",
		value: "",
	},

	{
		name: "submission_datetime",
		label: "Enter your submission datetime",
		type: "datetime-local",
		value: "",
	},
];
