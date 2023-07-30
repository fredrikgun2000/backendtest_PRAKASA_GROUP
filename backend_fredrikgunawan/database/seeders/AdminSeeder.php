<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\User\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $user = new User;
            $user->email = 'admin123@gmail.com';
            $user->password = Hash::make('Admin123@');
            $user->role = 'admin';
            $user->save();
    
            $admin = new Admin;
            $admin->user_id = $user->id;
            $admin->first_name = 'Admin';
            $admin->last_name = 'Admin';
            $admin->save();
            
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
