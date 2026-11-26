<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function assignedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'assigned_staff_id');
    }

    /** Campuses a staff account is allowed to work in. */
    public function campuses(): BelongsToMany
    {
        return $this->belongsToMany(Campus::class, 'campus_user');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * @return list<int>
     */
    public function campusIds(): array
    {
        return $this->campuses->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function hasCampusAccess(int $campusId): bool
    {
        return in_array($campusId, $this->campusIds(), true);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role !== null && in_array($this->role->name, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin', 'admin');
    }

    public function canManageStudents(): bool
    {
        return $this->hasRole('super_admin', 'admin', 'staff');
    }

    public function isViewer(): bool
    {
        return $this->hasRole('viewer');
    }

    /** Viewers and (optionally) staff cannot see restricted/confidential fields. */
    public function canViewSensitiveData(): bool
    {
        return $this->hasRole('super_admin', 'admin');
    }

    /** Admins may manage every student; staff are limited to students assigned to them. */
    public function canAccessStudent(?Student $student): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return $student !== null && (int) $student->assigned_staff_id === (int) $this->id;
    }
}
