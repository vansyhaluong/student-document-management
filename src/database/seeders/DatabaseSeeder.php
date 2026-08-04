<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            DocumentTypeSeeder::class,
        ]);

        User::factory()->create([
            'username' => 'admin',
            'password_hash' => Hash::make('admin123'),
            'full_name' => 'Quản trị viên hệ thống',
            'email' => 'admin@tdc.edu.vn',
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::factory()->create([
            'username' => 'thuky01',
            'password_hash' => Hash::make('thuky123'),
            'full_name' => 'Thư ký khoa',
            'email' => 'thuky01@tdc.edu.vn',
            'role' => 'secretary',
            'is_active' => true,
        ]);

        User::factory()->create([
            'username' => 'nhanvien01',
            'password_hash' => Hash::make('nhanvien123'),
            'full_name' => 'Nhân viên tiếp nhận',
            'email' => 'nhanvien01@tdc.edu.vn',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
