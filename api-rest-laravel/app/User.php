<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    
    //MUY IMPORTANTE; ES LA TABLA CON LA QUE VA A TRABAJAR ESTE OBJETO!
    protected $table = 'users';  

    //TODOS LOS CAMPOS DE LA TABLA
    protected $fillable = [
        'id', 'name', 'surname','role', 'email', 'password', 'description', 'image', 'created_at', 'updated_at',
    ];

    //LOS CAMPOS OCULTOS PARA LOS RESPONSES
    protected $hidden = [
        'password', 'remember_token',
    ];

    //LOS CAMPOS CASTEADOS EN UN TIPO DE DATO CONCRETO
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    //RELACIONES
    //relacion de uno a muchos
    public function posts(){
        $this->hasMany('App/Post');
    }
}
