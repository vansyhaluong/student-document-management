<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'username',
        'password_hash',
        'full_name',
        'email',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Bảng users dùng cột password_hash thay vì password mặc định của Laravel,
     * nên phải override lại tên cột auth dùng để so khớp mật khẩu khi login.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
