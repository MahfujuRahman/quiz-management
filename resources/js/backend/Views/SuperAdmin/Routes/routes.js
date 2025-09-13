//app layout
import Layout from "../Layouts/Layout.vue";
//Dashboard
import Dashboard from "../Management/Dashboard/Dashboard.vue";
//SettingsRoutes
import SettingsRoutes from "../Management/Settings/setup/routes.js";
//UserRoutes
import UserRoutes from "../Management/UserManagement/User/setup/routes.js";
//routes
import QuizParticipationRoutes from '../../../GlobalManagement/QuizManagement/QuizParticipation/setup/routes.js';

import QuizSubmissionResultRoutes from '../../../GlobalManagement/QuizManagement/QuizSubmissionResult/setup/routes.js';
import QuizRoutes from '../../../GlobalManagement/QuizManagement/Quiz/setup/routes.js';
import QuizQuestionRoutes from '../../../GlobalManagement/QuizManagement/QuizQuestion/setup/routes.js';
import QuizQuestionTopicRoutes from '../../../GlobalManagement/QuizManagement/QuizQuestionTopic/setup/routes.js';

const routes = {
  path: "",
  component: Layout,
  children: [
    {
      path: "dashboard",
      component: Dashboard,
      name: "adminDashboard",
    },
    //management routes
        QuizParticipationRoutes,

        QuizSubmissionResultRoutes,
        QuizRoutes,
        QuizQuestionRoutes,
        QuizQuestionTopicRoutes,
           
    //user routes
    UserRoutes,
    //settings
    SettingsRoutes,
  ],
};

export default routes;
