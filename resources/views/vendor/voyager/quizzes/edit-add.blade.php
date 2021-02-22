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

                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                </div>
                            </div>




                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-bordered panel-default">
                                        <div class="panel-heading" style="padding-left: 5px;"><h3>Questions <span class="btn btn-success" data-toggle="modal" data-target="#questions" >Add</span></h3> </div>
                                        <div class="panel-body">
                                            <p>Quiz questions</p>
                                        </div>
                                        @php
                                            $index = 0;
                                            $usedQuestions = array();
                                        @endphp
                                            <ul class="list-group sortable_item questions-list" >
                                                @if ($edit)
                                                    @if ($dataTypeContent->questions()->exists() && count($dataTypeContent->questions))
                                                    @foreach ($dataTypeContent->questions as $question)
                                                        <li class="list-group-item question-tr-{{ $question->id }}"  id="question-{{ $question->id }}" question="question-tr-{{ $question->id }}" >
                                                            <?php
                                                                 $usedQuestions[] = $question->id;
                                                            ?>
                                                            <input type="hidden" name="ques[{{ $index }}][display_order]" class="item-order" value="{{ $question->quiz_question->display_order }}" >

                                                            <input type="hidden" name="ques[{{ $index }}][question_id]" value="{{ $question->id }}">

                                                            {{-- <input type="hidden" name="ques[{{ $index }}][id]" value="{{ $question->id }}"> --}}


                                                            <div class="content-title"><i class="icon voyager-question" style="color: #22a7f0;"></i> <span id="list-item-question-tr-{{ $question->id }}">{{ $question->title}}</span></div>

                                                            <div class="content-action"><span class="rewert-quiz" onclick="revertQuestion('question-tr-{{ $question->id }}' , '{{ $question->title }}')"><i class="voyager-trash"></i></span></div>
                                                        </li>
                                                        <?php $index++; ?>
                                                    @endforeach
                                                    @endif
                                                @endif
                                            </ul>

                                            <input type="hidden" id="questionIndex" value="{{ $index }}">
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




    <!-- Questions Modal -->
    <div id="questions"  class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Questions List</h4>
          </div>
          <div class="modal-body">
            @if ($questions->isNotEmpty())
            <p>Select Questions from the below list</p>
            <table class="table table-bordered dtable">
                 <thead>
                    <tr>
                        <th>Questions</th>
                        <th>Action</th>
                    </tr>
                </thead>
                @foreach ($questions as $question)

                @if (!empty($usedQuestions) && in_array($question->id, $usedQuestions))
                    @continue('condition')
                @endif
                <tr  id="question-tr-{{ $question->id }}">
                    <th><label for="question-{{ $question->id }}">{{ $question->title }}</label></th>
                    <td class="text-center"><input type="checkbox" id="question-{{ $question->id }}" class="question-checkboxes" questiontitle="{{ $question->title }}" question="{{ $question->id }}" value="question-tr-{{ $question->id }}"></td>
                </tr>
                @endforeach
            </table>
            @else
                <p>No question in the system to add.</p>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="addQuestions()">Add</button>
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

            /*$( "#sortable" ).sortable();
            $( "#sortable" ).disableSelection();*/

            $('.sortable_item').sortable();
            $('.sortable_item').disableSelection();

            $('.dtable').DataTable();

            /*$( "#sortable" ).sortable({
              stop: function( event, ui ) {

                updateCurriculumOrders();
              }
            });*/

            $( ".sortable_item" ).sortable({
              stop: function( event, ui ) {
                console.log('test');
                updateCurriculumOrders();
              }
            });
        });

        function addQuestions(){

            itemIndex = $('#questionIndex').val() ;

            $('.question-checkboxes:checked').each(function() {
                var questionTrId = this.value;
                var questionTitle = this.getAttribute('questiontitle');
                var questionid = this.getAttribute('question');

                $('.questions-list').append(questionItem(questionid,questionTitle,questionTrId,itemIndex));
                itemIndex ++;
                tbtr = $('#questions table.dtable').DataTable();
                tbtr.row('#'+questionTrId).remove().draw();
            });

            $('#questionIndex').val(itemIndex++) ;
        }

        function questionItem(questionid, title, questionTrId, itemIndex ){
            var js_html = '';


             js_html +=' <li class="list-group-item '+questionTrId+'" id="question-'+itemIndex+'" question="'+questionTrId+'" >';
            js_html += '<input type="hidden" name="ques['+itemIndex+'][display_order]" class="item-order" value="'+itemIndex+'">';
            js_html += '<input type="hidden" name="ques['+itemIndex+'][question_id]" value="'+questionid+'">';

            js_html += '<div class="content-title"><i class="icon voyager-question" style="color: #22a7f0;"></i> <span id="list-item-'+questionTrId+'">'+title+'</span></div>';
            js_html += '<div class="content-action"><span class="rewert-quiz" onclick="revertQuestion(\''+questionTrId+'\' , \''+title+'\')">'+trashIcon()+'</span></div></li>';

            return js_html;
        }

        function trashIcon(){
            return '<?= trashIcon(); ?>';
        }


        function revertQuestion(questionTrId, title){
            questionId = getQuestionId(questionTrId);

            tbtr = $('#questions table.dtable').DataTable();

            newRow = tbtr.row.add([
                '<label for="questions-'+questionId+'">'+title+'</label>',
                '<input type="checkbox" class="question-checkboxes" questiontitle="'+title+'" id="questions-'+questionId+'" value="'+questionTrId+'">']).draw().node();

            // $(newRow).addClass("lsdfs");
            $(newRow).attr('id', questionTrId);
            $( newRow ).find('td').eq(1).addClass('text-center');

            if (newRow) {
                $('li.list-group-item.'+questionTrId).remove();
            }

        }

        function getQuestionId(str){
            bytr = 'question-tr-';
            if(str.includes(bytr)){
                return str.substring(bytr.length);
            }
        }


        function getSortOrder(selector){
            sortedIDs = $( selector ).sortable( "toArray" );
            // console.log(sortedIDs);
            return sortedIDs;
            // return sortedIDs = $( selector ).sortable( "toArray" );
        }

        function updateCurriculumOrders(){
            selector = '.questions-list';
            newOrder = getSortOrder(selector);
            console.log(newOrder);
            newOrder.forEach(function(item, index){
                console.log(item);
            $('#'+item+ ' .item-order').val(index);
            });
        }



        function rearrageAll() {
            updateCurriculumOrders();
            // updateSectionItemsOrders();
        }

    </script>
@stop

@php
    function trashIcon(){
        return '<i class="voyager-trash"></i>';
    }
@endphp
