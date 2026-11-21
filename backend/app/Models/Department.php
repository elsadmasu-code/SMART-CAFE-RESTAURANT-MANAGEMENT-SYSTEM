<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'branch_id',
        'name_en',
        'name_am',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
