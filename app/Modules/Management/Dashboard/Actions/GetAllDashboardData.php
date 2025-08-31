<?php

namespace App\Modules\Management\Dashboard\Actions;

class GetAllDashboardData
{


    public static function execute()
    {
        try {



            // $data = [
            //     'total_users' => \App\Modules\Management\UserManagement\User\Models\Model::count(),
            //     'total_projects' => \App\Modules\Management\ProjectManagement\Project\Models\Model::count(),
            //     'total_todos' => \App\Modules\Management\TodoManagement\AddTodo\Models\Model::count(),
            //     'total_credentials' => \App\Modules\Management\TodoManagement\Credential\Models\Model::count(),
            //     'total_attendance' => \App\Modules\Management\AttendanceManagement\Attendance\Models\Model::count(),
            //     'total_meetings' => \App\Modules\Management\MeetingManagement\Meeting\Models\Model::count(),
            //     'total_meeting_agendas' => \App\Modules\Management\MeetingManagement\MeetingAgenda\Models\Model::count(),
            // ];
            $data = [
                'getTotalIncomes' => 500000,
                'getTotalExpenses' => 300000,
                'getTotalProducts' => 150,
                'getTotalPurchaseOrders' => 75,
                'getTotalSalesOrders' => 100,
                'getTotalCustomers' => 200,
                'getTotalSuppliers' => 50,
                'getTotalUsers' => 500,
                'getTotalWarehouses' => 20,
            ];

            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }
}
