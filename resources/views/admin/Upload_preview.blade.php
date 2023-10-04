@extends('admin.layouts.admin')
@section('content')
@section('title', 'Report Preview')
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
                                <a class="btn btn-outline-primary ms-auto" href="{{ admin_url('report') }}" style="position: absolute;
                                float: right;">Back</a>


                            </div>


                            <hr>
                            <form id="updateForm" method="post" action="{{ admin_url('create/' . encryptId($promotion->id)) }}">
                                @csrf
                                {{-- <input type="hidden" name="promotion_id" value="{{ encryptId($promotion->id) }}"> --}}
                                <table id="region_list_table" class="table table-striped table-bordered" style="width: 97%;margin-left: 16px;">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Manufacturer/Suppler Name</th>
                                            <th>Code</th>
                                            <th>Start Date(DD-MM-YYYY)</th>
                                            <th>End Date(DD-MM-YYYY)</th>
                                            <th>Promo Offer Details</th>
                                            <th>Category Value</th>
                                            <th>Reject</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @php
                                        $i = 1;
                                        @endphp
                                        @foreach ($preview as $index => $listDetails)
                                        <tr>
                                            <td>{{ $i }}
                                            </td>
                                            <td>{{ $listDetails->manufacturer_name ? $listDetails->manufacturer_name : '' }}
                                            </td>
                                            <td>{{ $listDetails->code ? $listDetails->code : '' }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $listDetails->start_date)->format('d-m-Y') }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $listDetails->endDate)->format('d-m-Y') }}
                                            </td>
                                            <td>{{ isset($listDetails->offerDetails) ? $listDetails->offerDetails : '' }}
                                            </td>
                                            @if($listDetails->approveRejectStatus == 2)
                                            <td class="for"><input type="number" autocomplete="off" readonly class="form-control label-box-rejected" name="category_values[]" id="category_value_{{ $index }}" value="{{ $listDetails->category_value }}">
                                                <div class="error-message" style="color: red"></div>
                                                <input type="hidden" name="indexId[]" id="indexId{{ $index }}" value="{{ $listDetails->id }}">
                                            </td>
                                            @else
                                            <td class="for"><input type="number" min="0" step="0.1" onkeydown="return event.keyCode !== 69" autocomplete="off" class="form-control label-box" name="category_values[]" id="category_value_{{ $index }}" value="{{ $listDetails->category_value }}">
                                                <div class="error-message" style="color: red"></div>
                                                <input type="hidden" name="indexId[]" id="indexId{{ $index }}" value="{{ $listDetails->id }}">
                                            </td>

                                            @endif
                                            
                                            <td>

                                                @if($listDetails->approveRejectStatus == 2)
                                                    <a class="reasoncheck" style="cursor: pointer;" data-reason="{{ $listDetails->rejectComments }}">Already Reject Click to View Reason</a>
                                                @else
                                                <a class="btn btn-xs btn-danger reject" id="reject" data-id="{{ encryptId($listDetails->id) }}" data-promotion="{{ encryptId($promotion->id) }}"><i class="fa fa-ban"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                        $i++;
                                        @endphp
                                        @endforeach
                                    </tbody>

                                </table>
                                <button type="submit" class="btn btn-primary" id="submitid" style="position: absolute;
                                    top: 25px;
                                    right: 2%">Submit</button>

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
                    <h4 class="modal-title" id="Upload_promotion_modalLabel">Enter Reason</h4>
                    <button type="button" class="btn-close cls-modal" data-bs-dismiss="modal" id="close_modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input id="currentIndex" class="currentIndex" type="hidden" />
                        <input id="promotion" class="promotion" type="hidden" />
                        <div class="col-md-10 mb-3 form-group">
                            <label class="form-label required">Reason </label>
                            <textarea name="reason" class="form-control reason" id="reason"></textarea>
                            <span style="color: red; display:none;" id="required">Reason Required</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="close_modal1" class="btn btn-light cls-modal" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="reject_submit" disabled class="btn btn-primary">Reject</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="reason_modal" class="modal fades" tabindex="-1" role="dialog" aria-labelledby="reason_modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="Upload_promotion_modalLabel">Reason</h4>
                    <button type="button" class="btn-close cls-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">                        
                        <div class="col-md-10 mb-3 form-group">
                            <label class="form-label required">Reason </label>
                            <textarea name="reasonview" readonly class="form-control reasonview" id="reasonview"></textarea>
                            <span style="color: red; display:none;" id="required">Reason Required</span>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>

    </div>
    @push('script')
    <script>
        $('.reject').on('click', function(event) {
            event.preventDefault();
            $('.currentIndex').val($(this).data('id'))
            $('.promotion').val($(this).data('promotion'))
            $("#Upload_promotion_modal").modal('show');
        });
        $('.reasoncheck').on('click', function(event) {
            event.preventDefault();
            $('.reasonview').val($(this).data('reason'));
            $("#reason_modal").modal('show');
        })
        $('#reject_submit').on('click', function(event) {
           var currentIndex = $('.currentIndex').val();
           var reason = $('.reason').val();
           var promotion = $('.promotion').val();
           $.ajax({
                url: "{{ admin_url('Reject_sku') }}",
                type: 'POST',
                data: {
                    currentIndex: currentIndex,
                    reason: reason,
                    promotion: promotion
                },
                success: function(response) {
                   if(response.message){
                    
                     window.location.reload();
                   }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        })

        $('#reason').on('keyup', function(e) {
            var reason = $('#reason').val().trim();
            if (reason == '') {
                $('#required').css('display', 'block')
                $('#reject_submit').attr('disabled', 'disabled');
            } else {
                $('#required').css('display', 'none')
                $('#reject_submit').removeAttr('disabled');
            }
        })

        document.getElementById('close_modal').addEventListener('click', function() {
            document.getElementById('reason').value = ''; // Clear the file input value
            $('#reject_submit').attr('disabled', 'disabled');
        });
        document.getElementById('close_modal1').addEventListener('click', function() {
            document.getElementById('reason').value = ''; // Clear the file input value
            $('#reject_submit').attr('disabled', 'disabled');
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
    </script>
    @endpush
    @stop