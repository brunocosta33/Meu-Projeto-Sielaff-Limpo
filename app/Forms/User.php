<?php

namespace App\Forms;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Notification;

class User extends Authenticatable
{
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }


    use HasRolesAndPermissions;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'last_login'
    ];

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
        foreach($permissions_array as $permission){
            $roles = Role::whereIn('id',Permission_role::whereIn('permission_id',
            Permission::where('name',$permission)->pluck('id')->toarray()
            )->pluck('role_id')->toarray())->get();

            if(!$this->hasAnyRole($roles ?? []) && !$this->hasPermission($permission) ){
                return false;
            }
        }
        return true;
    }



    public function hasAnyPermissionsOrRole($permissions_array)
    {
        foreach($permissions_array as $permission){

            $roles = Role::whereIn('id',Permission_role::whereIn('permission_id',
            Permission::where('name',$permission)->pluck('id')->toarray()
            )->pluck('role_id')->toarray())->get();

            if($this->hasAnyRole($roles) || $this->hasPermission($permission) ){
                return true;
            }
        }
        return false;
    }

    public function hasAnyRole($roles_array){
        foreach ($roles_array as $role) {
            if($this->hasRole($role->name))
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
}
