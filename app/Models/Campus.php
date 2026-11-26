<?php

namespace App\Models;

use App\Models\Concerns\CampusScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use CampusScoped;
    use SoftDeletes;

    public static function campusScopeColumn(): string
    {
        return 'id';
    }

    protected $fillable = [
        'name',
        'code',
        'phone',
        'is_active',
        'campus_kh',
        'campus_en',
        'campus_label',
        'campus_code',
        'campus_number',
        'contact_person',
        'contact_tel',
        'email',
        'location_kh',
        'location_en',
        'address',
        'latitude',
        'longitude',
        'image',
        'order',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function studentCourses()
    {
        return $this->hasMany(StudentCourse::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'campus_id');
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    /** Staff accounts assigned to this campus. */
    public function users()
    {
        return $this->belongsToMany(User::class, 'campus_user');
    }
}
