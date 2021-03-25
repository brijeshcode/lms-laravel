@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                            class="form-edit-add"
                            action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                            method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                        @if($edit)
                            {{ method_field("PUT") }}
                        @endif

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Adding / Editing -->
                            @php
                                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
                            @endphp

                            {{-- <div class="form-group col">
                                <label class="control-label" for="tinymce.init(window.voyagerTinyMCE.getConfig());name">Title</label>
                                <input type="text" class="form-control" name="title" placeholder="Title">
                            </div>

                            <div class="form-group col">
                                <label class="control-label" for="name">Description</label>
                                <textarea class="form-control rich_text_box" name="description"></textarea>

                            </div> --}}

                            @foreach($dataTypeRows as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $display_options = $row->details->display ?? NULL;
                                    if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                        $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                                    }
                                @endphp
                                @if (isset($row->details->legend) && isset($row->details->legend->text))
                                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                                @endif

                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @if ($errors->has($row->field))
                                        @foreach ($errors->get($row->field) as $error)
                                            <span class="help-block">{{ $error }}</span>
                                        @endforeach
                                    @endif
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    @if (isset($row->details->view))
                                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add'), 'view' => ($edit ? 'edit' : 'add'), 'options' => $row->details])
                                    @elseif ($row->type == 'relationship')
                                        @include('voyager::formfields.relationship', ['options' => $row->details])
                                    @else
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                    @endif

                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach

                                </div>
                            @endforeach

                            {{-- <div class="panel panel-default">
                              <div class="panel-body">
                                Basic panel example
                              </div>
                            </div> --}}

                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-bordered panel-default">
                                        <div class="panel-heading" style="padding-left: 5px;"><h3>Price Veriations </h3> </div>
                                        <div class="panel-body">
                                            @php
                                                $pIndex = 0;
                                            @endphp
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Duration</th>
                                                        <th>Price</th>
                                                        <th>Sell Price</th>
                                                        <th><span class="btn btn-success" onclick="addpriceVeriations()" >Add</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="priceVeriations" >
                                                    @if ($edit && $dataTypeContent->pricing)
                                                        @foreach (json_decode($dataTypeContent->pricing) as $pricing)
                                                            <tr id="price-veriation-{{ $pIndex }}">
                                                                <td>
                                                                    <select class="form-control" name="pricing[{{ $pIndex }}][price_type]">
                                                                        <option>Days</option>
                                                                        <option>Weeks</option>
                                                                        <option>Months</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="1" min="1" name="pricing[{{ $pIndex }}][duration]" value="{{ $pricing->duration }}" required class="form-control">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" min="1" name="pricing[{{ $pIndex }}][price]" required class="form-control" value="{{ $pricing->price }}">
                                                                </td>
                                                                <td>
                                                                    <input type="number" step="0.01" min="1" name="pricing[{{ $pIndex }}][sell_price]" required class="form-control" value="{{ $pricing->sell_price }}">
                                                                </td>

                                                                <td>
                                                                    <span class="btn btn-danger" onclick="deletePriceVeriation('price-veriation-{{ $pIndex }}')">Delete</span>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $pIndex++;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                            <input type="hidden" id="price-variation-index"  value="{{ $pIndex }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-bordered panel-default">
                                        <div class="panel-heading" style="padding-left: 5px;"><h3>Curriculum <span class="btn btn-success" onclick="js_addCurriculum()">Add</span></h3> </div>
                                        <div class="panel-body">
                                                @php
                                                    $index = 0;
                                                    $usedLessons = array();
                                                    $usedQuizes = array();
                                                @endphp
                                            <div class="panel-group curriculum-sections" id="sortable">
                                                @if ($edit)
                                                    @foreach ($dataTypeContent->curriculums as $key => $curriculumn)

                                                        <div class="panel panel-default  ui-state-default" order="{{ $curriculumn->section_order }}" id="curriculumn-{{ $index }}">
                                                            <div class="panel-heading">
                                                                <h4 data-toggle="collapse" class="panel-title" data-target="#collapse{{ $index }}">
                                                                   <div class="form-group">
                                                                        <label>Title</label> <span class="btn btn-danger" onclick="js_deleteCurriculum({{ $index }})">Delete</span>
                                                                        <input type="text" name="curri[{{ $index }}][title]" class="form-control" value="{{ $curriculumn->title }}">
                                                                        <input type="hidden" name="curri[{{ $index }}][id]" value="{{ $curriculumn->id }}">
                                                                        <input type="hidden" name="curri[{{ $index }}][section_order]" class="curriculum-order" value="{{ $curriculumn->section_order }}">
                                                                    </div>
                                                                </h4>
                                                            </div>
                                                            <div id="collapse{{ $index }}" class="panel-collapse collapse">
                                                                <div class="panel-body">
                                                                    <div class="form-group">
                                                                        <label>Description</label>
                                                                        <textarea class="form-control" name="curri[{{ $index }}][description]">{{ $curriculumn->description }}</textarea>
                                                                    </div>
                                                                    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#lessons" onclick="lessonCalledBy('curriculum-index-{{ $index }}')">Add Lessons</button>

                                                                     <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#quizes" onclick="quizCalledBy('curriculum-index-{{ $index }}')">Add Quiz</button>
                                                                </div>
                                                                <?php $itemIndex = 0; ?>

                                                                 <ul class="list-group sortable_item sort-curriculum-items curriculum-index-{{ $index }}" currindex="{{ $index }}" >
                                                                    @forelse ($curriculumn->items as $item)
                                                                        <li class="list-group-item  {{ $item->item_type }}-tr-{{ $item->item_id }} " id="curriculum-{{ $index }}quiz-{{ $itemIndex }}" listtype="{{ $item->item_type }}" {{ $item->item_type }}="{{ $item->item_type }}-tr-{{ $item->item_id }}" >
                                                                            <input type="hidden" name="curri[{{ $index }}][item][{{ $itemIndex }}][display_order]" class="item-order" value="{{ $item->display_order }}">

                                                                            <input type="hidden" name="curri[{{ $index }}][item][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                                                                            <input type="hidden" name="curri[{{ $index }}][item][{{ $itemIndex }}][item_id]" value="{{ $item->item_id }}">

                                                                            <input type="hidden" name="curri[{{ $index }}][item][{{ $itemIndex }}][item_type]" value="{{ $item->item_type }}">

                                                                            <div class="content-title"><i class="voyager-{{  $item->item_type == 'quiz' ? 'alarm-clock' : 'book' }}" style="color: #22a7f0;"></i> <span id="list-item-{{ $item->item_type }}-tr-{{ $item->item_id }}">
                                                                                <?php $tempTitle = ''; $deleteAction = '' ;?>
                                                                                @if ($item->item_type == 'lesson')
                                                                                    <?php
                                                                                        $usedLessons[] = $item->lessons->id;
                                                                                        $deleteAction = 'revertLesson';
                                                                                    ?>
                                                                                    {{ $tempTitle = $item->lessons->title }}
                                                                                @else
                                                                                    <?php
                                                                                        $usedQuizes[] = $item->quizes->id;
                                                                                        $deleteAction = 'revertQuiz';
                                                                                    ?>
                                                                                {{ $tempTitle = $item->quizes->title }}
                                                                                @endif
                                                                            </span>
                                                                            </div>
                                                                            <div class="content-action"><span class="rewert-{{ $item->item_type }}" onclick="{{ $deleteAction }}('{{ $item->item_type }}-tr-{{ $item->item_id }}' , '{{ $tempTitle }}')" > {!! trashIcon() !!}</span></div>
                                                                        <?php $itemIndex++; ?>
                                                                        </li>


                                                                    @empty
                                                                        {{-- empty expr --}}
                                                                    @endforelse
                                                                </ul>
                                                                    <input type="hidden" value="{{ $itemIndex }}" id="curriculum-item-indexer-{{ $index }}" >


                                                            </div>
                                                        </div>
                                                        <?php $index++; ?>
                                                    @endforeach
                                                @endif


                                                {{--  --}}
                                            </div>
                                            <input type="hidden" id="curriculum-index" value="{{ $index }}">

                                        </div>
                                    </div>
                                </div>
                            </div>




                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" class="btn btn-primary save" onclick="rearrageAll()">{{ __('voyager::generic.save') }}</button>
                            @stop
                            @yield('submit-buttons')
                        </div>

                        <input type="hidden" name="created_by" value="{{ Auth::user()->id }}">
                    </form>

                    <iframe id="form_target" name="form_target" style="display:none"></iframe>
                    <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                            enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                        <input name="image" id="upload_file" type="file"
                                 onchange="$('#my_form').submit();this.value='';">
                        <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                        {{ csrf_field() }}
                    </form>

                </div>
            </div>
        </div>
    </div>


