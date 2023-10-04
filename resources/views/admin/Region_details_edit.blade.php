@extends('admin.layouts.admin')
@section('content')
@section('title', 'Region Edit')
@section('pageurl', admin_url('region_management'))
<style type="text/css">
    .error {
        color: red;
        margin: 10px;
    }
</style>


<!-- start page content wrapper-->
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard')}}" title="Home"><img src="{{ asset('public/assets/images/home.svg')}}" alt="" id="align-image" width="20" height="20" style="vertical-align: text-top;"> </a><span style="margin:10px">@yield('title')</span> </h4>

                </div>
                <div class="row">

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                              
                                <div class="col" style="text-align: end;">
                                    <a class="btn btn-outline-danger ms-auto" href="{{ admin_url('region_management') }}"> Back</a>

                                </div>


                            <hr>
                            <form id="RegionEdit" class="forms-sample" method="post" action="{{ admin_url('region_management/edit/' . encryptId($region_details['id'])) }}" enctype="multipart/form-data">
                                @csrf


                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Region </label>
                                        <input type="text" class="form-control" id="region" name="region" value="{{ $region_details['region'] }}" placeholder="Region">
                                        @error('region')
                                        <span class="error ">{{ $errors->first('region') }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">State </label>
                                        <select class="form-select " name="state" id="state" aria-label="Default select example">
                                            <option value=''>Select State</option>
                                            @foreach ($state_details as $state)
                                            <option @if ($region_details['state']==$state['id']) selected @endif value="{{ encryptId($state->id) }}">
                                                {{ $state->state }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">City </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="city" name="city[]" multiple="multiple">
                                            <option value=''>Select City</option>
                                            @foreach ($city_details as $city)
                                            <option @if (in_array($city['id'], $selected_city)) selected @endif value="{{ $city->id }}">
                                                {{ $city->city }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Region Code</label>
                                        <input type="text" class="form-control" id="region_code" name="region_code" value="{{ $region_details['region_code'] }}">
                                        @error('region_code')
                                        <span class="error ">{{ $errors->first('region_code') }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label class="form-label required">Region ID</label>
                                    <input type="text" class="form-control" id="region_id" name="region_id" value="{{ $region_details['region_id'] }}">
                                    @error('region_id')
                                    <span class="error ">{{ $errors->first('region_id') }}</span>
                                    @enderror
                                </div>

                                <div class="text-end mt-3">
                                    <button type="submit" id="region_edit" class="px-4 btn btn-primary btn-skew"><span class="fs-7">Update</span></button>
                                    <a class="px-4 btn btn-secondary btn-skew fs-7" href="{{ admin_url('region_management') }}">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                </div>
            </div>
            <!-- end row -->

        </div> <!-- container-fluid -->

    </div> <!-- content -->


</div>
@push('script')
<script type="text/javascript">
    $('#reset').on('click', function() {
        window.location.reload();
    })

    $('#state').change(function() {
        state_id = $('#state option:selected').val();
        $.ajax({

            url: "{{ admin_url('state_get_city') }}",
            method: "POST",
            data: {
                _token: $("input[name='_token']").val(),
                state_id: state_id,
            },
            success: function(result) {
                $('#city').empty();
                $.each(result.data, function(index, value) {
                    $('#city')
                        .append($("<option></option>")
                            .val(value._id)
                            //  .attr("value", index._id)
                            .text(value.city));

                });
            }
        });
    });

    $(function() {
        validator = $('#RegionEdit').validate({
            rules: {
                region: {
                    required: true,
                    maxlength: 30,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,
                    alphanumericwithspace: true,
                },

                state: {
                    required: true,

                },
                "city[]": {
                    required: true,
                },
            },
            messages: {
                region: {
                    required: "Please enter Region",
                    maxlength: "You have reached your maximum limit of characters allowed",
                },
                state: {
                    required: "Please Select State",
                },
                "city[]": {
                    required: "Please Select City",
                },


            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');

            },
            submitHandler: function(form) {
                $("#region_edit").prop('disabled', true); //disable 
                form.submit();
            }
        });
    });
</script>
@endpush
@stop