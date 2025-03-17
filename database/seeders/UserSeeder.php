<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Nayeemm',
                'email' => 'msrnayeem,@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '1096',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Alii',
                'email' => 'alii@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '102',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Karim',
                'email' => 'karim@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '203',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rahman',
                'email' => 'rahman@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '304',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hasan',
                'email' => 'hasan@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '405',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jamal',
                'email' => 'jamal@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '506',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kamal',
                'email' => 'kamal@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '607',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Faruk',
                'email' => 'faruk@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '708',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sajid',
                'email' => 'sajid@gmail.com',
                'shift_id' => 1,
                'zk_device_id' => '809',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
