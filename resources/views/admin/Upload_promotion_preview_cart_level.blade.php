@extends('admin.layouts.admin')
@section('content')
@section('title', 'Preview')
@push('style')
@endpush
<div class="content-page">
    <div class="content">

        <!-- Start Content-->
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard') }}" title="Home"><img src="{{ asset('public/assets/images/home.svg') }}" alt="" id="align-image" width="20" height="20" style="vertical-align: text-top;"> </a><span style="margin:10px">@yield('title')</span> </h4>

                </div>
                <div class="row">

                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" style="position: relative;">

                                <!-- <form class="float-right" action="{{ admin_url('Download_dispatch_cart_level/' . encryptId($promotion->id)) }}" id="download_certificate" method="post" style="margin-left: 850px;margin-top: 39px;">
                                    @csrf
                                    <input type="hidden" name="category_values" value="">
                                    <input type="hidden" name="indexId" value="">
                                    <button title="Download" type="submit" class="btn btn-primary" style="position: absolute;
                                    right: 10%;">Download</button>
                                    

                                </form> -->
                                <a id="download" class="btn btn-primary" style="position: absolute;
                                    right: 10%;">Download</a>
                                <a href="{{ admin_url('Upload_view/' . encryptId($promotion->id)) }}" class="btn btn-primary" id="errorlog" style="position: absolute;
                                    float: right;">Click Here to View the Error Logs</a>


                            </div>


                            <hr>
                            <form id="updateForm" method="post" action="{{ admin_url('create_combo/' . encryptId($promotion->id)) }}">
                                @csrf
                                <input type="hidden" name="promotion_id" value="{{ encryptId($promotion->id) }}">
                                <table id="region_list_table" class="table table-striped table-bordered" style="width: 97%;margin-left: 16px;">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>SKU ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $i = 1;
                                        @endphp
                                        @foreach ($data as $index => $listDetails)
                                        <tr>
                                            <td>{{ $i }}
                                            </td>
                                            <td>{{ $listDetails->skuId }}
                                            </td>

                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                                <button type="submit" id="validateButton" class="btn btn-primary" style="position: absolute; top: 25px;right: 2%;">Upload</button>
                            </form>

                        </div>
                    </div>

                </div>
            </div>
            <!-- end row -->

        </div> <!-- container-fluid -->



    </div> <!-- content -->
    <div id="Upload_promotion_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Upload_promotion_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="Upload_promotion_modalLabel">Upload Promotion</h4>
                    <button type="button" class="btn-close cls-modal" id="close_modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="UploadPromotypes" class="forms-sample" method="post" action="{{ admin_url('PromotypeFileEditCartLevel/' . encryptId($promotion->id)) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">                      
                        <div class="row">
                            <div class="col-md-6 mb-3 form-group" id="label_box">
                                <label class="form-label required">Minimum Value <span
                                        style="color: red">*</span></label>
                                <input type="text" class="form-control" id="minimum_value" name="minimum_value">
                                @error('minimum_value')
                                    <span class="error ">{{ $errors->first('minimum_value') }}</span>
                                @enderror
                            </div>
                            <div class="col-md-10 mb-3 form-group">
                                <label class="form-label required">Promotype File </label>
                                <input class="form-control rounded-pill" type="file" required onchange="validateFile(this)" name="promotype_file" id="promotype_file" accept=".xlsx" style="border-radius: 1.25rem;">
                                @error('promotype_file')
                                <span class="error ">{{ $errors->first('promotype_file') }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="close_modal1" class="btn btn-light cls-modal" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="upload_modal" class="btn btn-primary">Submit</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('script')
<script>
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
            } else {
                fileError.textContent = ""; // Clear any previous error message
            }
        }
    }
    document.getElementById('close_modal').addEventListener('click', function() {
        document.getElementById('promotype_file').value = ''; // Clear the file input value
    });
    document.getElementById('close_modal1').addEventListener('click', function() {
        document.getElementById('promotype_file').value = ''; // Clear the file input value
    });
    $(document).ready(function() {
        function getCategoryValues() {
            var categoryValues = [];
            $('input[name="category_values[]"]').each(function() {
                categoryValues.push($(this).val());
            });
            return categoryValues;
        }

        $('#download_certificate').on('click', function(event) {
            event.preventDefault();
            var categoryValues = getCategoryValues();
                        var categoryValuesInput = $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'category_values')
                .val(JSON.stringify(categoryValues));
                        $('#download_certificate').append(categoryValuesInput);
                        $('#download_certificate').submit();
        });
        $('#validateButton').on('click', function(event) {
            event.preventDefault();
            $("#Upload_promotion_modal").modal('show');
        });

        $('#download').click(function() {
            $.ajax({
                url: "{{ admin_url('Download_dispatch/' . encryptId($promotion->id)) }}",
                type: 'POST',                
                success: function(response) {
                    console.log(response.fileContent);
                    console.log(response);
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

        $("#updateForm").submit(function(e) {
            e.preventDefault();
            var isValid = true;
            $(".label-box").each(function() {
                // Get the input element and the corresponding error message div
                var inputElement = $(this);
                var errorMessage = inputElement.next(".error-message");

                // Check if the label box is empty
                if (inputElement.val().trim() === "") {
                    isValid = false;

                    // Display an error message under the label box
                    errorMessage.text("Please fill category values.");
                    inputElement.addClass("is-invalid");
                } else {
                    // Clear the error message and remove the 'invalid' class if it's valid
                    errorMessage.text("");
                    inputElement.removeClass("is-invalid");
                }
            });
            if (isValid) {
                this.submit();
            } else {
                $(".error-message").show();
            }

        });
    });
</script>
@endpush
@stop