<!-- Lessons Modal -->
<div id="lessons" calledby="" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Lessons</h4>
      </div>
      <div class="modal-body">
        @if ($lessons->isNotEmpty())
        <p>Select Lessons from the below list</p>
        <table class="table table-bordered dtable">
            <thead>
                <tr>
                    <th>Lessons</th>
                    <th>Action</th>
                </tr>
            </thead>
            @foreach ($lessons as $lesson)
            @if (!empty($usedLessons) && in_array($lesson->id, $usedLessons))
                @continue('condition')
            @endif
            <tr id="lesson-tr-{{ $lesson->id }}">
                <th><label for="lesson-{{ $lesson->id }}">{{ $lesson->title }}</label></th>
                <td class="text-center"><input type="checkbox" class="lesson-checkboxes" lessontitle="{{ $lesson->title }}" id="lesson-{{ $lesson->id }}" lessonid="{{ $lesson->id }}" value="lesson-tr-{{ $lesson->id }}"></td>
            </tr>
            @endforeach
        </table>
        @else
            <p>No lesson in the system to add.</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="addLesson()">Add</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>


<!-- Quizes Modal -->
<div id="quizes" calledby="" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Quiz List</h4>
      </div>
      <div class="modal-body">
        @if ($quizes->isNotEmpty())
        <p>Select Quiz from the below list</p>
        <table class="table table-bordered dtable">
             <thead>
                <tr>
                    <th>Quizes</th>
                    <th>Action</th>
                </tr>
            </thead>
            @foreach ($quizes as $quiz)

            @if (!empty($usedQuizes) && in_array($quiz->id, $usedQuizes))
                @continue('condition')
            @endif
            <tr  id="quiz-tr-{{ $quiz->id }}">
                <th><label for="quiz-{{ $quiz->id }}">{{ $quiz->title }}</label></th>
                <td class="text-center"><input type="checkbox" id="quiz-{{ $quiz->id }}" class="quiz-checkboxes" quiztitle="{{ $quiz->title }}" quizid="{{ $quiz->id }}" value="quiz-tr-{{ $quiz->id }}"></td>
            </tr>
            @endforeach
        </table>
        @else
            <p>No quiz in the system to add.</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="addQuiz()">Add</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>


