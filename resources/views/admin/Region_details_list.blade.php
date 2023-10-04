@extends('admin.layouts.admin')
@section('content')
@section('title', 'Region List')
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
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard') }}" title="Home"><img
                                src="{{ asset('public/assets/images/home.svg') }}" alt="" id="align-image"
                                width="20" height="20" style="vertical-align: text-top;"> </a><span
                            style="margin:10px">@yield('title')</span> </h4>

                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="col" style="text-align: end;">
                                    <a class="btn btn-outline-primary ms-auto" href="{{ admin_url('RegionAdd') }}"> Add
                                        Region</a>

                                </div>



                                <hr>
                                <table id="region_list_table" class="table  table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Region</th>
                                            <th>State</th>
                                            <th>City</th>
                                            <th>Region Code</th>
                                            <th>Region ID</th>
                                            <th>Status</th>
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

<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->


@push('script')
    <script type="text/javascript">
        $(function() {
            var table = $('#region_list_table').DataTable({
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
                    url: "{{ admin_url('region_management') }}",
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
                        data: 'region',
                        name: 'region'
                    },
                    {
                        data: 'state',
                        name: 'state'
                    },
                    {
                        data: 'city',
                        name: 'city'
                    },
                    {
                        data: 'region_code',
                        name: 'region_code'
                    },
                    {
                        data: 'region_id',
                        name: 'region_id'
                    },
                    {
                        data: 'profile_status',
                        name: 'profile_status'
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
                var region_id = $(this).data('id');
                var types = $(this).data('type');
                if (types == 1) {
                    var title = 'Do you want to In-Activate the Region';
                    var text = 'In-Activate';
                    var btncolor = '#dc3545'
                } else {
                    var title = 'Do you want to Activate the Region';
                    var text = 'Activate';
                    var btncolor = '#7ddc35'
                }
                Swal.fire({
                    title: title,
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: text,
                    confirmButtonColor: btncolor,
                    denyButtonColor: '#28a745',
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ admin_url('RegionStatus') }}",
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                region_id: region_id,
                                types: types
                            },
                            success: function(response) {
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-right',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter',
                                            Swal.stopTimer)
                                        toast.addEventListener('mouseleave',
                                            Swal.resumeTimer)
                                    }
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: response.msg
                                });
                                table.draw();
                            },
                            error: function(data) {
                                $.notify(data.responseJSON.msg, "error");
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire('Region not deleted.', '', 'info');
                    }
                })
            });

            $(document).on('click', '.RegionDelete', function() {
                var region_id = $(this).data('id');
                Swal.fire({
                    title: 'Do you want to Delete the Region',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: `Delete`,
                    denyButtonText: `Don't Delete`,
                    confirmButtonColor: '#dc3545',
                    denyButtonColor: '#28a745',
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ admin_url('RegionDelete') }}",
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                region_id: region_id
                            },
                            success: function(response) {
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-right',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    didOpen: (toast) => {
                                        toast.addEventListener('mouseenter',
                                            Swal.stopTimer)
                                        toast.addEventListener('mouseleave',
                                            Swal.resumeTimer)
                                    }
                                });
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Region Deleted successfully'
                                });
                                table.draw();
                            },
                            error: function(data) {
                                $.notify(data.responseJSON.msg, "error");
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire('Region not deleted.', '', 'info');
                    }
                })
            });
        });
    </script>
@endpush
@stop
