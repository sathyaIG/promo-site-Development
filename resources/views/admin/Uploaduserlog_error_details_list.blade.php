@extends('admin.layouts.admin')
@section('content')
@section('title', 'Upload Error Log List')
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
                    <h4 class="page-title-main"><a href="{{ admin_url('dashboard')}}" title="Home"><img src="{{ asset('public/assets/images/home.svg')}}" alt="" id="align-image" width="20" height="20" style="vertical-align: text-top;"> </a><span style="margin:10px">@yield('title')</span> </h4>

                </div>
                <div class="row">

                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                               
                                <div class="col" style="text-align: end;">
                                    <a class="btn btn-outline-success" href="{{ admin_url('UploadUserFileDownload/'.encryptId($upload_id)) }}"> Download</a>
                                    <a class="btn btn-outline-primary" href="{{ admin_url('upload_user_logs') }}"> Back</a>
                                </div>
                            <hr>
                            <table id="upload_list_table" class="table  table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>File Name</th>
                                        <th>Error Description</th>
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

        var table = $('#upload_list_table').DataTable({
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
                        filename: 'Upload Error Log List',
                        title: '',
                        exportOptions: {
                            columns: ':visible'
                        },
                    }, ]
                },
                'pageLength'
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            searching: true,
            "order": [
                [0, "desc"]
            ],
            ajax: {
                url: "{{ admin_url('Upload_user_view/' . encryptId($upload_id)) }}",
                type: 'GET',
                data: function(d) {
                    d.name = $('input[name=name]').val();
                    d.email = $('input[name=email]').val();
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
                    data: 'error',
                    name: 'error'
                },



            ]
        });




    });
</script>
@endpush
@stop