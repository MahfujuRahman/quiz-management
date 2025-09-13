export default [
	{
		name: "title",
		label: "Enter your title",
		type: "text",
		value: "",
	},

	{
		name: "question_level",
		label: "Enter your question level",
		type: "select",
		label: "Select question level",
		multiple: false,
		data_list: [
			{
				label: "Easy",
				value: "easy",
			},
			{
				label: "Medium",
				value: "medium",
			},
			{
				label: "Hard",
				value: "hard",
			},
		],
		value: "",
	},

	{
		name: "mark",
		label: "Enter your mark",
		type: "number",
		step: "0.01",
		value: "",
	},

	{
		name: "is_multiple",
		label: "Enter your is multiple",
		type: "select",
		label: "Select is multiple",
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
		name: "session_year",
		label: "Enter your session year",
		type: "text",
		value: "",
	},
];
