@extends('admin.layouts.admin')
@section('content')
@section('title', 'Upload Promotion Edit Preview')
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
                                

                            </div>

                            <hr>
                            <form id="PromotionEdit" class="forms-sample" method="post" action="{{ admin_url('PromotypeFileUpload') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="promo_type" value="{{ $promoType }}" />
                                <div class="row">
                                    <div class="col-md-10 mb-3 form-group">
                                        <label class="form-label required">Promotype File </label>
                                        <input class="form-control rounded-pill" type="file" name="promotype_file" id="promotype_file" accept=".xlsx" style="border-radius: 1.25rem;">
                                        @error('promotype_file')
                                        <span class="error ">{{ $errors->first('promotype_file') }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">LOB / Business Type </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="business_type" name="business_type[]" multiple="multiple">

                                            @foreach ($business_type_details as $business_type)
                                            <option value="{{ encryptId($business_type->id) }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Theme </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="theme" name="theme[]" multiple="multiple">

                                            @foreach ($business_type_details as $business_type)
                                            <option value="{{ encryptId($business_type->id) }}">
                                                {{ $business_type->business_type }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <!-- <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Region </label>
                                        <select class="form-control multiple-select" placeholder="please select" id="region" name="region[]" multiple="multiple">
                                            @foreach ($region_details as $region)
                                            <option value="{{ encryptId($region->id) }}">
                                                {{ $region->region }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> -->



                                <div class="text-end mt-3">
                                    <button type="submit" id="promotion_edit" class="px-4 btn btn-primary btn-skew"><span class="fs-7">Preview</span></button>
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