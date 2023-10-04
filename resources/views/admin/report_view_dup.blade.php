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
                                <form class="col-md-12" action="{{ admin_url('export_output') }}" id="download_certificate" method="post">
                                    @csrf
                                    <input type="hidden" name="selectedIndexId" class="selectedPromotionIds" />
                                    <input type="hidden" name="promo_type" class="selected_promo_type" />
                                    <input type="hidden" name="selected_department" class="selected_department" />
                                    <input type="hidden" name="selected_creteria" class="selected_creteria" />
                                    <input type="hidden" name="selected_zone" class="selected_zone" />
                                    <input type="hidden" name="selected_cities" class="selected_cities" />

                                    <div style="float:right; margin-right:25px !important">
                                    <button type="submit" id="export_button" style="display:none" class="btn btn-xs btn-primary">Export Output File</button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-start flex-wrap filter-wrapper">
                                <select name="search_promo_type" id="promo_type" class="form-control promo_type" style="width: 200px;">
                                    <option value="">Select Promotype</option>
                                    <option value="1">Single Promotype</option>
                                    <option value="2">Combo Promotype</option>
                                </select>

                                <select name="department" id="department" class="form-control department ms-4" style="width: 200px; display:none">
                                    <option value="">Select Department</option>
                                    @foreach($department as $listDepartment)
                                        <option value="{{ $listDepartment->id }}">{{ $listDepartment->department }}</option>
                                    @endforeach
                                </select>      

                                <select name="selection_creteria" id="selection_creteria" class="form-control selection_creteria ms-4" style="width: 200px; display:none">
                                    <option value="">Select List</option>
                                    <option value="Panindia">Pan India</option>
                                    <option value="Zone">Zone</option>
                                    <option value="Cities">Cities</option>
                                </select>

                                <div class="zone ms-4" style="display: none;">
                                <select name="selection_zone[]" data-width="200px" id="selection_zone" class="multiple-select form-control selection_zone" multiple>                                    
                                    <option value="North">North</option>
                                    <option value="South">South</option>
                                    <option value="East">East</option>
                                    <option value="West">West</option>
                                    <option value="Central">Central</option>
                                </select>
                                <input type="checkbox" id="checkbox" > Select All
                                </div>
                                <div class="cities ms-4" style="display: none;">
                                    <select name="selection_cities[]" data-width="300px" id="selection_cities" class="multiple-select form-control selection_zone" multiple>
                                        @foreach($citiesArray as $listCities)
                                            <option value="{{ $listCities }}">{{ $listCities }}</option>

                                        @endforeach
                                    </select>
                                    <input type="checkbox" id="cities_checkbox" > Select All
                                </div>   
                                </div>              
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

        $('#promo_type').on('change', function () {
                $('#department').val('');
                $('#selection_zone').val('');
                $('#selection_cities').val('');
                $('#selection_creteria').val('');
                $('#department').css('display','none')
                $('#selection_creteria').css('display','none');
                $('.zone, .cities').css('display','none')
            if($(this).val() == 1){
                $('#department').css('display','block')
                $('#selection_creteria').css('display','none');
            }else if($(this).val() == 2){
                $('#selection_creteria').css('display','block');
                $('#department').css('display','none')
            }else{                
                $('#department').css('display','none')
                $('#selection_creteria').css('display','none');
                $('.zone, .cities').css('display','none')
            }
            
            var selectedPromoType = $(this).val();
            table.ajax.url("{{ admin_url('report') }}?promo_type=" + selectedPromoType).load();
        });

        $('#department').on('change', function () {            
            
            var selectedDepartment = $(this).val();
            var selectedPromoType = $('#promo_type').val();
            table.ajax.url("{{ admin_url('report') }}?department=" + selectedDepartment +"&promo_type=" + selectedPromoType).load();
            if(selectedDepartment != '' && selectedPromoType != ''){
                $('.promotionId').css('display','block')
            }
        });

        $('#selection_creteria').on('change', function() {
            $('.zone').css('display','none')
            $('.cities').css('display','none');
            var selectedSelectionCreteria = $(this).val();
            var selectedPromoType = $('#promo_type').val();
            table.ajax.url("{{ admin_url('report') }}?selection_creteria=" + selectedSelectionCreteria +"&promo_type=" + selectedPromoType).load();
            if(selectedSelectionCreteria != '' && selectedPromoType != ''){
                $('.promotionId').css('display','block')
            }
            if(selectedSelectionCreteria == 'Zone'){
                $('.zone').css('display','block')
            }
            if(selectedSelectionCreteria == 'Cities'){
                $('.cities').css('display','block')
            }
        })

        var selectedValuesArray = [];
        $('#selection_zone').on('change', function() {
            var selectedValue = $(this).val();
            var selectedPromoType = $('#promo_type').val();
            var selectedSelectionCreteria = $('#selection_creteria').val();
            if (selectedValue && selectedValue.length > 0) {
                table.ajax.url("{{ admin_url('report') }}?selection_zone=" + selectedValue.join(',') +"&promo_type=" + selectedPromoType +"&selection_creteria=" + selectedSelectionCreteria ).load();
            }

            $('.selected_zone').val(selectedValue.join(','))
            $('#checkbox').prop('checked', selectedValue.length === $('#selection_zone option').length);
        })

        var selectedValuesCitiesArray = [];
        $('#selection_cities').on('change', function() {
            var selectedCitiesValue = $(this).val();
            var selectedPromoType = $('#promo_type').val();
            var selectedSelectionCreteria = $('#selection_creteria').val();
            if (selectedCitiesValue && selectedCitiesValue.length > 0) {
                table.ajax.url("{{ admin_url('report') }}?selection_cities=" + selectedCitiesValue.join(',') +"&promo_type=" + selectedPromoType +"&selection_creteria=" + selectedSelectionCreteria ).load();
            }

            $('.selected_cities').val(selectedCitiesValue.join(','))
            $('#cities_checkbox').prop('checked', selectedCitiesValue.length === $('#selection_cities option').length);
        })

        $(document).on('click', '.StatusChange', function() {
            var upload_id = $(this).data('id');
            var types = $(this).data('type');
            var rejectCount = $(this).data('rejectcount');
            var options = ['Select Options', 'Approve', 'Reject'];
            if (rejectCount > 0) {
                var title = 'Already Some SKU are rejected Are you Sure want to Approve or Reject the SKU';
            } else {
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
                '<span id="error-span" style="color:red; float:left">Review Field is Required</span>';

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
                    if($(this).data('processtype') == 1 && selectedOption == 'Approve'){
                        Swal.fire('Please Update The Category Value Before Approve');
                    }else{
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
                                setTimeout(function() {
                                    location.reload(true);
                                }, 2000);
                            
                        },
                        error: function(error) {
                            Swal.fire('Error',
                                'Failed to store data in the database', 'error');
                        }
                    });
                    }
                   
                }
            });

            if ($('#dropdownOptions').val() == 'Select Options') {
                $('.swal2-confirm').attr('disabled', 'disabled')
                $('#error-span').css('display', 'none')
            } else {
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

                } else if ($(this).val() === 'Approve') {
                    $('#textAreaInput').hide();
                    $('#reviewHeading').hide();
                    $('.swal2-confirm').removeAttr('disabled', 'disabled')
                    $('#error-span').css('display', 'none')

                } else {
                    $('#error-span').css('display', 'none')
                    $('.swal2-confirm').attr('disabled', 'disabled')
                }
            });


        });

    });

    $('#download_certificate').on('click', function(event) {
        event.preventDefault();
        $('#download_certificate').submit();

        setTimeout(function() {
            window.location.reload()
        }, 2000); // Simulate a 2-second delay (adjust as needed)
    });

    $(document).ready(function() {        
        var selectedPromotionIds = [];
        $('#region_list_table tbody').on('change', '.promotionId', function() {
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
            $('.selected_promo_type').val(selectedType);
            $('.selected_department').val($('#department').val());
            $('.selected_creteria').val($('#selection_creteria').val())
            

            $('.promotionId').each(function() {
                if ($(this).data('id') !== selectedType) {
                    $(this).prop('disabled', anyChecked);
                }
            });

            if ($(".promotionId:checked").length > 0 && $('#department').val() != '' ) {
                $("#export_button").css("display", 'block');
            }else if($(".promotionId:checked").length > 0 && $('#selection_creteria').val() == 'Panindia' ){
                $("#export_button").css("display", 'block');
            }else if($(".promotionId:checked").length > 0 && $('#selection_creteria').val() == 'Zone' && $('#selection_zone').val() != '' ){
                $("#export_button").css("display", 'block');
            }else if($(".promotionId:checked").length > 0 && $('#selection_creteria').val() == 'Cities' && $('#selection_cities').val() != '' ){
                $("#export_button").css("display", 'block');
            }
             else {
                $("#export_button").css("display", 'none');
            }


        });
        $("#checkbox").click(function(){
            if($("#checkbox").is(':checked') ){
                $("#selection_zone > option").prop("selected",true);
                $("#selection_zone").trigger("change.select2");
            }else{
                console.log('in');;
                $("#selection_zone > option").prop("selected", false);
                $("#selection_zone").trigger("change.select2");
            }
            updateSelectedValues('#selection_zone','.selected_zone');
        });

        $("#cities_checkbox").click(function(){
            if($("#cities_checkbox").is(':checked') ){
                $("#selection_cities > option").prop("selected",true);
                $("#selection_cities").trigger("change.select2");
            }else{
                console.log('in');;
                $("#selection_cities > option").prop("selected", false);
                $("#selection_cities").trigger("change.select2");
            }
            updateSelectedValues('#selection_cities','.selected_cities');
        });

        $('#selection_zone,#selection_cities').select2({
            // width: '100%'
        });
      
        function updateSelectedValues(id,className) {
            var selectedValues = $(id).val() || [];
            var selectedString = selectedValues.join(', ');
            $(className).val(selectedValues.join(','))
        }
        // sendPromotionIds(selectedPromotionIds);
    })

    
</script>
@endpush
@stop