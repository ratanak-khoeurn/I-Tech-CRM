<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = ['title', 'department', 'level', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
