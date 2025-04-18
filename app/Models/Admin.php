<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $fillable = ['userNameAD', 'passwordAD', 'roleID'];
    protected $hidden = ['passwordAD'];
}