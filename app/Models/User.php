<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->role_id && !$user->roles()->where('roles.id', $user->role_id)->exists()) {
                $user->roles()->syncWithoutDetaching([$user->role_id]);
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    /**
     * Relasi many-to-many ke roles (via pivot role_user)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Relasi direct ke role via foreign key role_id (fallback & backward-compatible)
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Ambil role pertama user (helper)
     */
    public function getPrimaryRole(): ?Role
    {
        return $this->roles->first() ?? $this->role;
    }

    /**
     * Ambil nama role pertama sebagai string
     */
    public function getRoleName(): string
    {
        $role = $this->getPrimaryRole();
        return $role ? $role->role : 'guest';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if user has specific role (with canonical synonyms support)
     */
    public function hasRole(string|array $roles): bool
    {
        $userRoles = $this->roles->pluck('role')->map(fn($r) => strtolower(trim($r)))->toArray();
        if ($this->role && !in_array(strtolower(trim($this->role->role)), $userRoles)) {
            $userRoles[] = strtolower(trim($this->role->role));
        }

        // Canonical synonyms mapping both Indonesian and English documentation roles
        $aliasMap = [
            'superadmin'             => ['superadmin', 'super admin', 'system admin', 'system administrator'],
            'system administrator'   => ['superadmin', 'super admin', 'system admin', 'system administrator'],
            'system admin'           => ['superadmin', 'super admin', 'system admin', 'system administrator'],
            'super admin'            => ['superadmin', 'super admin', 'system admin', 'system administrator'],
            'kepala gudang'          => ['kepala gudang', 'kepala_gudang', 'warehouse manager'],
            'warehouse manager'      => ['kepala gudang', 'kepala_gudang', 'warehouse manager'],
            'admin gudang'           => ['admin gudang', 'admin_gudang'],
            'teknisi'                => ['teknisi', 'technician'],
            'viewer'                 => ['viewer', 'pengawas', 'transparency'],
            'department manager'     => ['department manager', 'dept manager', 'kepala bidang', 'viewer'],
            'dept manager'           => ['department manager', 'dept manager', 'kepala bidang', 'viewer'],
        ];

        $targetRoles = is_array($roles) ? $roles : [$roles];
        $expandedTargets = [];
        foreach ($targetRoles as $tr) {
            $normalized = strtolower(trim($tr));
            $expandedTargets[] = $normalized;
            if (isset($aliasMap[$normalized])) {
                $expandedTargets = array_merge($expandedTargets, $aliasMap[$normalized]);
            }
        }

        foreach ($userRoles as $ur) {
            if (in_array($ur, $expandedTargets)) {
                return true;
            }
            if (isset($aliasMap[$ur])) {
                foreach ($aliasMap[$ur] as $alias) {
                    if (in_array($alias, $expandedTargets)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // Superadmin bypass — akses semua
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->isActive()) {
            return false;
        }

        // Cek permission dari semua role yang dimiliki user
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('name', $permissionName)) {
                return true;
            }
        }

        if ($this->role && $this->role->permissions->contains('name', $permissionName)) {
            return true;
        }

        return false;
    }

    /**
     * Direct permissions (model_has_permissions)
     */
    public function directPermissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'model_has_permissions',
            'model_id',
            'permission_id'
        )->where('model_type', static::class);
    }
}
