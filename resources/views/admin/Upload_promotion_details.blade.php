@extends('admin.layouts.admin')
@section('content')
@section('title', 'Upload Promotion')
@push('style')
@endpush
<!-- ============================================================== -->
<!-- Start Page Content here -->
<!-- ============================================================== -->
<style type="text/css">
    .error {
        color: red;
        margin: 10px;
    }
</style>
<div class="content-page">
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col">
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard') }}" title="Home"><img
                                src="{{ asset('public/assets/images/home.svg') }}" alt="" id="align-image"
                                width="20" height="20" style="vertical-align: text-top;"> </a><span
                            style="margin:10px">@yield('title')</span> </h4>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <hr>
                            <form id="UploadPromotype" class="forms-sample" method="get"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">LOB / Business Type </label>
                                        <select class="form-control multiple-select" placeholder="please select"
                                            id="business_types" name="business_type[]" multiple="multiple">

                                            @foreach ($getManufacturerBusiness as $business_type)
                                                <option value="{{ $business_type->id }}">
                                                    {{ $business_type->business_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 form-group">
                                        <label class="form-label required">Theme </label>
                                        <select class="form-control multiple-select" placeholder="please select"
                                            id="themes" name="theme[]" multiple="multiple">

                                            @foreach ($getManufacturerBusiness as $business_type)
                                                <option value="{{ $business_type->id }}">
                                                    {{ $business_type->business_type }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="row ">
                                    <label class="form-label required"><b>Select Promotype </b></label>
                                    <div class="col-md-4 mb-3 form-group" style="padding: 0 24px">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="promo_type"
                                                value="{{ encryptId(1) }}" data-id="1" id="single_promotype"
                                                checked>
                                            <label class="form-check-label" for="single_promotype">Single Promotype
                                            </label>
                                        </div>

                                    </div>
                                    <div class="col-md-4 mb-3 form-group" style="padding: 0 24px">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="promo_type"
                                                value="{{ encryptId(2) }}" data-id="2" id="combo_promotype">
                                            <label class="form-check-label" for="combo_promotype">Combo
                                                Promotype</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 form-group" style="padding: 0 24px">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="promo_type"
                                                value="{{ encryptId(3) }}" data-id="3" id="cart_level">
                                            <label class="form-check-label" for="cart_level">Cart Level </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-4 mb-3 form-group" style="padding: 0 24px">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="promo_type"
                                                value="{{ encryptId(4) }}" data-id="4" id="cart_free">
                                            <label class="form-check-label" for="cart_free">Cart Free</label>
                                        </div>

                                    </div>
                                    <div class="col-md-4 mb-3 form-group" style="padding: 0 24px">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="promo_type"
                                                value="{{ encryptId(5) }}" data-id="5" id="group_promo">
                                            <label class="form-check-label" for="group_promo">Group Promo</label>
                                        </div>
                                    </div>

                                </div>
                                <div style="text-align: center;">
                                    <!-- <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#Download_promotion_modal">Download</button> -->
                                    <a id="download" class="btn btn-success">Download</a>
                                    <button type="submit" id="validateButton"
                                        class="btn btn-primary">Upload</button>
                                    <!-- <button type="submit" class="btn btn-primary">Upload</button> -->

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- container -->
    <div id="Upload_promotion_modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="Upload_promotion_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="Upload_promotion_modalLabel">Upload Promotion</h4>
                    <button type="button" class="btn-close cls-modal" id="close_modal" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="UploadPromotypes" class="forms-sample" method="post"
                    action="{{ admin_url('PromotypeFileUpload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="business_type[]" id="business_type" />
                        <input type="hidden" name="theme[]" id="theme" />
                        <input type="hidden" name="promoType" id="promoType" />
                        <div class="row">

                            <div class="col-md-6 mb-3 form-group" id="minimum_box" style="display: none;">
                                <label class="form-label required">Minimum Purchase <span
                                        style="color: red">*</span></label>
                                <select class="form-select " name="minimum_purchase" id="minimum_purchase"
                                    aria-label="Default select example">
                                    <option value=''>Select</option>
                                    <option value='500'>500</option>
                                    <option value='1000'>1000</option>
                                    <option value='1500'>1500</option>
                                    <option value='2000'>2000</option>
                                    {{-- @foreach ($userrole_details as $userrole)
                                <option value="{{ $userrole->id }}">
                                    {{ $userrole->user_role }}
                                </option>
                                @endforeach --}}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 form-group" id="label_box" style="display: none;">
                                <label class="form-label required">Minimum Value <span
                                        style="color: red">*</span></label>
                                <input type="text" class="form-control" id="minimum_value" name="minimum_value">
                                {{-- @error('minimum_value')
                                    <span class="error ">{{ $errors->first('minimum_value') }}</span>
                                @enderror --}}
                            </div>
                            <div class="mb-3" style="display: none;" id="check_brand">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input brands" name="brand"
                                        id="brand">
                                    <label class="form-check-label" for="checkbox-signin">Select Brand</label>
                                </div>
                            </div>

                            <div class="mb-3" style="display: none;" id="check_category">
                                <div class="form-check" >
                                    <input type="checkbox" class="form-check-input brands" name="brand"
                                        id="category">
                                    <label class="form-check-label" for="checkbox-signin">Select Category</label>
                                </div>
                            </div>


                            <div class="col-md-10 mb-3 form-group">
                                <label class="form-label required">Promotype Files </label>
                                <input class="form-control rounded-pill" required onchange="validateFile(this)"
                                    type="file" name="promotype_file" id="promotype_file" accept=".xlsx"
                                    style="border-radius: 1.25rem;">
                                @error('promotype_file')
                                    <span class="error ">{{ $errors->first('promotype_file') }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="close_modal1" class="btn btn-light cls-modal"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="upload_modal" class="btn btn-primary">Submit</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div> <!-- content -->

@include('admin.Upload_promotion_download')
<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->

@push('script')
    <script type="text/javascript">
        $('input[name="promo_type"]').on('change', function() {
            if ($(this).data('id') == 3) {
                $('#label_box').css('display', 'block');
                $('#minimum_value').prop("required", "true");
            } else if ($(this).data('id') == 5) {
                console.log($(this).data('id'));
                $('#label_box').css('display', 'none');
                $('#minimum_box').css('display', 'block');
                $('#check_brand').css('display', 'block');
                $('#check_category').css('display', 'block');
                $("#minimum_value").removeAttr('required');
            } else {
                $('#label_box').css('display', 'none');
                $("#minimum_value").removeAttr('required');
            }
        });
        $('.brands').click(function() {
                    var $inputs = $('.brands');
                    if ($(this).is(':checked')) { // <-- check if clicked box is currently checked
                        $inputs.not(this).prop('disabled', true); // <-- disable all but checked checkbox
                    } else { //<-- if checkbox was unchecked
                        $inputs.prop('disabled', false); // <-- enable all checkboxes
                    }
                })
        document.addEventListener("DOMContentLoaded", function() {
            const minimumValueInput = document.getElementById("minimum_value");
            minimumValueInput.addEventListener("input", function() {
                this.value = this.value.replace(/[^0-9]/g, "");
            });
        });

        function validateFile(input) {
            const allowedExtensions = ["xlsx"];
            const fileInput = input;
            const fileError = document.getElementById("file-error");

            if (fileInput.files.length > 0) {
                const fileName = fileInput.value;
                const fileExtension = fileName.split(".").pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    fileInput.value = ""; // Clear the file input
                    fileError.textContent = "Only .xlsx files are allowed.";
                }
            }
        }

        document.getElementById('close_modal').addEventListener('click', function() {
            document.getElementById('promotype_file').value = ''; // Clear the file input value
        });
        document.getElementById('close_modal1').addEventListener('click', function() {
            document.getElementById('promotype_file').value = ''; // Clear the file input value
        });
        $(".cls-modal").on("click", function() {

            // $("#UploadPromotype")[0].reset();
            $(".error").html('');
            $(".error").removeClass("error");
            $('.rounded-pill').removeClass("is-invalid");
        })
        $(function() {
            var table = $('#promotion_list_table').DataTable({
                "autoWidth": false,
                dom: 'Bfrtip',
                aLengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                buttons: [{
                        extend: 'collection',
                        text: 'Export',
                        buttons: [{
                            extend: 'excel',
                            filename: 'Region List',
                            title: '',
                            exportOptions: {
                                columns: 'th:not(:last-child)'
                            },
                        }, ]
                    },
                    'pageLength'
                ],
                responsive: true,
                columnDefs: [{
                        responsivePriority: 1,
                        targets: 0
                    },
                    {
                        responsivePriority: 1,
                        targets: 1
                    }
                ],
                processing: true,
                serverSide: true,
                searching: true,
                "order": [
                    [0, "desc"]
                ],
                ajax: {
                    url: "{{ admin_url('upload_promotion') }}",
                    type: 'GET',
                    data: function(d) {
                        d.region = $('input[name=region]').val();
                        d.state = $('input[name=state]').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'file_name',
                        name: 'file_name'
                    },
                    {
                        data: 'created_date',
                        name: 'created_date'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: "150px",
                    },
                ]
            });
        });
        $(function() {
            var validator = $('#UploadPromotype').validate({
                rules: {
                    "business_type[]": {
                        required: true,

                    },
                    "theme[]": {
                        required: true,

                    },
                },
                messages: {
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
                // submitHandler: function(form) {
                // $("#upload_modal").prop('disabled', true); //disable
                // form.submit();
                // return false
                // }
            });

            $("#UploadPromotype").submit(function(event) {
                event.preventDefault();
                if (validator.valid()) {
                    var business_type = $("#business_types").val();
                    var theme = $("#themes").val();
                    var promoType = $('input[name="promo_type"]:checked').val();

                    $("#business_type").val(business_type)
                    $("#theme").val(theme)
                    $("#promoType").val(promoType)

                    $("#Upload_promotion_modal").modal('show');
                }
            });


            // $("#UploadPromotypes").submit(function(event) {

            // })

        });


        $('#download').click(function() {
            var selectedPromoType = $('input[name="promo_type"]:checked').val();

            $.ajax({
                url: "{{ admin_url('PromotypeDownload') }}",
                type: 'POST',
                data: {
                    promotype: selectedPromoType,
                    // Add more data if needed
                },
                success: function(response) {
                    // Simulate a click on the hidden download link
                    var link = document.createElement('a');
                    link.href = response.fileContent;
                    link.target = '_blank'; // Open in a new tab
                    link.download = response.filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(error);
                }
            });
        });
    </script>
@endpush
@stop
