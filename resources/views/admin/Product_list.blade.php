@extends('admin.layouts.admin')
@section('content')
@section('title','Product List')
@push('style')
@endpush
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
                            <hr>
                            <table id="region_list_table" class="table  table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Product Name</th>
                                        <th>Product ID</th>
                                        <th>Category L1</th>
                                        <th>Category L2</th>
                                        <th>Category L3</th>
                                        <th>Department</th>
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

@endpush
@stop