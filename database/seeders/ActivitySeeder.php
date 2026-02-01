<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('activities')->insert([
            [
                'actor_type'   => 'customer',
                'actor_id'     => 5,
                'action'       => 'order_created',
                'subject_type' => 'order',
                'subject_id'   => 101,
                'description'  => 'قام العميل بإنشاء طلب تغيير زيت',
                'old_values'   => null,
                'new_values'   => json_encode([
                    'status' => 'pending',
                    'service' => 'oil_change'
                ]),
                'ip_address'   => '192.168.1.10',
                'user_agent'   => 'Android App',
                'created_at'   => $now->subMinutes(30),
                'updated_at'   => $now->subMinutes(30),
            ],

            [
                'actor_type'   => 'admin',
                'actor_id'     => 1,
                'action'       => 'order_assigned',
                'subject_type' => 'order',
                'subject_id'   => 101,
                'description'  => 'قام المدير بإسناد الطلب للفني أحمد',
                'old_values'   => json_encode([
                    'technician_id' => null
                ]),
                'new_values'   => json_encode([
                    'technician_id' => 3
                ]),
                'ip_address'   => '192.168.1.2',
                'user_agent'   => 'Chrome / Windows',
                'created_at'   => $now->subMinutes(20),
                'updated_at'   => $now->subMinutes(20),
            ],

            [
                'actor_type'   => 'technician',
                'actor_id'     => 3,
                'action'       => 'order_started',
                'subject_type' => 'order',
                'subject_id'   => 101,
                'description'  => 'الفني بدأ تنفيذ طلب تغيير الزيت',
                'old_values'   => json_encode([
                    'status' => 'assigned'
                ]),
                'new_values'   => json_encode([
                    'status' => 'in_progress'
                ]),
                'ip_address'   => '192.168.1.55',
                'user_agent'   => 'Mobile App',
                'created_at'   => $now->subMinutes(10),
                'updated_at'   => $now->subMinutes(10),
            ],

            [
                'actor_type'   => 'technician',
                'actor_id'     => 3,
                'action'       => 'oil_changed',
                'subject_type' => 'order',
                'subject_id'   => 101,
                'description'  => 'تم تغيير الزيت بنجاح',
                'old_values'   => json_encode([
                    'status' => 'in_progress'
                ]),
                'new_values'   => json_encode([
                    'status' => 'completed'
                ]),
                'ip_address'   => '192.168.1.55',
                'user_agent'   => 'Mobile App',
                'created_at'   => $now->subMinutes(5),
                'updated_at'   => $now->subMinutes(5),
            ],

            [
                'actor_type'   => 'customer',
                'actor_id'     => 5,
                'action'       => 'payment_paid',
                'subject_type' => 'payment',
                'subject_id'   => 5001,
                'description'  => 'قام العميل بدفع قيمة الخدمة',
                'old_values'   => json_encode([
                    'payment_status' => 'unpaid'
                ]),
                'new_values'   => json_encode([
                    'payment_status' => 'paid',
                    'amount' => 30
                ]),
                'ip_address'   => '192.168.1.10',
                'user_agent'   => 'Android App',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);
    }
}