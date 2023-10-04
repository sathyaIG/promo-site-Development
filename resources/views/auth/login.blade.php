@extends('layouts.login')
@section('title', 'Login')
@section('content')


<div class="account-pages my-5">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-4">
                <div class="text-center">
                    <a href="{{ admin_url('dashboard') }}">
                        <img src="{{ asset('public/assets/images/logo.png')}}" alt="" width='150' height="50" class="mx-auto">
                    </a>
                    <p class="text-muted mt-2 mb-4">Welcome to {{ env('APP_NAME') }}</p>

                </div>
                <div class="card">
                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <h4 class="text-uppercase mt-0">Sign In</h4>
                        </div>

                        <form id="loginform" action="{{ admin_url('logintry') }}" method="post">
                            @csrf
                            <div class="mb-3 validate-input form-group">
                                <label for="emailaddress" class="form-label">Email address</label>
                                <input class="form-control" type="email" name="email" id="emailaddress" placeholder="Enter your email">
                                @error('email')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3 validate-input form-group">
                                <label for="password" class="form-label">Password</label>
                                <input class="form-control" type="password" name="password" id="password" placeholder="Enter your password">
                                @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                    <label class="form-check-label" for="checkbox-signin">Remember me</label>
                                </div>
                            </div>

                            <div class="mb-3 d-grid text-center">
                                <button class="btn btn-primary" type="submit"> Log In </button>
                            </div>
                        </form>

                    </div> <!-- end card-body -->
                </div>
                <!-- end card -->

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <p> <a href="{{ route('password.request') }}" class=" ms-1" style="color:black"><i class="fa fa-lock me-1"></i>Forgot your password?</a></p>
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
                /\d/.test(value) // has a digit
        });

        $.validator.addMethod("passcheck", function(value) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{12,30}$/.test(value) // consists of only these

        });

        $('#loginform').validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                },
                password: {
                    required: true,
                    passcheck: true,
                },

            },
            messages: {
                email: {
                    required: "Please enter your E-Mail ID",
                    email: "Please enter a vaild E-Mail ID"
                },
                password: {
                    required: "Please enter your Password",
                    passcheck: "Password should be alphanumeric, case sensitive with minimum of 12 characters",
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