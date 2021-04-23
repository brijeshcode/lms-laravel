<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageItem extends Model
{
    use SoftDeletes;
    use HasFactory;

	protected $fillable = ['package_id', 'batch_id', 'week_days', 'duration_month', 'total_classes', 'cost_per_class', 'total_cost','total_hours' , 'class_duration_note', 'additional_note'];
}
