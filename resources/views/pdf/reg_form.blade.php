<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration form</title>
    <link rel="stylesheet" href="{{public_path()}}/frontend/assets/styles/style.css">
    <style>
        /*
* Prefixed by https://autoprefixer.github.io
* PostCSS: v8.4.14,
* Autoprefixer: v10.4.7
* Browsers: last 4 version
*/

body {
            font-family: 'roboto';
        }

        .form_body::after {
            position: absolute;
            content: "";
            height: 100vh;
            width: 630px;
            top: 11vh;
            left: 50%;
            -webkit-transform: translateX(-50%);
                -ms-transform: translateX(-50%);
                    transform: translateX(-50%);
            background-image: url("{{public_path()}}/logo.jpg");
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 57%;
            background-blend-mode: multiply;
            opacity: 0.2;
            z-index: -1;
        }

        .form_section {
            margin: 40px 0px;
        }

        @media print {
            .form_section {
                margin: 0;
            }
        }

        .form .form_body {
            padding-top: 20px;
            width: 610px;
            margin: 0 auto;
            position: relative;
        }

        .form .form_body .error {
            color: red !important;
        }

        .form .form_body input:-internal-autofill-selected {
            background-color: unset !important;
        }

        .form .form_body .class h2 {
            font-size: 14px;
            text-align: right;
            font-weight: 700;
            padding-bottom: 26px;
        }

        .form .form_body .header {
            gap: 20px;
            position: relative;
            padding-bottom: 45px;
        }

        .form .form_body .header .logo {
            width: 105px;
            float: left;
        }

        .form .form_body .header .logo img {
            width: 100%;
            background-color: white;
        }

        .form .form_body .header .information {
            text-align: center;
            float: left;
        }

        .form .form_body .header .information h2 {
            font-size: 17px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .form .form_body .header .information h3 {
            font-size: 22px;
            font-weight: 700;
            padding: 17px 0px;
        }

        .form .form_body .header .information h4 {
            font-size: 17px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .form .form_body .header .photo{
            float: left;
        }
        .form .form_body .header .photo label {
            height: 110px;
            width: 110px;
            position: absolute;
            text-align: center;
            top: 35px;
            right: 0;
            border: 1px solid black;
            line-height: 20px;
        }

        .form .form_body .header .photo label input {
            display: none;
        }

        .form .form_body .header .photo label img {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
        }

        .form .form_body label,
        .form .form_body p {
            font-size: 14px;
            line-height: 20px;
            margin-bottom: 0;
        }

        .form .form_body .heading {
            font-size: 13px;
            line-height: 13px;
            background-color: #0070c0;
            color: white;
            letter-spacing: 0.5px;
            padding: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        .form .form_body table {
            width: 100%;
        }

        .form .form_body table td {
            padding: 5px;
            vertical-align: middle;
            font-size: 14px;
        }

        .form .form_body table td:nth-child(1) {
            width: 140px;
            padding-left: 5px;
        }

        .form .form_body .previous_attemp_module table td,
        .form .form_body .category table td,
        .form .form_body .education table td {
            text-align: center;
        }

        .form .form_body .previous_attemp_module table td input,
        .form .form_body .category table td input,
        .form .form_body .education table td input {
            text-align: center;
        }

        @media print {
            .form .form_body .category {
                page-break-after: always;
            }
        }

        .form .form_body .application_for table td {
            font-size: 13px;
            padding: 3px;
        }

        .form .form_body .application_for table td:nth-child(1),
        .form .form_body .application_for table td:nth-child(2) {
            text-align: center;
        }

        .form .form_body .essay {
            padding-top: 10px;
            padding-bottom: 0;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            gap: 30px;
            -webkit-box-align: center;
                -ms-flex-align: center;
                    align-items: center;
            font-weight: bold;
        }

        .form .form_body .essay .right {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            gap: 40px;
            -webkit-box-align: center;
                -ms-flex-align: center;
                    align-items: center;
        }

        .form .form_body .signature {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: justify;
                -ms-flex-pack: justify;
                    justify-content: space-between;
            font-size: 14px;
            margin-top: 30px;
            font-weight: 600;
        }

        .form .form_body .signature .right {
            padding-right: 145px;
        }

        .form .form_body .signature2 {
            padding-bottom: 30px;
        }

        .form .form_body .signature2 .right {
            padding-right: 0;
        }
    </style>
</head>

<body>
    <div class="form">
        <div class="form_body">
            <div class="class">
                <h2>Equivalent to AIB Form-128</h2>
            </div>

            <div class="header">
                <div class="logo">
                    <img src="{{ public_path('logo.jpg') }}" alt="">
                    {{-- <img src="/{{ ('logo.jpg') }}" alt=""> --}}
                </div>
                <div class="information" style="width: 390px;">
                    <h2 class="campus">
                        AERONAUTICAL INSTITUTE OF BANGLADESH
                    </h2>
                    <h3>
                        Registration Form
                    </h3>
                    <h4>
                        Part-66 Module Examination
                    </h4>
                </div>
                <div class="photo">
                    <img style="width: 120px;" src="{{public_path().'/'.$data->image}}" id="profile_pic_preview">
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="intro">
                <h2 class="heading">Instruction</h2>
                <p>
                    Please fill up the form in BLOCK CAPITALS as per NID/Birth Certificate/Passport using black ink.
                    Attach the required documents.
                </p>
            </div>
            <div class="p_details">
                <h2 class="heading">
                    Personal Details
                </h2>
                <style>
                    .div_table{}
                    .div_table .row{}
                    .div_table .right,
                    .div_table .left{
                        width: 170px;
                        border: 1px solid black;
                        border-bottom: 0;
                        float: left;
                        padding: 5px;
                        font-size: 14px;
                    }
                    .div_table .right{
                        border-left: 0;
                        width: 415px;
                    }
                    .div_table .left.last,
                    .div_table .right.last{
                        border-bottom: 1px solid black;
                    }
                </style>
                <div class="div_table">
                    <div class="row">
                        <div class="left">Full Name</div>
                        <div class="right">{{$data->full_name}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Father's Name</div>
                        <div class="right">{{$data->father_name}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Nid/B.C/Passport number</div>
                        <div class="right">{{$data->nid}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Date <span style="text-transform: lowercase;">of</span> Birth</div>
                        <div class="right">{{$data->date_of_birth}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Gender</div>
                        <div class="right">{{$data->gender}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Present Address </div>
                        <div class="right">{{$data->present_address}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Permanent Address </div>
                        <div class="right">{{$data->permanent_address}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Nationality</div>
                        <div class="right">{{$data->nationality}}</div>
                    </div>
                    <div class="row">
                        <div class="left">Phone Number</div>
                        <div class="right">{{$data->phone_number}}</div>
                    </div>
                    <div class="row">
                        <div class="left last">Email</div>
                        <div class="right last">{{$data->email}}</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            <div class="education">
                <h2 class="heading">
                    Educational Qualification(s)
                </h2>
                <div style="border: 1px solid black;">
                    <table>
                        <tbody>
                            <tr>
                                <td>SI. No.</td>
                                <td>Qualification</td>
                                <td>Group/Subject </td>
                                <td>Result/Grade </td>
                                <td>Passing Year </td>
                            </tr>
                            @if (isset($data->education_qualification) && collect($data->education_qualification)->count())
                                @foreach ($data->education_qualification as $key=>$item)
                                    @php
                                        $i = 1;
                                    @endphp
                                    <tr>
                                        <td>
                                            {{$item->si}}
                                        </td>
                                        <td>
                                            {{$item->qualification}}
                                        </td>
                                        <td>
                                            {{$item->subject}}
                                        </td>
                                        <td>
                                            {{$item->grade}}
                                        </td>
                                        <td>
                                            {{$item->year}}
                                        </td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            @else
                                @for ($i = 1; $i <= 3; $i++)
                                    <tr>
                                        <td>
                                            <input type="text" value="{{$i}}" readonly
                                                name="education_qualification[q{{$i}}][si]"
                                                placeholder="...">
                                        </td>
                                        <td>
                                            <input type="text" name="education_qualification[q{{$i}}][qualification]"
                                                placeholder="...">
                                        </td>
                                        <td>
                                            <input type="text" name="education_qualification[q{{$i}}][subject]" placeholder="...">
                                        </td>
                                        <td>
                                            <input type="text" name="education_qualification[q{{$i}}][grade]" placeholder="...">
                                        </td>
                                        <td>
                                            <input type="text" onfocus="event.target.showPicker()"
                                                name="education_qualification[q{{$i}}][year]" placeholder="...">
                                        </td>
                                    </tr>
                                @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
                <p>
                    N.B.: HSC/Diploma/Equivalent Qualification must be mentioned.
                </p>
            </div>

            <div class="category">
                <h2 class="heading">
                    Category / Subcategory of Examination (Tick as Appropriate)
                </h2>
                <div>
                    <div>
                        <div id="category_of_exam"></div>
                    </div>
                </div>
                <div style="border: 1px solid black;">
                    <table>
                        <tbody>

                            <tr>
                                <td>Please Select</td>
                                <td colspan="2">Category / Subcategory</td>
                            </tr>
                            <tr>
                                <td>
                                    @php
                                        $checked = array_search("B1.1 - Aeroplane Turbine",$data->category_of_exam) !== false;
                                    @endphp
                                    @if (isset($data->category_of_exam) && $checked)
                                        <img src="{{public_path()}}/check.png" style="width:17px;" alt="">
                                    @else
                                        <img src="{{public_path()}}/uncheck.png" style="width:12px;" alt="">
                                    @endif
                                </td>
                                <td>
                                    B1.1
                                </td>
                                <td>
                                    Aeroplane Turbine
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @php
                                        $checked = array_search("B1.3 - Helicopter Turbine",$data->category_of_exam) !== false;
                                    @endphp
                                    @if (isset($data->category_of_exam) && $checked)
                                    <img src="{{public_path()}}/check.png" style="width:17px;" alt="">
                                    @else
                                        <img src="{{public_path()}}/uncheck.png" style="width:12px;" alt="">
                                    @endif
                                </td>
                                <td>
                                    B1.3
                                </td>
                                <td>
                                    Helicopter Turbine
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @php
                                        $checked = array_search("B2 - Avionics",$data->category_of_exam) !== false;
                                    @endphp
                                    @if (isset($data->category_of_exam) && $checked)
                                        <img src="{{public_path()}}/check.png" style="width:17px;" alt="">
                                    @else
                                        <img src="{{public_path()}}/uncheck.png" style="width:12px;" alt="">
                                    @endif
                                </td>
                                <td>
                                    B2
                                </td>
                                <td>
                                    Avionics
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="application_for">
                <h2 class="heading">
                    Application for:
                </h2>
                <p>
                    Please tick the box corresponding to the module that you like to sit for:
                </p>
                <div>
                    <div id="application_for"></div>
                </div>
                <div style="border: 1px solid black;">
                    <table>
                        <tbody>
                            <tr>
                                <td>Please Select</td>
                                <td>Module Number</td>
                                <td>Module Name</td>
                            </tr>
                            @php
                                $application_fors = [
                                    [
                                        "module" => "1",
                                        "name" => "Mathematics"
                                    ],
                                    [
                                        "module" => "2",
                                        "name" => "Physics"
                                    ],
                                    [
                                        "module" => "3",
                                        "name" => "Electrical Fundamentals"
                                    ],
                                    [
                                        "module" => "4",
                                        "name" => "Electronic Fundamentals"
                                    ],
                                    [
                                        "module" => "5",
                                        "name" => "Digital Techniques / Electronic Instrument Systems"
                                    ],
                                    [
                                        "module" => "6",
                                        "name" => "Materials & Hardware"
                                    ],
                                    [
                                        "module" => "7A",
                                        "name" => "Maintenance Practices"
                                    ],
                                    [
                                        "module" => "8",
                                        "name" => "Basic Aerodynamics"
                                    ],
                                    [
                                        "module" => "9A",
                                        "name" => "Human Factors"
                                    ],
                                    [
                                        "module" => "10",
                                        "name" => "Aviation Legislation"
                                    ],
                                    [
                                        "module" => "11A",
                                        "name" => "Aeroplane Aerodynamics, Structures and Systems"
                                    ],
                                    // [
                                    // "module" => "12",
                                    // "name" => "Helicopter Aerodynamics, Structures and Systems"
                                    // ],
                                    [
                                        "module" => "13",
                                        "name" => "Aircraft Aerodynamics, Structures and Systems"
                                    ],
                                    [
                                        "module" => "12",
                                        "name" => "Helicopter Aerodynamics, Structures and Systems"
                                    ],
                                    [
                                        "module" => "14",
                                        "name" => "Propulsion"
                                    ],
                                    [
                                        "module" => "15",
                                        "name" => "Gas Turbine Engines"
                                    ],
                                    [
                                        "module" => "17A",
                                        "name" => "Propellers"
                                    ],
                                ];
                            @endphp
                            @foreach ($application_fors as $key => $value)
                            <tr>
                                <td>
                                    <div>
                                        @php
                                            $input_value = $value["module"]. ' - ' .$value["name"];
                                            if(isset($data->application_for)){
                                                $check = array_search($input_value, $data->application_for) != false;
                                            }
                                        @endphp
                                        @if (isset($data->application_for) && $check)
                                            <img src="{{public_path()}}/check.png" style="width:17px;" alt="">
                                        @else
                                            <img src="{{public_path()}}/uncheck.png" style="width:12px;" alt="">
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $value["module"] }}
                                </td>
                                <td>
                                    {{ $value["name"] }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="essay">
                <div class="left" style="float:left;width:100px;">
                    <b>ESSAY</b>
                </div>
                <div class="right" style="float:left;width: 380px;">
                    @php
                    $essays = ["Module 7A", "Module 9A", "Module 10"];
                    @endphp

                    @foreach ($essays as $item)
                    <label for="">
                        @if (isset($data->essay) && array_search($item,$data->essay) !== false)
                        <img src="{{public_path()}}/check.png" style="width:17px;" alt="">
                        @else
                        <img src="{{public_path()}}/uncheck.png" style="width: 12px;" alt="">
                        @endif
                        <?= $item ?>
                    </label>
                    @endforeach
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="previous_attemp_module">
                <h2 class="heading">
                    Information of Previously Attempted Modules:
                </h2>
                <div style="border: 1px solid black;">
                    <table>
                        <tbody>
                            <tr>
                                <td>Module No.</td>
                                <td>Date of 1st Attempt & Exam Center</td>
                                <td>Date of 2nd Attempt & Exam Center</td>
                                <td>Date of 3rd Attempt & Exam Center</td>
                            </tr>
                            @foreach ($data->previous_attemped as $item)
                                @php
                                    $i = 1;
                                @endphp
                                <tr>
                                    <td>
                                        {{$item->module_no}}
                                    </td>
                                    <td>
                                        {{$item->date_1_center}}
                                    </td>
                                    <td>
                                        {{$item->date_2_center}}
                                    </td>
                                    <td>
                                        {{$item->date_3_center}}
                                    </td>
                                </tr>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="previous_attemp_module">
                <h2 class="heading">
                    Declaration:
                </h2>
                <p>
                    I hereby declare that the information I have submitted is correct. I have not taken the above module
                    exam(s) within the last 90 days. I shall abide by the rules and regulations of the Aeronautical
                    Institute of
                    Bangladesh (AIB).
                </p>
            </div>

            <div class="signature">
                <div class="left" style="float: left;width: 300px;">
                    <b>Signature of Applicant</b>
                </div>
                <div class="right" style="float:right;width:300px;text-align:right;">
                    <b>Date:</b>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="previous_attemp_module">
                <h2 class="heading">
                    For office use only:
                </h2>
                <div>
                    <b>Registration Number:</b> <br> <br>
                    <b>Comments:</b>
                </div>
            </div>

            <div class="">
                <div style="float: left;width: 300px;">
                    <b>Signature of Admin Officer</b>
                </div>
                <div  style="float: left; width: 300px;text-align:right;">
                    <b>Signature of Training Manager</b>
                </div>
                <div style="clear: both;"></div>
            </div>

        </div>
    </div>

</body>

</html>
