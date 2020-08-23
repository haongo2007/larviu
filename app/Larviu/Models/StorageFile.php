<?php

namespace App\Larviu\Models;

use Illuminate\Database\Eloquent\Model;

class StorageFile extends Model
{
	public $guard_name = 'api';

    protected $table="storage_file";

    protected $fillable = [
        'name', 'url', 'type', 'extension',
    ];
}
