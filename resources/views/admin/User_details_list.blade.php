@extends('admin.layouts.admin')
@section('content')
@section('title', 'User List')
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
                                    <a class="btn btn-outline-primary ms-auto" href="{{ admin_url('UserAdd') }}"> Add
                                        User</a>
                                    <button type="submit" id="validateButton" class="btn btn-primary">Upload</button>
                                    <a href="{{ admin_url('upload_user_logs/') }}" class="btn btn-primary"
                                        id="uploadlog">Upload Logs</a>


                                </div>



                                <hr>
                                <table id="user_list_table" class="table  table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>User Role</th>
                                            <th>Department</th>
                                            <th>Business Type</th>
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
    <div id="Upload_promotion_modal" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="Upload_promotion_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="Upload_promotion_modalLabel">User Upload</h4>
                    <button type="button" class="btn-close cls-modal" id="close_modal" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="UploadPromotypes" class="forms-sample" method="post"
                    action="{{ admin_url('UserFileUpload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        {{-- <input type="hidden" name="business_type[]" id="business_type" />
                        <input type="hidden" name="theme[]" id="theme" />
                        <input type="hidden" name="promoType" id="promoType" /> --}}
                        <div class="row">
                            <div class="col-md-10 mb-3 form-group">
                                <label class="form-label required">User Files </label>
                                <input class="form-control rounded-pill" required onchange="validateFile(this)"
                                    type="file" name="user_file" id="user_file" accept=".xlsx"
                                    style="border-radius: 1.25rem;">
                                @error('user_file')
                                    <span class="error ">{{ $errors->first('user_file') }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary float-right" id="download_user">Download
                                Template</button>
                            <button type="button" id="close_modal1" class="btn btn-light cls-modal"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="upload_modal" class="btn btn-primary">Submit</button>
                        </div>
                </form>

            </div>

        </div>

    </div>

</div>

<!-- ============================================================== -->
<!-- End Page content -->
<!-- ============================================================== -->


@push('script')
    <script type="text/javascript">
        $('#download_user').click(function() {


            $.ajax({
                url: "{{ admin_url('UserDownload') }}",
                type: 'POST',

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
        $("#validateButton").click(function(event) {
            event.preventDefault();
            $("#Upload_promotion_modal").modal('show');
        });
        document.getElementById('close_modal').addEventListener('click', function() {
            document.getElementById('user_file').value = ''; // Clear the file input value
        });
        document.getElementById('close_modal1').addEventListener('click', function() {
            document.getElementById('user_file').value = ''; // Clear the file input value
        });
        $(function() {
            var table = $('#user_list_table').DataTable({
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
                            filename: 'User List',
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
                    url: "{{ admin_url('user_management') }}",
                    type: 'GET',
                    data: function(d) {
                        d.name = $('input[name=name]').val();
                        d.email = $('input[name=email]').val();
                        d.clientsearch = $('#client_search').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'business_type',
                        name: 'business_type'
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
                var user_id = $(this).data('id');
                var types = $(this).data('type');
                if (types == 1) {
                    var title = 'Do you want to In-Activate the User';
                    var text = 'In-Activate';
                    var btncolor = '#dc3545'
                } else {
                    var title = 'Do you want to Activate the User';
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
                            url: "{{ admin_url('UserStatus') }}",
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                user_id: user_id,
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
                        Swal.fire('User not deleted.', '', 'info');
                    }
                })
            });

            $(document).on('click', '.UserDelete', function() {
                var user_id = $(this).data('id');
                Swal.fire({
                    title: 'Do you want to Delete the User',
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
                            url: "{{ admin_url('UserDelete') }}",
                            type: 'post',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                user_id: user_id
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
                                    title: 'User Deleted successfully'
                                });
                                table.draw();
                            },
                            error: function(data) {
                                $.notify(data.responseJSON.msg, "error");
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire('User not deleted.', '', 'info');
                    }
                })
            });
        });
    </script>
@endpush
@stop
