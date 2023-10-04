@extends('layouts.login')
@section('title', 'Reset Password')
@section('content')

<div class="account-pages my-5">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-4">
                <div class="text-center">
                    <a href="{{ admin_url('dashboard') }}">
                        <img src="{{ asset('public/assets/images/logo.png')}}" alt="" height="22" class="mx-auto">
                    </a>
                    <p class="text-muted mt-2 mb-4">Welcome to {{ env('APP_NAME') }}</p>

                </div>
                @error('email')
                <div class="alert alert-danger alert-dismissible">
                    <h5><i class="icon fas fa-ban"></i> Alert!</h5>
                    {{ $message }}
                </div>

                @enderror
                <div class="card">
                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <h4 class="text-uppercase mt-0">Reset your Password</h4>
                        </div>

                        <form id="resetpasswordform" method="POST" action="{{ admin_url('passwordreset_submit') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="mb-3 form-group">
                                <label for="emailaddress" class="form-label">Email address</label>
                                <input class="form-control" type="email" name="email" id="emailaddress" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 form-group">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" class="form-control " name="password" required placeholder="New Password" autofocus="">
                                @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3 form-group">
                                <label for="password" class="form-label">Password</label>
                                <input id="password-confirm" type="password" class="form-control" name="password-confirm" required placeholder="Re-enter New Password">
                                @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>



                            <div class="mb-3 d-grid text-center">
                                <button class="btn btn-primary" type="submit"> Update Password </button>
                            </div>
                        </form>

                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p> <a href="{{ route('login') }}" class=" ms-1" style="color:black"><i class="fa fa-user me-1"></i>Go to login</a></p>
                        <!-- <p class="text-muted">Don't have an account? <a href="pages-register.html" class="text-dark ms-1"><b>Sign Up</b></a></p> -->
                    </div> <!-- end col -->
                </div>
                <!-- end row -->

            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</div>
<!-- end page -->

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
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{12,30}$/.test(value) // consists of only these

        });


        $('#resetpasswordform').validate({
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
                    passcheck: " Password format mismatch- Password should be alphanumeric, case sensitive with minimum of 12 characters"
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