<div class="modal fade modal-danger" id="confirm_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
            </div>

            <div class="modal-body">
                <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .content-title{
        width: 90%;
        display: inline-block;
    }
    .content-action{
        width: auto;
        display: inline-block;
    }
    .rewert-lession{
        cursor: pointer;
    }
    .ui-sortable .list-group-item{

        cursor: grab;
    }
    .ui-sortable .list-group-item:active{
        cursor: grabbing;
    }
</style>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script>
    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
          return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            $('.confirm_delete_name').text(params.filename);
            $('#confirm_delete_modal').modal('show');
          };
        }

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                } else if (elt.type != 'date') {
                    elt.type = 'text';
                    $(elt).datetimepicker({
                        format: 'L',
                        extraFormats: [ 'YYYY-MM-DD' ]
                    }).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });

        $(document).ready(function () {
            $('.dd').nestable({
                maxDepth: 1
            });

            $( "#sortable" ).sortable();
            $( "#sortable" ).disableSelection();

            $('.sortable_item').sortable();
            $('.sortable_item').disableSelection();

            $('.dtable').DataTable();

            $( "#sortable" ).sortable({
              stop: function( event, ui ) {

                updateCurriculumOrders();
              }
            });

            $( ".sortable_item" ).sortable({
              stop: function( event, ui ) {
                console.log('test');
                updateSectionItemsOrders();
              }
            });
        });

        function addLesson(){
            tolist = $('#lessons').attr('calledby');
            currIndex = $('.' + tolist).attr('currindex') ;

            itemIndex = $('#curriculum-item-indexer-'+currIndex).val() ;

            $('.lesson-checkboxes:checked').each(function() {

                var selected_lesson = this.value;
                var lessonid = this.getAttribute('lessonid');

                var lessonTitle = this.getAttribute('lessontitle');
                $('.'+tolist).append(lessonListItem(lessonid, lessonTitle,selected_lesson, currIndex, itemIndex));
                itemIndex ++;

               // get the data table
               tbtr = $('#lessons table.dtable').DataTable();
               tbtr.row('#'+selected_lesson).remove().draw();
               // $('#'+selected_lesson).delete();
               // $('#'+selected_lesson).remove();
            });

             $('#curriculum-item-indexer-'+currIndex).val(itemIndex++ ) ;


        }

        function lessonListItem(lessonid, text, lessonTrId, currIndex, itemIndex){
            var js_html = '';
            js_html +=' <li class="list-group-item '+lessonTrId+'" id="curriculum-'+currIndex+'-lesson-'+itemIndex+'" listtype="lesson" lesson="'+lessonTrId+'" >';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][display_order]" class="item-order" value="'+itemIndex+'">';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][item_id]" value="'+lessonid+'">';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][item_type]" value="lesson">';

            js_html += '<div class="content-title"><i class="voyager-book" style="color: #22a7f0;"></i> <span id="list-item-'+lessonTrId+'">'+text+'</span></div>';
            js_html += '<div class="content-action"><span class="rewert-lession" onclick="revertLesson(\''+lessonTrId+'\' , \''+text+'\')">'+trashIcon()+'</span></div></li>';

            return js_html;
        }

        function addQuiz(){
            tolist = $('#quizes').attr('calledby');
            currIndex = $('.' + tolist).attr('currindex') ;

            itemIndex = $('#curriculum-item-indexer-'+currIndex).val() ;

            $('.quiz-checkboxes:checked').each(function() {
                var quiz = this.value;
                var quizTitle = this.getAttribute('quiztitle');
                var quizid = this.getAttribute('quizid');

                $('.'+tolist).append(quizListItem(quizid, quizTitle,quiz , currIndex, itemIndex));
                itemIndex ++;
                tbtr = $('#quizes table.dtable').DataTable();
                tbtr.row('#'+quiz).remove().draw();
                // $('#'+selected_lesson).delete();
            });

            $('#curriculum-item-indexer-'+currIndex).val(itemIndex++) ;
        }

        function quizListItem(quizid, text, quizTrId, currIndex, itemIndex ){
            var js_html = '';


             js_html +=' <li class="list-group-item '+quizTrId+'" id="curriculum-'+currIndex+'-quiz-'+itemIndex+'" listtype="quiz" quiz="'+quizTrId+'" >';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][display_order]" class="item-order" value="'+itemIndex+'">';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][item_id]" value="'+quizid+'">';
            js_html += '<input type="hidden" name="curri['+currIndex+'][item]['+itemIndex+'][item_type]" value="quiz">';

            js_html += '<div class="content-title"><i class="voyager-alarm-clock" style="color: #22a7f0;"></i> <span id="list-item-'+quizTrId+'">'+text+'</span></div>';
            js_html += '<div class="content-action"><span class="rewert-quiz" onclick="revertQuiz(\''+quizTrId+'\' , \''+text+'\')">'+trashIcon()+'</span></div></li>';

            return js_html;
        }

        function trashIcon(){
            return '<?= trashIcon(); ?>';
        }

        function revertLesson(lessonTrId, text){

            lessionId = getLessionId(lessonTrId);

            tbtr = $('#lessons table.dtable').DataTable();

            newRow = tbtr.row.add([
                '<label for="lesson-'+lessionId+'">'+text+'</label>',
                '<input type="checkbox" class="lesson-checkboxes" lessontitle="'+text+'" id="lesson-'+lessionId+'" value="'+lessonTrId+'">']).draw().node();

            // $(newRow).addClass("lsdfs");
            $(newRow).attr('id', lessonTrId);
            $( newRow ).find('td').eq(1).addClass('text-center');

            if (newRow) {
                $('li.list-group-item.'+lessonTrId).remove();
            }
        }

        function revertQuiz(quizTrId, text){

            quizId = getQuizId(quizTrId);

            tbtr = $('#quizes table.dtable').DataTable();

            newRow = tbtr.row.add([
                '<label for="quiz-'+quizId+'">'+text+'</label>',
                '<input type="checkbox" class="quiz-checkboxes" quiztitle="'+text+'" id="quiz-'+quizId+'" value="'+quizTrId+'">']).draw().node();

            // $(newRow).addClass("lsdfs");
            $(newRow).attr('id', quizTrId);
            $( newRow ).find('td').eq(1).addClass('text-center');

            if (newRow) {
                $('li.list-group-item.'+quizTrId).remove();
            }

        }

        function getQuizId(str){
            bytr = 'quiz-tr-';
            if(str.includes(bytr)){
                return str.substring(bytr.length);
            }
        }

        function getLessionId(str){
            bytr = 'lesson-tr-';
            if(str.includes(bytr)){
                return str.substring(bytr.length);
            }
        }
        function lessonCalledBy(curriculumIndex){
            $('#lessons').attr('calledby', curriculumIndex);
        }

        function quizCalledBy(curriculumIndex){
            $('#quizes').attr('calledby', curriculumIndex);
        }

        function js_deleteCurriculum(index){

            $('ul.list-group.curriculum-index-'+index+' li').each(function() {
                text = $("span", this).text();
                trId = $(this).attr($(this).attr('listtype'));

                if ($(this).attr('listtype') == 'lesson') {
                    revertLesson(trId, text);
                }else{
                    revertQuiz(trId, text);
                }
            });
            $('#curriculumn-'+ index).remove();
        }

        function deletePriceVeriation(row){
            $('#'+row).remove();
        }

        function addpriceVeriations(){

            var index = $('#price-variation-index').val();
            var js_html = '';

            js_html += '<tr id="price-veriation-'+index+'">';
            js_html += '<td>';
            js_html +=   '<select class="form-control" name="pricing['+index+'][price_type]">';
            js_html +=      '<option>Days</option>';
            js_html +=      '<option>Weeks</option>';
            js_html +=      '<option>Months</option>';
            js_html +=   '</select>';
            js_html +=   '</td>';
            js_html += '<td>';
            js_html += '<input type="number" step="1" min="1" name="pricing['+index+'][duration]" required class="form-control">';
            js_html +=   '</td>';
            js_html +=  '<td>';
            js_html +=  '<input type="number" step="0.01" min="1" name="pricing['+index+'][price]" required class="form-control">';
            js_html +=  '</td>';
            js_html +=  '<td>';
            js_html +=  '<input type="number" step="0.01" min="1" name="pricing['+index+'][sell_price]" required class="form-control">';
            js_html +=  '</td>';

            js_html += '<td>';
            js_html +=  '<span class="btn btn-danger" onclick="deletePriceVeriation(\'price-veriation-'+index+'\')">Delete</span>';
            js_html += '</td>';
            js_html += '</tr>';

            $('#priceVeriations').append(js_html);

            $('#price-variation-index').val(++index);

        }
        function js_addCurriculum(){
            var curriculumIndex = $('#curriculum-index').val();
            js_html = '';
            js_html += '';

            js_html += '<div class="panel panel-default ui-state-default" order="'+curriculumIndex+'" id="curriculumn-'+curriculumIndex+'" >';
            js_html +=  '<div class="panel-heading">';
            js_html +=      '<h4 data-toggle="collapse" class="panel-title" data-target="#collapse'+curriculumIndex+'">';
            js_html +=      '<div class="form-group">';
            js_html +=          '<label>Title</label> <span class="btn btn-danger" onclick="js_deleteCurriculum('+curriculumIndex+')">Delete</span>';
            js_html +=          '<input type="text" name="curri['+curriculumIndex+'][title]" class="form-control">';
            js_html +=          '<input type="hidden" name="curri['+curriculumIndex+'][section_order]" class="curriculum-order" value="'+curriculumIndex+'">';
            js_html +=      '</div>';
            js_html +=      '</h4>';
            js_html +=  '</div>';
            js_html += '<div id="collapse'+curriculumIndex+'" class="panel-collapse collapse in">';
            js_html += '<div class="panel-body">';
                js_html += '<div class="form-group">';
                    js_html += '<label>Description</label>';
                    js_html += '<textarea class="form-control" name="curri['+curriculumIndex+'][description]"></textarea>';
                js_html += '</div>';

            js_html += '<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#lessons" onclick="lessonCalledBy(\'curriculum-index-'+curriculumIndex+'\')">Add Lessons</button>';
            js_html += '    <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#quizes" onclick="quizCalledBy(\'curriculum-index-'+curriculumIndex+'\')">Add Quiz</button>';

            js_html += '</div>';
            js_html += '<input type="hidden" value="0" id="curriculum-item-indexer-'+curriculumIndex+'" >';
            js_html += '<ul class="list-group sortable_item sort-curriculum-items curriculum-index-'+curriculumIndex+'" currindex="'+curriculumIndex+'" ></ul>';

            js_html += '</div></div>';


            $('.curriculum-sections').append(js_html);
            $('.sortable_item').sortable();
            $('.sortable_item').disableSelection();

            $( ".sortable_item" ).sortable({
              stop: function( event, ui ) {
                updateSectionItemsOrders();
              }
            });

            $('#curriculum-index').val(parseInt(curriculumIndex) +1 );

        }

        function getSortOrder(selector){
            sortedIDs = $( selector ).sortable( "toArray" );
            // console.log(sortedIDs);
            return sortedIDs;
            // return sortedIDs = $( selector ).sortable( "toArray" );
        }

        function updateCurriculumOrders(){
            selector = '.curriculum-sections';
            newOrder = getSortOrder(selector);
            newOrder.forEach(function(item, index){
                $('#'+item+ ' .curriculum-order').val(index);
            });
        }

        function updateSectionItemsOrders(){
            $('.sort-curriculum-items').each(function() {

                var curriculumIndex = this.getAttribute('currindex');
                var selector = '.curriculum-index-'+ curriculumIndex;

                newOrder = getSortOrder(selector);
                newOrder.forEach(function(item, index){
                    $('#'+item+ ' .item-order').val(index);
                });
            });
        }

        function rearrageAll() {

            updateCurriculumOrders();
            updateSectionItemsOrders();
        }

    </script>
@stop

@php
    function trashIcon(){
        return '<i class="voyager-trash"></i>';
    }
@endphp
