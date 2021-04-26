<?php

namespace App\Models;

use App\Models\PackageItem;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\TaxClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;
    use HasFactory;

	protected $fillable = ['name', 'class_type_id', 'service_id', 'service_type_id', 'starts', 'is_taxable', 'tax_class_id'];

    public static function boot() {
        parent::boot();

        static::deleting(function($packages) {
        	$packages->items()->each(function($item){
             	// $section->items()->delete();
             	$item->delete();
        	});
        });
    }

    public function taxes()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassType::class, 'class_type_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function items() {
        return $this->hasMany(PackageItem::class, 'package_id');
    }

}
