@extends('admin.layouts.admin')
@section('content')
@section('title', 'General Settings View')
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
                                        <div class="row">
                                            <div class="col-12 col-sm-12">
                                                <div class="card card-primary card-outline card-tabs">
                                                    <div class="card-body">
                                                        <div class="tab-content" id="custom-tabs-three-tabContent"
                                                            style="margin-top: -51px;">
                                                            <div class="tab-pane fade active show"
                                                                id="custom-tabs-four-job" role="tabpanel"
                                                                aria-labelledby="custom-tabs-four-job-tab">
                                                                <div class="page-title-actions clearfix">


                                                                    <a class="float-left mr-1 mob-mt-1"
                                                                        href="{{ admin_url('General_Settings_Edit') }}">
                                                                        <button class="btn btn-primary float-left"
                                                                            style="margin-left: 862px;">Edit </button>
                                                                    </a>
                                                                </div>
                                                                <dl class="row">
                                                                    <dt class="col-sm-8">Promotion Upload will be
                                                                        allowed only before the start date </dt>
                                                                    <dd class="col-sm-4 price">
                                                                        {{ $General_Settings->upload_allowed_days }}
                                                                        Day(s)</dd>

                                                                    <dt class="col-sm-8">manufacturer will be able to
                                                                        edit the document only before </dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->edit_document }}.PM
                                                                    </dd>
                                                                    <dt class="col-sm-8">Start Date</dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->start_date }}
                                                                    </dd>
                                                                    <dt class="col-sm-8">End Date</dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->end_date }}</dd>
                                                                    <dt class="col-sm-8">Bulk File gets Generated on</dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->generated_on }}</dd>
                                                                    <dt class="col-sm-8">Manual Upload Dates</dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->upload_dates }}</dd>
                                                                    <dt class="col-sm-8">File Submitted by Brand till</dt>
                                                                    <dd class="col-sm-4">
                                                                        {{ $General_Settings->submitted_till }}</dd>

                                                                </dl>
                                                            </div>

                                                            <div class="tab-pane fade  " id="custom-tabs-users"
                                                                role="tabpanel"
                                                                aria-labelledby="custom-tabs-four-job-tab">
                                                                <div class="page-title-actions clearfix">


                                                                    <a class="float-right mr-1 mob-mt-1"
                                                                        href="{{ admin_url('Free_Campaign_User_Edit') }}">
                                                                        <button
                                                                            class="btn btn-primary float-right ">Edit
                                                                        </button>
                                                                    </a>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.card -->
                                            </div>
                                        </div>
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
                            filename: 'Upload Log List',
                            title: '',
                            exportOptions: {
                                columns: 'th:not(:last-child)'
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
                    url: "{{ admin_url('upload_logs') }}",
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
                        data: 'file_orgname',
                        name: 'file_orgname'
                    },
                    {
                        data: 'error_count',
                        name: 'error_count'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'upload_by',
                        name: 'upload_by'
                    },
                    {
                        data: 'date_time',
                        name: 'date_time'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                    },


                ]
            });




        });
    </script>
@endpush
@stop
