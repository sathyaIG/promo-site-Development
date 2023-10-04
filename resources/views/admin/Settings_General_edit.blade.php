@extends('admin.layouts.admin')
@section('content')
@section('title', 'General Settings Edit')
@push('style')
@endpush
<!-- ============================================================== -->
<!-- Start Page Content here -->
<!-- ============================================================== -->

<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard') }}"
                            title="Home"><img src="{{ asset('public/assets/images/home.svg') }}"
                                alt="" id="align-image" width="20" height="20"
                                style="vertical-align: text-top;"> </a><span
                            style="margin:10px">@yield('title')</span> </h4>

                </div>
                <div class="row">

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                               
                            <hr>
                            <div class=" ">
                                <section class="content">
                                    <div class="container-fluid">

                                        <form name="UserEdit" id="UserEdit" autocomplete="off" method="post"
                                        action="{{ admin_url('General_Settings_EditSubmit') }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-11 mgrnoato">
                                                <div class="card-body">
            
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">Promotion Upload will be allowed only before the start date</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="number" min="1"
                                                                    name="upload_days" id="upload_days"
                                                                    value="{{ $General_Settings->upload_allowed_days }}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">Manufacturer will be able to edit the document only before</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="number"
                                                                    name="edit_document" id="edit_document"
                                                                    value="{{ $General_Settings->edit_document}}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">Start Date</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="number" min="1"
                                                                    name="start_date" id="start_date"
                                                                    value="{{$General_Settings->start_date}}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">End Date</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="number" min="1"
                                                                    name="end_date" id="end_date"
                                                                    value="{{ $General_Settings->end_date }}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">Bulk File gets Generated on</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="text"
                                                                    name="generate_days" id="generate_days"
                                                                    value="{{ $General_Settings->generated_on }}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">Manual Upload Dates</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="text" 
                                                                    name="manualupload_days" id="manualupload_days"
                                                                    value="{{ $General_Settings->upload_dates }}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <label class="required">File Submitted by Brand till</label>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input required="" type="text" 
                                                                    name="file_submit" id="file_submit"
                                                                    value="{{ $General_Settings->submitted_till }}"
                                                                    class="form-control" style="margin-bottom: 16px;">
                                                            </div>
                                                        </div>
                                                    </div>
            
            
                                                    <div class="row">
                                                        <div class="col-md-6">
            
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group" style="float:right;">
                                                                <button id="submitid" type="button" class="btn btn-success">Update
                                                                </button>
                                                                <button type="button" class="btn btn-danger cancel">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
            
                                                </div>
                                            </div>
                                        </div>
                                    </form>


                                  
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
            <!-- end row -->

        </div> <!-- container-fluid -->

    </div> <!-- content -->


</div>

<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->

@push('script')
            <script type="text/javascript">
                // $(document).on('change', '.date_valid', function() {
                //     $(this).valid()
                // });

                $(document).on('click', '.cancel', function() {
                    window.location.href = "{{ admin_url('General_Settings_View') }}";
                });

                $(function() {


                    $.validator.addMethod("greaterThan",
                        function(value, element, params) {

                            if (!/Invalid|NaN/.test(new Date(value))) {
                                return new Date(value) > new Date($(params).val());
                            }

                            return isNaN(value) && isNaN($(params).val()) ||
                                (Number(value) > Number($(params).val()));
                        }, 'Must be greater than Start Date.');


                    $.validator.addMethod("pwcheck", function(value) {
                        return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) // consists of only these
                            &&
                            /[a-z]/.test(value) // has a lowercase letter
                            &&
                            /[A-Z]/.test(value) // has a lowercase letter
                            &&
                            /\d/.test(value) // has a digit
                    });

                    $.validator.addMethod("passcheck", function(value) {
                        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{12,30}$/.test(
                            value) // consists of only these

                    });

                    $.validator.addMethod('filesize', function(value, element, param) {
                        return this.optional(element) || (element.files[0].size <= param)
                    });
                    $.validator.addMethod("namewithspace", function(value) {
                        return /[a-zA-Z ]+$/.test(value) // consists of only these

                    });

                    $(document).on('click', '#submitid', function() {
                        Swal.fire({
                            title: 'Are you sure want to update',
                            showDenyButton: false,
                            showCancelButton: true,
                            confirmButtonText: `Update`,
                            denyButtonText: `Don't Update`,
                            confirmButtonColor: '#3ac47d',
                            denyButtonColor: '#28a745',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#UserEdit').submit();
                            } else if (result.isDenied) {
                                Swal.fire('Not Updated.', '', 'info');
                            }
                        });
                    });

                    $('#UserEdit').validate({
                        rules: {

                            upload_days: {
                                required: true,
                            },
                            edit_document: {
                                required: true,
                            },
                            start_date: {
                                required: true,
                            },
                            end_date: {
                                required: true,
                            },
                            generate_days: {
                                required: true
                            },
                            manualupload_days: {
                                required: true
                            },
                            file_submit: {
                                required: true
                            }
                            
                        },

                        messages: {

                            upload_days: {
                                required: "Promotion Upload will be allowed only before the start date is required",
                            },
                            edit_document: {
                                required: "Manufacturer will be able to edit the document only before is required",
                            },
                            start_date: {
                                required: "Start Date is required",
                            },
                            end_date: {
                                required: "End Date is required",
                            },
                            generate_days: {
                                required: "Bulk File gets Generated on is required",
                            },
                            manualupload_days: {
                                required: "Manual Upload Dates is required"
                            },
                            file_submit: {
                                required: "File Submitted by Brand till is required"
                            }
                            
                        },
                        errorElement: 'span',
                        errorPlacement: function(error, element) {
                            error.addClass('invalid-feedback');
                            element.closest('.form-group').append(error);
                        },
                        highlight: function(element, errorClass, validClass) {
                            $(element).addClass('is-invalid');
                        },
                        unhighlight: function(element, errorClass, validClass) {
                            $(element).removeClass('is-invalid');
                        },
                        submitHandler: function(form) {
                            $("#submitid").prop('disabled', true); //disable 
                            form.submit();
                        }
                    });



                });
            </script>
        @endpush

@stop
