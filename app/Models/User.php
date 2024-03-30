<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    use HasFactory;
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'mobile',
        'password',
        'otp',
    ];

    protected $attributes = [
        'otp' => '0',
    ];



    public function todos(){
        return $this->hasMany(Todo::class);
    }
    public function categories(){
        return $this->hasMany(Category::class);
    }

}
