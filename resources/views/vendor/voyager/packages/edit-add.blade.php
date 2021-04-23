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

                            @if (!$edit)
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Name *</label>
                                            <input type="text" name="name" required class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Classes Type</label>
                                            <select class="form-control" name="class_type_id">
                                                @foreach ($classTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Service </label>
                                            <select class="form-control" name="service_type_id">
                                                @foreach ($servies as $service)
                                                    @foreach ($service->types as $serType)
                                                        <option value="{{ $serType->id }}">
                                                            {{ $service->name .' - ' . $serType->name }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
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
                                            <div class="panel-heading" style="padding: 15px;position: relative;"><h3 style="display: inline">Batches and Price Veriations </h3><span style="display: inline;top: 6px;right: 10px;position: absolute;" class="btn btn-success" onclick="addBatch()">Add Batch</span> </div>
                                            <div class="panel-body ">
                                                @php
                                                    $batchIndex = 0;
                                                @endphp
                                                <div id="batches-listings">

                                                <div class="batch-{{ $batchIndex }} mb-4 mt-4" >
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <label>Batch</label>
                                                            <select class="form-control" name="batch_selected[{{ $batchIndex }}][batch_id]">
                                                                @foreach ($batches as $batch)
                                                                <option value="{{ $batch->id }}" >{{ $batch->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>&nbsp;</label>
                                                            <span onclick="appendPriceVar({{ $batchIndex }})" class="btn btn-success">Add batch Variations</span>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $pIndex = 0;
                                                    @endphp

                                                    <div id="price-var-{{ $batchIndex }}" >

                                                    <div id="price-index-{{ $batchIndex }}-{{ $pIndex }}" class="row " style=" margin-bottom:15px; border-bottom: 1px  solid #f0f0f0;">

                                                        <div class="col-md-2">
                                                            <label>Week Days </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][week_days]" step="1" min="1" max="7" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Month Duration </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][duration_month]" step="1" min="1" max="999" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Classes </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_classes]" step="1" class="form-control">
                                                        </div>


                                                        <div class="col-md-2">
                                                            <label>Cost per class</label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][cost_per_class]" step="1" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Hours</label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_hours]" step="1" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Cost </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_cost]" step="1" class="form-control">
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Class duration note</label>
                                                            <textarea required class="form-control" name="batch[{{ $batchIndex }}][{{ $pIndex }}][class_duration_note]" ></textarea>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Additional note</label>
                                                            <textarea required class="form-control" name="batch[{{ $batchIndex }}][{{ $pIndex }}][additional_note]" ></textarea>
                                                        </div>
                                                    </div>
                                                        <input type="hidden" id="price-variation-{{ $batchIndex }}-index"  value="{{ ++$pIndex }}">
                                                    </div>
                                                </div>
                                                </div>
                                                <input type="hidden" id="batch-index"  value="{{ $batchIndex }}">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Name *</label>
                                            <input type="text" name="name" value="{{ $dataTypeContent->name }}" required class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Classes Type</label>
                                            <select class="form-control" name="class_type_id">
                                                @foreach ($classTypes as $type)
                                                    <option {{ $dataTypeContent->class_type_id == $type->id ? 'selected' : ''  }} value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="">
                                            <label>Service </label>
                                            <select class="form-control" name="service_type_id">
                                                @foreach ($servies as $service)
                                                    @foreach ($service->types as $serType)
                                                        <option {{ $dataTypeContent->service_type_id == $serType->id ? 'selected' : ''  }} value="{{ $serType->id }}">
                                                            {{ $service->name .' - ' . $serType->name }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
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
                                            <div class="panel-heading" style="padding: 15px;position: relative;"><h3 style="display: inline">Batches and Price Veriations </h3><span style="display: inline;top: 6px;right: 10px;position: absolute;" class="btn btn-success" onclick="addBatch()">Add Batch</span> </div>
                                            <div class="panel-body ">
                                                @php
                                                    $batchIndex = 0;
                                                @endphp
                                                <div id="batches-listings">
                                                @foreach ($batchItems as $batch_id => $items)

                                                <div id="batch-{{ $batchIndex }}" class="batch-{{ $batchIndex }} mb-4 mt-4" >
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <label>Batch</label>
                                                            <select class="form-control" name="batch_selected[{{ $batchIndex }}][batch_id]">
                                                                @foreach ($batches as $batch)
                                                                <option {{ $batch_id == $batch->id ? 'selected' : ''}} value="{{ $batch->id }}" >{{ $batch->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <span onclick="appendPriceVar({{ $batchIndex }})" class="btn btn-success">Add batch Variations</span>

                                                        </div>
                                                        @if ($batchIndex > 0)
                                                            <div class="col-md-2">
                                                                <span onclick="deletePriceVeriation('batch-{{ $batchIndex }}')" class="btn btn-danger">Remove Batch</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @php
                                                        $pIndex = 0;
                                                    @endphp
                                                    @foreach ($items as $bItem)

                                                    <div id="price-var-{{ $batchIndex }}" >

                                                    <div id="price-index-{{ $batchIndex }}-{{ $pIndex }}" class="row " style=" margin-bottom:15px; border-bottom: 1px  solid #f0f0f0;">
                                                    <input type="hidden" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][package_item]" value="{{ $bItem['id'] }}" >

                                                        <div class="col-md-2">
                                                            <label>Week Days </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][week_days]" value="{{ $bItem['week_days'] }}" step="1" min="1" max="7" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Month Duration </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][duration_month]" step="1" min="1" max="999" value="{{ $bItem['duration_month'] }}" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Classes </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_classes]" step="1" value="{{ $bItem['total_classes'] }}" class="form-control">
                                                        </div>


                                                        <div class="col-md-2">
                                                            <label>Cost per class</label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][cost_per_class]" step="1" value="{{ $bItem['cost_per_class'] }}" class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Hours</label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_hours]" step="1" value="{{ $bItem['total_hours'] }}"  class="form-control">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label>Total Cost </label>
                                                            <input type="number" required name="batch[{{ $batchIndex }}][{{ $pIndex }}][total_cost]" step="1" class="form-control" value="{{ $bItem['total_cost'] }}" >
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Class duration note</label>
                                                            <textarea required class="form-control" name="batch[{{ $batchIndex }}][{{ $pIndex }}][class_duration_note]"  >{{ $bItem['class_duration_note'] }}</textarea>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label>Additional note</label>
                                                            <textarea required class="form-control" name="batch[{{ $batchIndex }}][{{ $pIndex }}][additional_note]" >{{ $bItem['additional_note'] }}</textarea>
                                                        </div>
                                                        @if ($pIndex > 0)
                                                        <div class="col-md-2">
                                                            <span class="btn btn-danger" onclick="deletePriceVeriation('price-index-{{ $batchIndex }}-{{ $pIndex }}')">Del</span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    </div>
                                                    <?php $pIndex++; ?>
                                                    @endforeach
                                                        <input type="hidden" id="price-variation-{{ $batchIndex }}-index"  value="{{ $pIndex }}">
                                                </div>
                                                <?php $batchIndex++; ?>
                                                    @endforeach

                                                </div>
                                                <input type="hidden" id="batch-index"  value="{{ $batchIndex }}">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" class="btn btn-primary save" onclick="rearrageAll()">{{ __('voyager::generic.save') }}</button>
                            @stop
                            @yield('submit-buttons')
                        </div>

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

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }

        /* Firefox */
        input[type=number] {
          -moz-appearance: textfield;
        }
    </style>
    <!-- End Delete File Modal -->
