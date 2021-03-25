<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
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

class OrderController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
    public function create(Request $request)
    {
    	$courses = Course::select('id', 'title', 'price', 'sale_price')->get();
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

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'courses' ));
    }

    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        // dd($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        // $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        $request->validate(
            ['user_id' => 'required', 'orderItems' => 'required'],
            [
                'user_id.required' => 'Customer Field is required.',
                'orderItems.required' => 'Item required.'
            ]
        );
        $items = array();
        $orderSubTotal = 0;
        $orderTotal = 0;
        foreach ($request->orderItems as $key => $orderItem) {
            $course = Course::find($orderItem);
            $items[] = [
                'user_id' => $request->user_id,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'price' => $course->price,
                'sell_price' => $course->sale_price,
            ];
            $orderSubTotal += isset($course->sale_price) && !empty($course->sale_price) ? $course->sale_price : $course->price;
        }
        $orderTotal = $orderSubTotal;

        $order = Order::create([
            'date' => $request->date,
            'user_id' => $request->user_id,
            'order_sub_total' => $orderSubTotal,
            'order_total' => $orderTotal,
            'order_status' => $request->order_status
        ]);

        $order->items()->createMany($items);
        /*ddd($items, $request->all());
        dd($request);*/
        $data = $order;



        // $data->questions()->createMany($request->ques);

        event(new BreadDataAdded($dataType, $data));

        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $data)) {
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

        // return $dataTypeContent->customer->name;
        // $courses  = Course::get();
        /*return $dataTypeContent = Quiz::with([
            'questions'
        ])->where('id', $id)->first();*/
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }
}
