@extends('layouts.login')
@section('title', 'Account Activate')
@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- <div class="col-md-5 login_logobg1">
                <div class="">
                    <div class="col-md-10 login_logobgsectn"> <img
                            src="{{ asset('public/asset/images/common/login-logo.png') }}" width="92%" alt="">
                    </div>
                </div>
            </div> --}}
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <div class="" style="margin-top: 132px;text-align: center;">
                        <a href="{{ admin_url('dashboard') }}">
                            <img src="{{ asset('public/assets/images/logo.png') }}" alt="" width='150'
                                height="50" class="mx-auto">
                        </a>
                        <p class="text-muted mt-2 mb-4">Welcome to {{ env('APP_NAME') }}</p>

                    </div>
                </div>
            </div>

            <div class="col-md-12 loginfrm">
                <div class="col-md-12 loginfrmsectn" style="text-align: center;">
                    @error('email')
                        <div class="alert
                    alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Alert!</h5>
                            {{ $message }}
                        </div>
                    @enderror
                    @if ($status == 'not_activate')
                        <h2>Activate your Account</h2>
                        <div style="display: flex;
                        justify-content: center;">
                            <form id="ActivateAccountform" method="POST" action="{{ url('SubmitAccountActivate') }}" style="width: 22%;">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                <div class="input-group mb-3 form-group">
                                    <input id="email" type="email" class="form-control " readonly="" name="email"
                                        value="{{ $email ?? old('email') }}" required autocomplete="email">
                                </div>
                                <div class="input-group mb-3 form-group">
                                    <input id="password" type="password" class="form-control " name="password" required
                                        placeholder="Password" autofocus>
                                </div>
                                @error('password')
                                    <span class="error-message">{{ $message }}</span>
                                    <br>
                                @enderror
                                <div class="input-group mb-3">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password-confirm" required placeholder="Re-enter Password">
                                </div>
                                <div class="row">

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary"
                                            >Activate</button>
                                    </div>
                                    <!-- /.col -->
                                </div>
                            </form>
                        </div>
                    @else
                        <h2>Your account has already been activated.</h2>

                        <div class="col-12">
                            <div>
                                <a href="{{ url('login') }}"><button type="submit"
                                        class="btn btn-primary">Login</button></a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
@push('script')
    <script>
        $(function() {

            $.validator.addMethod("pwcheck", function(value) {
                return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) // consists of only these
                    &&
                    /[a-z]/.test(value) // has a lowercase letter
                    &&
                    /[A-Z]/.test(value) // has a lowercase letter
                    &&
                    /=!\-@._*]*$]/.test(value) // has a lowercase letter
                    &&
                    /\d/.test(value) // has a digit
                    &&
                    value.length >= 12 // has a minimum 12 Charactor length
            });

            $.validator.addMethod("passcheck", function(value) {
                return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{12,30}$/.test(
                    value) // consists of only these

            });


            $('#ActivateAccountform').validate({
                rules: {
                    email: {
                        required: true,
                        email: true,
                    },
                    password: {
                        required: true,
                        passcheck: true,
                    },
                    'password-confirm': {
                        required: true,
                        equalTo: "#password",
                        passcheck: true,
                    },
                },
                messages: {
                    email: {
                        required: "Enter official mail ID",
                        email: "Enter valid email ID"
                    },
                    password: {
                        required: "Please entered new a new password",
                        passcheck: " Password should be alphanumeric, case sensitive with minimum of 12 characters"
                    },
                    'password-confirm': {
                        required: "Please re-entered new a new password",
                        equalTo: "Mismatch of new and confirm password",
                        passcheck: "Password should be alphanumeric, case sensitive with minimum of 12 characters"
                    },
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endpush
