@extends('admin.layouts.admin')
@section('content')
@section('title', 'User View')
@section('pageurl', admin_url('user_management'))
<style type="text/css">
    .error {
        color: red;
        margin: 10px;
    }

    .fields {
        font-weight: bold;
    }
</style>
<!-- start page content wrapper-->
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
                                    <a class="btn btn-outline-danger ms-auto" href="{{ admin_url('user_management') }}"> Back</a>

                                </div>



                            <hr>
                            <div class="">
                                <div class="form-body">
                                    <form class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">User Name : </label>
                                            <p class="fields">{{ $user_details->name}}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Contact number</label>
                                            <p class="fields">{{ $user_details->mobile}}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <p class="fields">{{ $user_details->email}}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">User Role</label>
                                            <p class="fields">{{ isset($user_role_details->user_role)?$user_role_details->user_role:'' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Department</label>
                                            <p class="fields">{{ isset($department_details->department)?$department_details->department:'' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Business Type</label>
                                            <p class="fields">{{ isset($user_details->business_type)?getMultipleValue('admin_business_type',$user_details->business_type,'id','business_type'):'' }}</p>
                                        </div>
                                        @if($user_details->role == 5)
                                        <div class="col-md-6">
                                            <label class="form-label">Funding Category</label>
                                            <p class="fields">{{ isset($user_details->fundingCategory)?$user_details->fundingCategory:'' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Invoice</label>
                                            <p class="fields">{{ ($user_details->invoice == 1)?'True':'False' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Redemption Limit - Qty Per Order</label>
                                            <p class="fields">{{ isset($user_details->redemptionPerOrder)?$user_details->redemptionPerOrder:'' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Redemption Limit - Qty Per Member</label>
                                            <p class="fields">{{ isset($user_details->redemptionPerMember)?$user_details->redemptionPerMember:'' }}</p>
                                        </div>
                                        @endif
                                        

                                    </form>
                                </div>
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
@push('script')
@endpush
@stop