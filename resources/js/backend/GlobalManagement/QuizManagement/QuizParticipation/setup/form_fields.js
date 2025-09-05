export default [
	{
		name: "quiz_id",
		label: "Enter your quiz id",
		type: "number",
		value: "",
	},

	{
		name: "session_token",
		label: "Enter your session token",
		type: "text",
		value: "",
	},

	{
		name: "name",
		label: "Enter your name",
		type: "text",
		value: "",
	},

	{
		name: "email",
		label: "Enter your email",
		type: "text",
		value: "",
	},

	{
		name: "phone",
		label: "Enter your phone",
		type: "text",
		value: "",
	},

	{
		name: "organization",
		label: "Enter your organization",
		type: "text",
		value: "",
	},

	{
		name: "answers",
		label: "Enter your answers",
		type: "textarea",
		placeholder: "Enter JSON data",
		value: "",
	},

	{
		name: "obtained_marks",
		label: "Enter your obtained marks",
		type: "number",
		step: "0.01",
		value: "",
	},

	{
		name: "percentage",
		label: "Enter your percentage",
		type: "number",
		step: "0.01",
		value: "",
	},

	{
		name: "duration",
		label: "Enter your duration",
		type: "number",
		value: "",
	},

	{
		name: "submit_reason",
		label: "Enter your submit reason",
		type: "text",
		value: "",
	},

	{
		name: "is_completed",
		label: "Enter your is completed",
		type: "select",
		label: "Select is completed",
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
		name: "is_passed",
		label: "Enter your is passed",
		type: "select",
		label: "Select is passed",
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
		name: "started_at",
		label: "Enter your started at",
		type: "datetime-local",
		value: "",
	},

	{
		name: "submitted_at",
		label: "Enter your submitted at",
		type: "datetime-local",
		value: "",
	},
];
