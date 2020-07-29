<?php

namespace App\Larviu\Models;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    public $guard_name = 'api';

    protected $table="catalog";

    protected $fillable = [
        'name', 'order', 'is_active', 'id_parent', 'slug'
    ];
    public function Creator()
    {
    	return $this->belongsTo(User::class, 'creator','id');
    }
}
