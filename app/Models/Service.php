<?php

namespace App\Models;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    use HasFactory;

    public function types() {
        return $this->hasMany(ServiceType::class, 'service_id');
    }
}
