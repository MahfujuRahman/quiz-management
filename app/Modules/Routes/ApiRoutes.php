<?php

/*
|--------------------------------------------------------------------------
| Auth Management Module
|--------------------------------------------------------------------------
*/

include_once base_path("app/Modules/Management/Auth/Routes/Route.php");
/*
|--------------------------------------------------------------------------
| Dashboard data
|--------------------------------------------------------------------------
*/
include_once base_path("app/Modules/Management/Dashboard/Routes/Route.php");
/*
|--------------------------------------------------------------------------
| Setting Management Module
|--------------------------------------------------------------------------
*/

include_once base_path("app/Modules/Management/SettingManagement/WebsiteSettings/Routes/Route.php");

/*
|--------------------------------------------------------------------------
| User Management Module
|--------------------------------------------------------------------------
*/

include_once base_path("app/Modules/Management/UserManagement/User/Routes/Route.php");
include_once base_path("app/Modules/Management/UserManagement/Role/Routes/Route.php");
/*
|--------------------------------------------------------------------------
| Others Management Module
|--------------------------------------------------------------------------
*/

include_once base_path("app/Modules/Management/QuizManagement/QuizQuestionTopic/Routes/Route.php");
include_once base_path("app/Modules/Management/QuizManagement/QuizQuestion/Routes/Route.php");
include_once base_path("app/Modules/Management/QuizManagement/Quiz/Routes/Route.php");
include_once base_path("app/Modules/Management/QuizManagement/QuizSubmissionResult/Routes/Route.php");
include_once base_path("app/Modules/Management/QuizManagement/PublicQuiz/Routes/Route.php");
include_once base_path("app/Modules/Management/QuizManagement/QuizParticipation/Routes/Route.php");

