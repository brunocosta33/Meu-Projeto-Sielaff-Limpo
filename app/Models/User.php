<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;
use App\Models\TaskSchedule;

class User extends Authenticatable implements LaratrustUser
{
    use HasRolesAndPermissions;
    use Notifiable;
    use SoftDeletes;

   

    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }



    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'last_login', 'role_id', 'lang'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class);
    }



    public function hasPermissionsOrRole($permissions_array)
    {
        foreach ($permissions_array as $permission) {
            $roles = Role::whereIn('id', Permission_role::whereIn('permission_id',
                Permission::where('name', $permission)->pluck('id')->toarray()
            )->pluck('role_id')->toarray())->get();

            if (!$this->hasAnyRole($roles ?? []) && !$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }


    public function hasAnyPermissionsOrRole($permissions_array)
    {

        foreach ($permissions_array as $permission) {

            $roles = Role::whereIn('id', Permission_role::whereIn(
                'permission_id',
                Permission::where('name', $permission)->pluck('id')->toarray()
            )->pluck('role_id')->toarray())->get();
            if ($this->hasAnyRole($roles) || $this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }


    public function hasAnyRole($roles_array)
    {
        foreach ($roles_array as $role) {
            if ($this->hasRole($role->name))
                return true;
        }
        return false;
    }
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
        // Notification::send($user, new \App\Notifications\MailResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

        
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
    }

    public function taskSchedules()
    {
        return $this->belongsToMany(TaskSchedule::class)
            ->withPivot('estado', 'comentarios', 'data_conclusao')
            ->withTimestamps();
    }



}
