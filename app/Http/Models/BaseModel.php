<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    public $incrementing = true;
    public $timestamps = true;
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $perPage = 20;
    protected $guarded = [];
}
