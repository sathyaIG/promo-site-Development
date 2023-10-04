@extends('admin.layouts.admin')
@section('content')
@section('title', 'Report')
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

                            <div class="card-body">
                                <form class="col-md-1 float-right" action="{{ admin_url('export_output') }}" id="download_certificate" method="post">
                                    @csrf
                                    <input type="hidden" name="selectedIndexId" class="selectedPromotionIds" />
                                    <input type="hidden" name="promo_type" class="promo_type" />
                                    <button type="submit" id="export_button" style="margin-right: -38px; display:none"  class="btn btn-xs btn-primary">Export Output File</button>
                                </form>

                                <hr>
                                <table id="region_list_table" class="table  table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Uploaded Date</th>
                                            <th>Manufacturer Name</th>
                                            <th>File Name</th>
                                            <th>Status</th>
                                            <!-- <th>Status</th> -->
                                            <th>Promo Type</th>
                                            <th>Rejected File</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>


                                    <tbody>

                                    </tbody>
                                </table>
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
    $(function() {
        var table = $('#region_list_table').DataTable({
            "autoWidth": false,
            // dom: 'Bfrtip',
            aLengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            // buttons: false,
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
                url: "{{ admin_url('report') }}",
                type: 'GET',
                data: function(d) {
                    d.name = $('input[name=name]').val();
                    d.email = $('input[name=email]').val();
                    d.clientsearch = $('#client_search').val();
                }
            },
            columns: [{
                    data: 'check_box',
                    orderable: false,
                    searchable: false
                },
                // {
                //     data: 'DT_RowIndex',
                //     orderable: false,
                //     searchable: false
                // },
               
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'manufacturer_name',
                    name: 'manufacturer_name'
                },
                {
                    data: 'report_name',
                    name: 'report_name'
                },

                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'promo_type',
                    name: 'promo_type'
                },
                {
                    data: 'rejected_file',
                    name: 'rejected_file'
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

        $(document).on('click', '.StatusChange', function() {
            var upload_id = $(this).data('id');
            var types = $(this).data('type');
            var rejectCount = $(this).data('rejectcount');
            var options = ['Select Options','Approve', 'Reject'];
            if(rejectCount > 0){
                var title = 'Already Some SKU are rejected Are you Sure want to Approve or Reject the SKU';
            }else{
                var title = 'Are you sure want to Approve or Reject the SKU';
            }
            
            var text = 'Submit';
            var btncolor = '#dc3545';
            var dropdownHtml = '<select id="dropdownOptions" class="form-control">';
            for (var i = 0; i < options.length; i++) {
                dropdownHtml += '<option value="' + options[i] + '">' + options[i] + '</option>';
            }
            dropdownHtml += '</select>';
            var headingHtml =
                '<h4 id="reviewHeading" style="display: none;margin-right: 388px;">Review:</h4>'; // Initialize headingHtml with display: none

            var textareaHtml =
                '<textarea id="textAreaInput" class="form-control" style="display: none;margin-top: 3px;"></textarea>';

            var errorSpan = 
                '<span id="error-span" style="color:red; float:left">Review Field is Required</span>'

            Swal.fire({
                title: title,
                html: dropdownHtml + headingHtml + textareaHtml + errorSpan,
                showCancelButton: true,
                confirmButtonText: text,
                confirmButtonColor: btncolor,
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    var selectedOption = $('#dropdownOptions').val();
                    var textAreaValue = $('#textAreaInput').val();
                    return {
                        selectedOption: selectedOption,
                        textAreaValue: textAreaValue
                    };
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    var selectedOption = result.value.selectedOption;
                    var textAreaValue = result.value.textAreaValue;

                    $.ajax({
                        url: '{{ admin_url('ManufacturerStatus ') }}',
                        type: 'post',
                        data: {
                            upload_id: upload_id,
                            selected_option: selectedOption,
                            textarea_value: textAreaValue
                        },
                        success: function(response) {
                            Swal.fire('Success',
                                'success');
                            location.reload(true);
                        },
                        error: function(error) {
                            Swal.fire('Error',
                                'Failed to store data in the database', 'error');
                        }
                    });
                }
            });

            if($('#dropdownOptions').val() == 'Select Options'){
                $('.swal2-confirm').attr('disabled', 'disabled')
                $('#error-span').css('display', 'none')
            }else{
                $('.swal2-confirm').removeAttr('disabled', 'disabled')
                $('#error-span').css('display', 'none')
            }           

            $('#textAreaInput').on('keyup', function(e) {
                var reason = $('#textAreaInput').val().trim();
                if (reason == '') {
                    $('#error-span').css('display', 'block')
                    $('.swal2-confirm').attr('disabled', 'disabled')
                } else {
                    $('#error-span').css('display', 'none')
                    $('.swal2-confirm').removeAttr('disabled', 'disabled')
                }
            })

            $('#dropdownOptions').change(function() {
                if ($(this).val() === 'Reject') {
                    $('#textAreaInput').show();
                    $('#reviewHeading').show();
                    $('.swal2-confirm').attr('disabled', 'disabled')
                    $('#error-span').css('display', 'block')

                } else if($(this).val() === 'Approve') {
                    $('#textAreaInput').hide();
                    $('#reviewHeading').hide();
                    $('.swal2-confirm').removeAttr('disabled', 'disabled')
                    $('#error-span').css('display', 'none')

                }else {
                    $('#error-span').css('display', 'none')
                    $('.swal2-confirm').attr('disabled', 'disabled')
                }
            });

           
        });

    });

    $('#download_certificate').on('click', function(event) {
        event.preventDefault();       
        $('#download_certificate').submit();

        setTimeout(function () {
           window.location.reload()
        }, 2000); // Simulate a 2-second delay (adjust as needed)
    });

    $(document).ready(function() {
        var selectedPromotionIds = [];
        $('#region_list_table tbody').on('change', '.promotionId', function() {
            console.log('check')
            var selectedType = $(this).data('id');
            var checkboxesWithSameType = $('.promotionId[data-id="' + selectedType + '"]');
            var anyChecked = checkboxesWithSameType.is(':checked')
            if (anyChecked) {
                selectedPromotionIds = checkboxesWithSameType.filter(':checked').map(function() {
                    return $(this).val();
                }).get();
            } else {
                selectedPromotionIds = selectedPromotionIds.filter(function(id) {
                    return checkboxesWithSameType.filter(':checked').map(function() {
                        return $(this).val();
                    }).get().indexOf(id) === -1;
                });
            }

            if (anyChecked || selectedPromotionIds.length > 0) {
                selectedPromotionIds = checkboxesWithSameType.filter(':checked').map(function() {
                    return $(this).val();
                }).get();
            } else {
                selectedPromotionIds = [];
            }

            let selectedPromotionString = selectedPromotionIds.toString();
            $('.selectedPromotionIds').val(selectedPromotionString);
            $('.promo_type').val(selectedType);

            $('.promotionId').each(function() {
                if ($(this).data('id') !== selectedType) {
                    $(this).prop('disabled', anyChecked);
                }
            });
            
            if ($(".promotionId:checked").length > 0) {                
                $("#export_button").css("display", 'block');
            } else {
                $("#export_button").css("display", 'none');
            }


        });
        // sendPromotionIds(selectedPromotionIds);

        

    })

    // $('#export_button').on('click', function() {
    //     var promotionId = $('.selectedPromotionIds').val();
    //     var promo_type = $('.promo_type').val();
    //     $.ajax({
    //         url: '{{ admin_url('export_output ') }}',
    //         type: 'post',
    //         data: {
    //             selectedIndexId: promotionId,
    //             promo_type: promo_type
    //         },
    //         // success: function(response) {
    //         //     Swal.fire('Success',
    //         //         'success');
    //         //     location.reload(true);
    //         // },
    //         // error: function(error) {
    //         //     Swal.fire('Error',
    //         //         'Unable to process', 'error');
    //         // }
    //     });

    // })
</script>
@endpush
@stop