@extends('admin.layouts.admin')
@section('content')
@section('title', 'Upload Promotion Edit')
@section('pageurl', admin_url('upload_promotion'))
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">

                                <div class="col">
                                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard')}}" title="Home"><img src="{{ asset('public/assets/images/home.svg')}}" alt="" id="align-image" width="20" height="20" style="vertical-align: text-top;"> </a><span style="margin:10px">@yield('title')</span> </h4>

                                </div>
                                <div class="col" style="text-align: end;">
                                    <a class="btn btn-outline-danger ms-auto" href="{{ admin_url('upload_promotion') }}"> Back</a>

                                </div>

                            </div>

                            <hr>
                            <form id="PromotionEdit" class="forms-sample" method="post" action="{{ admin_url('upload_promotion/edit/' . encryptId($promotion_details['id'])) }}" enctype="multipart/form-data">
                                @csrf


                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">LOB / Business Type </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="business_type" name="business_type[]" multiple="multiple">

                                            @foreach ($business_type_details as $business_type)
                                            <option @if (in_array($business_type['id'], $selected_business)) selected @endif value="{{ $business_type->id }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach



                                            <!-- <option value=''>Select Business Type</option> -->
                                            <!-- @foreach ($business_type_details as $business_type)
                                            <option @if (old('business_type')==encryptId($business_type->id)) selected @endif
                                                value="{{ encryptId($business_type->id) }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach -->
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Theme </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="theme" name="theme[]" multiple="multiple">

                                            @foreach ($business_type_details as $business_type)
                                            <option @if (in_array($business_type['id'], $selected_business)) selected @endif value="{{ $business_type->id }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach


                                            <!-- <option value=''>Select Theme</option>
                                            @foreach ($business_type_details as $business_type)
                                            <option @if (old('business_type')==encryptId($business_type->id)) selected @endif
                                                value="{{ encryptId($business_type->id) }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach -->
                                        </select>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Region </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="region" name="region[]" multiple="multiple">
                                            @foreach ($region_details as $region)
                                            <option @if (in_array($region['id'], $selected_business)) selected @endif value="{{ $region->id }}">
                                                {{ $region->region }}
                                            </option>
                                            @endforeach


                                            <!-- <option value=''>Select Region</option>
                                            @foreach ($region_details as $region)
                                            <option @if (old('id')==    $region->id)) selected @endif
                                                value="{{ encryptId($region->id) }}">
                                                {{ $region->region }}
                                            </option>
                                            @endforeach -->
                                        </select>
                                    </div>
                                </div>



                                <div class="text-end mt-3">
                                    <button type="submit" id="promotion_edit" class="px-4 btn btn-primary btn-skew"><span class="fs-7">Update</span></button>
                                    <a class="px-4 btn btn-secondary btn-skew fs-7" href="{{ admin_url('upload_promotion') }}">Cancel</a>
                                </div>
                            </form>
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


    $(function() {
        validator = $('#PromotionEdit').validate({
            rules: {
                "region[]": {
                    required: true,

                },
                "business_type[]": {
                    required: true,

                },
                "theme[]": {
                    required: true,

                },
            },
            messages: {
                "region[]": {
                    required: "Please Select Region",

                },
                "business_type[]": {
                    required: "Please Select Business Type",

                },
                "theme[]": {
                    required: "Please Select Theme",

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
                $("#promotion_edit").prop('disabled', true); //disable 
                form.submit();
            }
        });
    });
</script>
@endpush
@stop