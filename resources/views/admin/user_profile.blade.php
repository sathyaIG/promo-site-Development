@extends('admin.layouts.admin')
@section('title', 'User Profile')
@section('pageurl', admin_url('profile'))
<style type="text/css">
    .error {
        color: red;
        margin: 10px;
    }

    .fields {
        font-weight: bold;
    }
</style>
@section('content')
<!-- start page content wrapper-->
<div class="page-content-wrapper">
    <!-- start page content-->
    <div class="page-content">
        <div class="page-breadcrumb  d-sm-flex align-items-center mb-3">
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 align-items-center">
                        <li class="breadcrumb-item"><a href="{{ admin_url('dashboard') }}">
                                <ion-icon name="home-outline"></ion-icon>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">User Profile</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto button-action">
                <div class="btn-group btn-skew">
                    <a id="rental_print_sign" data-bs-toggle="modal" data-bs-target="#bs-example-modal-md" class="btn btn-outline-primary">Change Password</a>
                </div>
                <div class="btn-group btn-skew">
                    <a class="btn btn-outline-primary" href="{{ admin_url('profileEdit') }}"> Edit</a>
                </div>
                <div class="btn-group btn-skew">
                    <a class="btn btn-outline-primary" href="{{ admin_url('dashboard') }}"> Back</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="">
                        <img src="{{ getProfileImage($user_detail->profile_image) }}" width="80" alt="" class="rounded-circle p-1 shadow-sm">
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">{{ $user_detail->name }}</h6>
                        <p class="mb-0">{{ $user_detail->email }}</p>
                    </div>
                </div>
                <hr>
                <div class="">
                    <div class="form-body">
                        <form class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">User Name : </label>
                                <p class="fields">{{ $user_detail->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact number</label>
                                <p class="fields">{{ $user_detail->mobile }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <p class="fields">{{ $user_detail->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <p class="fields">
                                    {{-- {{ isset($country_detail->country) ? $country_detail->country : '' }} --}}
                                    {{getCustomValue('admin_country', 'country', $country_detail->country);}}
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page content-->
</div>
<div id="bs-example-modal-md" class="modal fade modal-size" tabindex="7" aria-labelledby="bs-example-modal-md" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ">
            <div class="modal-header  align-items-center d-flex justify-content-center">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row d-flex justify-content-center">
                <!-- Content -->
                <form id="ChangePasswordSubmit" class="forms-sample" action="{{ admin_url('change_profile_password') }}" method="post">
                    <div class="modal-content checkoutpopup">
                        <div class="modal-body">
                            @csrf
                            <div class="frmrow">
                                <label class="required" for="">Old Password</label>
                                <div class="input-group">
                                    <input autocomplete="off" type="password" id="old_password" name="old_password" placeholder="Old Password" class="form-control" value="">

                                    <div class="input-group-text"><i class="fas fa-eye-slash" id="eye1"></i>
                                        @error('old_password')
                                        <span class="error ">{{ $errors->first('old_password') }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="frmrow">
                                <label class="required" for="">New Password</label>
                                <div class="input-group">
                                    <input autocomplete="off" type="password" id="password" name="password" placeholder="New Password" class="form-control" value="">
                                    <div class="input-group-text"><i class="fas fa-eye-slash" id="eye"></i>
                                    </div>
                                </div>
                                <div class="frmrow">
                                    <label class="required" for="">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" class="form-control" value="">
                                        <div class="input-group-text"><i class="fas fa-eye-slash" id="eye3"></i>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group" style="float:right;">
                                                <button type="submit" class="btn btn-success" style="margin-top: 11px;">Change
                                                    Password</button>
                                                <input type="reset" class="btn btn-danger" id="reset" name="reset" value="Reset" style="margin-top: 0.65rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.modal-content -->
</div>
@push('script')

<script type="text/javascript">
    var toastMixin = Swal.mixin({
        toast: true,
        icon: 'success',
        title: 'General Title',
        animation: true,
        position: 'top-right',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    @if($message = Session::get('success'))
    toastMixin.fire({
        icon: 'success',
        animation: true,
        title: '{{ $message }}'
    });
    @endif

    @if($message = Session::get('error'))
    toastMixin.fire({
        icon: 'error',
        animation: true,
        title: '{{ $message }}',
    });
    @endif

    $(function() {


        $('#eye').click(function() {

            if ($(this).hasClass('fa-eye-slash')) {

                $(this).removeClass('fa-eye-slash');

                $(this).addClass('fa-eye');

                $('#password').attr('type', 'text');

            } else {

                $(this).removeClass('fa-eye');

                $(this).addClass('fa-eye-slash');

                $('#password').attr('type', 'password');
            }
        });
        $('#eye1').click(function() {

            if ($(this).hasClass('fa-eye-slash')) {

                $(this).removeClass('fa-eye-slash');

                $(this).addClass('fa-eye');

                $('#old_password').attr('type', 'text');

            } else {

                $(this).removeClass('fa-eye');

                $(this).addClass('fa-eye-slash');

                $('#old_password').attr('type', 'password');
            }
        });
        $('#eye3').click(function() {

            if ($(this).hasClass('fa-eye-slash')) {

                $(this).removeClass('fa-eye-slash');

                $(this).addClass('fa-eye');

                $('#confirm_password').attr('type', 'text');

            } else {

                $(this).removeClass('fa-eye');

                $(this).addClass('fa-eye-slash');

                $('#confirm_password').attr('type', 'password');
            }
        });
    });
    $(function() {
        $(document).on('click', '#reset', function() {
            $('#ChangePasswordSubmit').validate().resetForm();

        });

        $('#ChangePasswordSubmit').validate({
            rules: {
                old_password: {
                    required: true,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,
                    // remote: {
                    //     url: "{{ admin_url('change_profile_password') }}",
                    //     type: "post",
                    //     headers: {
                    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    //     },
                    //     data: {
                    //         old_password: function() {
                    //             return $("input[name='old_password']").val();
                    //         },
                    //         userid: "{{ encryptId($user_detail['id']) }}"
                    //     },
                    //     dataFilter: function(data) {
                    //         var json = JSON.parse(data);
                    //         if (json.msg == "true") {
                    //             return "\"" + "Incorrect Old Password" + "\"";
                    //         } else {
                    //             return 'true';
                    //         }
                    //     }
                    // }
                },
                password: {
                    required: true,
                    passcheck: true,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,

                },
                confirm_password: {
                    required: true,
                    equalTo: "#password",
                    passcheck: true,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,

                },
            },
            messages: {
                old_password: {
                    required: "Please Enter Old-Password",
                    noSpaceAtEdges: "Please enter valid input"
                },
                password: {
                    required: "Please Enter Password",
                    passcheck: "Password should be alphanumeric, case sensitive with minimum of 12 characters",
                    noSpaceAtEdges: "Please enter valid input"

                },
                confirm_password: {
                    required: "Please Enter Confirm-Password",
                    equalTo: "Password and confirm password mismatch",
                    passcheck: "Password should be alphanumeric, case sensitive with minimum of 12 characters",
                    noSpaceAtEdges: "Please enter valid input"

                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.input-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
        });
    });
</script>
@endpush

@stop