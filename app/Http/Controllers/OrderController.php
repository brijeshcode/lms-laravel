<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use App\Models\Course;

class OrderController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
    public function create(Request $request)
    {
    	$courses = Course::select('id', 'title', 'price', 'sale_price')->get();
    	/*$temp = array();
    	if (!empty($courses)) {
    		foreach ($courses as $key => $course) {
    			$temp[$course->id] = $course;
    		}
    	}
    	return $courses = $temp;*/

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
}
