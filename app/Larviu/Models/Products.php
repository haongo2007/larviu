<?php

namespace App\Larviu\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    public $guard_name = 'api';

    protected $table="products";
}
