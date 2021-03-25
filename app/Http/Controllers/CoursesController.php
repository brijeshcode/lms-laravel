<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseCollection;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Section;
use App\Models\SectionItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;


class CoursesController extends \TCG\Voyager\Http\Controllers\VoyagerBaseController
{
	public function create(Request $request)
    {

    	$lessons 	= Lesson::get();
    	$quizes 	= Quiz::get();

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

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'lessons', 'quizes'));
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
        /*$temp= json_encode($request->pricing);
        $request->request->remove('pricing');
        $request->pricing = $temp;*/

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();



        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());
        $courseId = $data->id;

        $temp= json_encode($request->pricing);
        $request->request->remove('pricing');
        $request->pricing = $temp;
        $cource = Course::find($courseId);
        $cource->pricing = $temp;
        $cource->save();

        if (isset($request->curri)) {
            foreach ($request->curri as $key => $curriculum) {
                $curriculum['course_id'] = $courseId;
                $section = Section::create($curriculum);
                if (isset($curriculum['item'])) {
                    foreach ($curriculum['item'] as $key => $item) {
                        $item['section_id'] = $section->id;
                        $item['course_id'] = $courseId;
                        $items = SectionItems::create($item);
                    }
                }
            }
        }

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

    public function show(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        $isSoftDeleted = false;

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
            if ($dataTypeContent->deleted_at) {
                $isSoftDeleted = true;
            }
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        // Replace relationships' keys for labels and create READ links if a slug is provided.
        $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'read');

        // Check permission
        $this->authorize('read', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'read', $isModelTranslatable);

        $view = 'voyager::bread.read';

        if (view()->exists("voyager::$slug.read")) {
            $view = "voyager::$slug.read";
        }
        $dataTypeContent = Course::with([
            'curriculums.items.item',
            // 'curriculums.items.lessons',
            // 'curriculums.items.quizes',
        ])->first();

        // return $dataTypeContent;
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'isSoftDeleted'));
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

        // return $dataTypeContent;
        $lessons    = Lesson::get();
        $quizes     = Quiz::get();
        /*$dataTypeContent = Course::with([
            'curriculums.items',
            // 'curriculums.items.lessons',
            // 'curriculums.items.quizes',
        ])->where('id', $id)->first();*/

        /*return $dataTypeContent = Course::with([
            // 'curriculums.items',
            'curriculums.lessons'
            // 'curriculums.items.lessons',
            // 'curriculums.items.quizes',
        ])->where('id', $id)->first();*/
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable','lessons', 'quizes'));
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
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
        $temp= json_encode(array_values($request->pricing));
        $request->pricing = $temp;
        $cource = Course::find($id);
        $cource->pricing = $temp;
        if (isset($request->tax_classes_id)) {
            $cource->tax_classes_id = $request->tax_classes_id;
        }
        $cource->save();
        // dd($id);
        event(new BreadDataUpdated($dataType, $data));

        if (auth()->user()->can('browse', app($dataType->model_name))) {
            $redirect = redirect()->route("voyager.{$dataType->slug}.index");
        } else {
            $redirect = redirect()->back();
        }

        $oldSections = Section::select('id')->where('course_id', '=', $id)->get()->pluck('id');
        $oldSectionsItems = SectionItems::select('id')->where('course_id', '=', $id)->get()->pluck('id');
        // dd($oldSectionsItems);
        $oldSections = $oldSections->toArray();
        $oldSectionsItems = $oldSectionsItems->toArray();

        // dd($request->curri);
        if (isset($request->curri) && !empty($request->curri) ) {
            foreach ($request->curri as $key => $curriculum) {
                if (isset($curriculum['id']) && in_array($curriculum['id'], $oldSections)) {
                        $key = '';
                    $key = array_search ($curriculum['id'], $oldSections);
                    unset($oldSections[$key]);
                }
                // dd($curriculum);

                $curriculum['course_id'] = $id;
                $tempData = [
                    'course_id' => $id,
                    'title' => $curriculum['title'],
                    'section_order' => $curriculum['section_order'],
                    'description' => $curriculum['description'],
                ];

                $check = array();
                if (isset($curriculum['id'])) {
                    $tempData['id'] = $curriculum['id'];
                    $check = ['id' => $curriculum['id']];
                }

                $sectionId = Section::updateOrCreate($check,$tempData);

                if (isset($curriculum['item'])) {
                    foreach ($curriculum['item'] as $key => $item) {
                        if (isset($item['id']) && in_array($item['id'], $oldSectionsItems)) {
                            $key = '';
                            $key = array_search ($item['id'], $oldSectionsItems);
                            unset($oldSectionsItems[$key]);
                        }
                        $item['section_id'] = $sectionId->id;
                        $item['course_id'] = $id;
                        SectionItems::upsert($item , ['id'], ['display_order']);

                    }
                }
            }
        }

        Section::whereIn('id' , $oldSections)->delete();
        SectionItems::whereIn('id' , $oldSectionsItems)->delete();


        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);
        // dd($id);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL
            $ids[] = $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

            // Check permission
            $this->authorize('delete', $data);

            $model = app($dataType->model_name);
            if (!($model && in_array(SoftDeletes::class, class_uses_recursive($model)))) {
                $this->cleanup($dataType, $data);
            }
        }

        $displayName = count($ids) > 1 ? $dataType->getTranslatedAttribute('display_name_plural') : $dataType->getTranslatedAttribute('display_name_singular');

        $res = $data->destroy($ids);
        $data = $res
            ? [
                'message'    => __('voyager::generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('voyager::generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];

        if ($res) {
            event(new BreadDataDeleted($dataType, $data));
        }

        return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
    }

    // return cources collections for api
    public function getCources()
    {
        $cources = Course::with('curriculums.items')->where('status', 'active')->paginate();
        return $cources = new CourseCollection($cources);
    }

    public function getCource($courseId)
    {
        $course = Course::with('curriculums.items')->where('status', 'active')->find($courseId);
        $course = new CourseResource($course);
        return $course;
    }
}
