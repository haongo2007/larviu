<?php

namespace App\Larviu\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
	public $guard_name = 'api';

    protected $table="brand";

    protected $fillable = [
        'name', 'order', 'is_active', 'slug', 'creator_id', 'id_banner'
    ];
    public function Creator()
    {
    	return $this->belongsTo(User::class, 'creator_id','id');
    }
    public function Banner()
    {
        return $this->belongsTo(StorageFile::class,'id_banner','id')->where('type','brand');
    }
    public function Products()
    {
        return $this->belongsTo(Products::class,'id','brand_id');
    }
    public function productCheck()
    {
        return $this->Products()->exists();
    }
}
