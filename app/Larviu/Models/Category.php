<?php

namespace App\Larviu\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $guard_name = 'api';

    protected $table="category";

    protected $fillable = [
        'name', 'order', 'is_active', 'id_parent', 'slug', 'creator', 'id_banner'
    ];
    public function Creator()
    {
    	return $this->belongsTo(User::class, 'creator','id');
    }
    public function Parent()
    {
        return $this->hasOne(self::class,'id','id_parent');
    }
    public function Children()
    {
        return $this->hasMany(self::class,'id_parent');
    }
    public function Banner()
    {
        return $this->belongsTo(StorageFile::class,'id_banner')->where('type','category');
    }
}
