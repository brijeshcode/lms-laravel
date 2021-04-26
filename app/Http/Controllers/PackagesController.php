<?php

namespace App\Http\Controllers;

use App\Http\Resources\PackageCollection;
use App\Models\Batches;
use App\Models\ClassType;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\TaxClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;

class PackagesController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
    public function create(Request $request)
    {

        $slug 		= $this->getSlug($request);

        $dataType 	= Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;

        foreach ($dataType->addRows as $key => $row) {
            $dataType->addRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

		if (view()->exists("voyager::$slug.edit-add")) {
		      $view = "voyager::$slug.edit-add";
		}

        $classTypes = ClassType::get();
        $servies = Service::with('types')->get();
        $batches = Batches::get();

        $taxClasses = TaxClass::get();
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable' , 'servies', 'taxClasses' , 'batches', 'classTypes' ));
    }

    /**
     * POST BRE(A)D - Store data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */

    public function store(Request $request)
    {

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        // dd($request);
        if (!isset($request->batch) || !is_array($request->batch) || empty($request->batch)) {
            return 'no use';
        }
        $pData = array();
        $package = $request;
        $serType = ServiceType::select('service_id')->find($package->service_type_id);
        $batches = $package->batch;

        foreach ($batches as $bkey => $batch) {
            $batch_id = $request->batch_selected[$bkey]['batch_id'];
            foreach ($batch as $pkey => $price) {

                $pData[]= [
                    'batch_id' => $batch_id,
                    'week_days' => $price['week_days'],
                    'duration_month' => $price['duration_month'],
                    'total_classes' => $price['total_classes'],
                    'cost_per_class' => $price['cost_per_class'],
                    'total_cost' => $price['total_cost'],
                    'total_hours' => $price['total_hours'],
                    'class_duration_note' => $price['class_duration_note'],
                    'additional_note' => $price['additional_note']
                ];

            }
        }

        // dd($pData,'still here');

        $package = Package::create([
            'name' => $package->name,
            'class_type_id' => $package->class_type_id,
            'service_id' => $serType->service_id,
            'service_type_id' => $package->service_type_id,
            'is_taxable' => $package->is_taxable,
            'tax_class_id' => $package->tax_class_id
        ]);

        $package = $package->items()->createMany($pData);

        // event(new BreadDataAdded($dataType, $data));

        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $package)) {
                $redirect = redirect()->route("voyager.{$dataType->slug}.index");
            } else {
                $redirect = redirect()->back();
            }

            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }


    public function edit(Request $request, $id)
    {

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $model = $model->withTrashed();
            }

            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $model = $model->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$model, 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'edit', $isModelTranslatable);


        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        $classTypes = ClassType::get();
        $servies = Service::with('types')->get();
        $batches = Batches::get();
        $batchItems = array();
        foreach ($dataTypeContent->items as $key => $items) {

            $batchItems[$items->batch_id][] = [
                'id' => $items->id,
                'batch_id' => $items->batch_id,
                'week_days' => $items->week_days,
                'duration_month' => $items->duration_month,
                'total_classes' => $items->total_classes,
                'cost_per_class' => $items->cost_per_class,
                'total_cost' => $items->total_cost,
                'total_hours' => $items->total_hours,
                'class_duration_note' => $items->class_duration_note,
                'additional_note' => $items->additional_note
            ];
        }
        // return $batchItems;
        $taxClasses = TaxClass::get();

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'batches','servies', 'batchItems', 'classTypes', 'taxClasses'));
    }


    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

        $model = app($dataType->model_name);
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
            $model = $model->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $data = $model->withTrashed()->findOrFail($id);
        } else {
            $data = $model->findOrFail($id);
        }

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();
        // $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        $package = Package::findOrFail($id);

        $serType = ServiceType::select('service_id')->findOrFail($request->service_type_id);

        $package->name = $request->name;
        $package->service_id = $serType->service_id;
        $package->class_type_id = $request->class_type_id;
        $package->service_type_id = $request->service_type_id;
        $package->is_taxable = $request->is_taxable;
        $package->tax_class_id = $request->tax_class_id;

        $package->save();


        $batchItems = array();
        foreach ($package->items as $key => $items) {
            $batchItems[$items->id] = [
                'id' => $items->id
            ];
        }

        $batches = $request->batch;
        foreach ($batches as $bkey => $batch) {
            $batch_id = $request->batch_selected[$bkey]['batch_id'];
            foreach ($batch as $pkey => $price) {

                $pData = [
                    'batch_id' => $batch_id,
                    'week_days' => $price['week_days'],
                    'duration_month' => $price['duration_month'],
                    'total_classes' => $price['total_classes'],
                    'cost_per_class' => $price['cost_per_class'],
                    'total_cost' => $price['total_cost'],
                    'total_hours' => $price['total_hours'],
                    'class_duration_note' => $price['class_duration_note'],
                    'additional_note' => $price['additional_note']
                ];

                if (isset($price['package_item'])) {
                    // update id
                    $packageItem = PackageItem::where('id','=' ,$price['package_item'])
                    ->update($pData);
                    // $packageItem = PackageItem::findOrFail($price['package_item']);

                    unset($batchItems[$price['package_item']]);
                }else{
                    $pData['package_id'] = $id;
                    $packageItem = PackageItem::create($pData);
                }

            }

        }

        if (!empty($batchItems)) {
            PackageItem::destroy($batchItems);
        }
        // dd($id);

        event(new BreadDataUpdated($dataType, $data));

        if (auth()->user()->can('browse', app($dataType->model_name))) {
            $redirect = redirect()->route("voyager.{$dataType->slug}.index");
        } else {
            $redirect = redirect()->back();
        }

        // $data->questions()->sync($request->ques);

        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }

    public function getApiPackages()
    {
        $packages = Package::with(['taxes', 'class' , 'service', 'type','items' ])->paginate();
        return $packages = new PackageCollection($packages);
    }

}