@stop

@section('javascript')
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha512-uto9mlQzrs59VwILcLiRYeLKPPbS/bT71da/OEBYEwcdNUk8jYIy+D176RYoop1Da+f9mvkYrmj5MCLZWEtQuA==" crossorigin="anonymous"></script> --}}
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


            $('.dtable').DataTable();


        });

        function deletePriceVeriation(row){
            $('#'+row).remove();
        }

        function addBatch(){
            var batchIndex = $('#batch-index').val();
            batchIndex ++;

            js_html = '';


            js_html += '<div id="batch-'+batchIndex+'" class="batch-'+batchIndex+' mb-4 mt-4" >';
            js_html += '<div class="row">';
            js_html += ' <div class="col-md-2">';
            js_html += '    <label>Batch</label>';
            js_html += '    <select class="form-control" name="batch_selected['+batchIndex+'][batch_id]">';
            @foreach ($batches as $batch)
            js_html += '<option value="{{ $batch->id }}" >{{ $batch->name }}</option>';
            @endforeach
            js_html += '    </select>';
            js_html += ' </div>';
            js_html += ' <div class="col-md-2">';
            js_html += '    <span onclick="appendPriceVar('+batchIndex+')" class="btn btn-success">Add batch Variations</span>';
            js_html += ' </div>';
            if (batchIndex > 0) {
                js_html += ' <div class="col-md-2">';
                js_html += '    <span onclick="deletePriceVeriation(\'batch-'+batchIndex+'\')" class="btn btn-danger">Remove Batch</span>';
                js_html += ' </div>';
            }
            js_html += '</div>';
            js_html += '';
            js_html += '<div id="price-var-'+batchIndex+'" >';
            js_html += addBatchPriceVariation(batchIndex);
            js_html += '';
            js_html += '<input type="hidden" id="price-variation-'+batchIndex+'-index" value="1" /> ';
            js_html += '</div>';

            $('#batches-listings').append(js_html);

            $('#batch-index').val(++batchIndex);
        }

        function appendPriceVar(batch){
            var batchHtml = addBatchPriceVariation(batch);
            console.log(batchHtml);
            $('#price-var-'+ batch).append(batchHtml);
        }

        function addBatchPriceVariation(batch, priceFirstIndex = 0) {

            var index = $('#price-variation-'+batch+'-index').val();

            if (!index) {
                index = priceFirstIndex;
            }

            js_html = '';
            js_html += '<div class="row " id="price-index-'+batch+'-'+index+'" style=" margin-bottom:15px; border-bottom: 1px  solid #f0f0f0;">';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Week Days </label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][week_days]" step="1" min="1" max="7" class="form-control">';
            js_html +=' </div>';

            js_html +='';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Month Duration </label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][duration_month]" step="1" min="1" max="999" class="form-control">';
            js_html +=' </div>';

            js_html +='';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Total Classes</label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][total_classes]" step="1"  class="form-control">';
            js_html +=' </div>';

            js_html +='';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Cost per class</label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][cost_per_class]" step="1" class="form-control">';
            js_html +=' </div>';


            js_html +='';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Total Hours</label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][total_hours]" step="1" class="form-control">';
            js_html +=' </div>';


            js_html +='';
            js_html +='<div class="col-md-2">';
            js_html +=' <label>Total Cost</label>';
            js_html +='  <input type="number" required name="batch['+batch+'][' +index+ '][total_cost]" step="1" class="form-control">';
            js_html +=' </div>';


            js_html +='';
            js_html +='<div class="col-md-6">';
            js_html +=' <label>Class duration note</label>';
            js_html +=' <textarea class="form-control" name="batch['+batch+'][' +index+ '][class_duration_note]" ></textarea> ';
            js_html +=' </div>';


            js_html +='';
            js_html +='<div class="col-md-6">';
            js_html +=' <label>Additional note</label>';
            js_html +=' <textarea class="form-control" name="batch['+batch+'][' +index+ '][additional_note]" ></textarea> ';
            js_html +=' </div>';

            if (index > 0) {

            js_html +=  '<span class="btn btn-danger" onclick="deletePriceVeriation(\'price-index-'+batch+'-'+index+'\')">Del</span>';

            }
            $('#price-variation-'+batch+'-index').val(++index);
            return js_html;
        }
    </script>
@stop