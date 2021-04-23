<?php

namespace App\Models;

use App\Models\PackageItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;
    use HasFactory;

	protected $fillable = ['name', 'class_type_id', 'service_id', 'service_type_id', 'starts'];

    public function items() {
        return $this->hasMany(PackageItem::class, 'package_id');
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($packages) {
        	$packages->items()->each(function($item){
             	// $section->items()->delete();
             	$item->delete();
        	});
        });
    }
}
