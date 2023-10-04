@extends('admin.layouts.admin')
@section('content')
@section('title', 'User Add')
@section('pageurl', admin_url('UserAdd'))
<style type="text/css">
    .error {
        color: red;
        margin: 10px;
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
                                <form id="UserAdd" autocomplete="off" class="forms-sample" method="post" action="{{ admin_url('UserAddSubmit') }}" enctype="multipart/form-data">
                                    @csrf


                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">User Name <span style="color: red">*</span></label>
                                            <input type="text" class="form-control" id="user_name" name="user_name" placeholder="User Name">
                                            @error('user_name')
                                            <span class="error ">{{ $errors->first('user_name') }}</span>
                                            @enderror
                                        </div>
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">Contact Number <span style="color: red">*</span></label>
                                            <input type="text" class="form-control " name="mobile" id="mobile" placeholder="Contact Number">
                                            <span id="p1length_error"></span>
                                            @error('mobile')
                                            <span class="error ">{{ $errors->first('mobile') }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">Email Address<span style="color: red">*</span></label>
                                            <input type="text" class="form-control " id="email" name="email" placeholder="Email">
                                            <span class="error email" style="display: none;">Email Address Should not Repeated</span>
                                        </div>
                                       <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                           
                                            <label class="form-label required">Email Address 1<span style="color: red">*</span></label>
                                            <input type="text" class="form-control " id="email1" name="email1" placeholder="Email">
                                            <span class="error email1" style="display: none;">Email Address Should not Repeated</span>
                                        </div>
                                       <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                            <label class="form-label required">Email Address 2<span style="color: red">*</span></label>
                                            <input type="text" class="form-control "id="email2" name="email2" placeholder="Email">
                                            <span class="error email2" style="display: none;">Email Address Should not Repeated</span>
                                        </div>
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">User Role <span style="color: red">*</span></label>
                                            <select class="form-select " name="user_role" id="user_role" aria-label="Default select example">
                                                <option value=''>Select User Role</option>
                                                @foreach ($userrole_details as $userrole)
                                                <option value="{{ $userrole->id }}">
                                                    {{ $userrole->user_role }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">Department <span style="color: red">*</span></label>
                                            <select class="form-select multiple-select" name="department[]" aria-label="Default select example" multiple>
                                                @foreach ($department_details as $department)
                                                <option value="{{ encryptId($department->id) }}">
                                                    {{ $department->department }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label required">Business Type <span style="color: red">*</span></label>
                                            <select class="form-select multiple-select" placeholder="Business Type" name="business_type[]" id="business_type" multiple="multiple">
                                                @foreach ($business_type_details as $business_type)
                                                <option value="{{ encryptId($business_type->id) }}">
                                                    {{ $business_type->business_type }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group hideformanufacturer" style="display: none;">
                                            <label class="form-label required">Manufacturer Lists <span style="color: red">*</span></label>
                                            <select class="form-select multiple-select" name="manufacturer[]" id="manufacturer" style="width: 100%;" multiple="multiple">
                                                @foreach ($getManufacturer as $manufacturerlist)
                                                <option value="{{ encryptId($manufacturerlist->id) }}">
                                                    {{ $manufacturerlist->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                            <label class="form-label required">Funding Category <span style="color: red">*</span></label>
                                            <input type="number" min="0" class="form-control" placeholder="Funding Category" name="category">
                                        </div>

                                    
                                        <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                            <label class="form-label required">Redemption Limit - Qty Per Member <span style="color: red">*</span></label>
                                            <input type="text" class="form-control" placeholder="Redemption Limit - Qty Per Member" name="member" id="member">
                                        </div>
                                        <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                            <label class="form-label required">Redemption Limit - Qty Per Order <span style="color: red">*</span></label>
                                            <input type="text" class="form-control" placeholder="Redemption Limit - Qty Per Order" name="order" id="order">
                                        </div>


                                        <div class="col-md-6 mb-3 form-group hideforothers" style="display: none;">
                                            <label class="form-label required">Invoice <span style="color: red">*</span></label></br>
                                            <input type="radio" name="invoice" id="invoice" value="1" /> True
                                            <input type="radio" name="invoice" checked id="invoice" value="0" /> False
                                        </div> --}}
                                        <div class="col-md-6 mb-3 form-group">
                                            <label class="form-label">Profile Image </label>
                                            <input class="form-control" type="file" name="profile_image" id="profile_image" accept=".png, .jpg, .jpeg" style="border-radius: 1.25rem;" onchange="return fileValidation()">
                                            <span id="profile_image_error" class="error"></span>
                                        </div>
                                    </div>


                                    <div class="text-end mt-3">
                                        <button type="submit" id="user_add" class="px-4 btn btn-primary btn-skew"><span class="fs-7">Add</span></button>
                                        <a class="px-4 btn btn-secondary btn-skew fs-7" href="{{ admin_url('user_management') }}">Cancel</a>
                                    </div>
                                </form>
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
<script type="text/javascript">
    function fileValidation() {
        var fileInput = document.getElementById('profile_image');
        var filePath = fileInput.value;

        // Allowing file type
        var allowedExtensions = /(\.png|\.jpeg|\.gif|\.jpg)$/i;

        if (!allowedExtensions.exec(filePath)) {
            var errorSpan = document.getElementById('profile_image_error');
            errorSpan.textContent = 'Accepted file formats are: PNG, JPEG, GIF,JPG';
            $("#user_add").prop('disabled', true); //disable 
            form.submit();
            fileInput.value = '';

            return false;

        } else {
            $("#user_add").prop('disabled', false); //disable 
            form.submit();
        }
    }

    $('#user_role').on('change', function() {
        let currentUserValue = $(this).val();
        if (currentUserValue == 5) {
            $('.hideforothers').css('display', 'block');
            $('.hideformanufacturer').css('display', 'none');
        } else {
            $('.hideforothers').css('display', 'none');
            $('.hideformanufacturer').css('display', 'block');
        }
    })


    $('#reset').on('click', function() {
        window.location.reload();
    })

    function checkEmailUniqueness() {
        const email = $('#email').val();
        const email1 = $('#email1').val();
        const email2 = $('#email2').val();

        const isEmailUnique = email !== email1 && email !== email2;
        const isEmail1Unique = email1 === '' || (email1 !== email && email1 !== email2);
        const isEmail2Unique = email2 === '' || (email2 !== email && email2 !== email1);

        // Check if all email fields are empty
        const allEmpty = email === '' && email1 === '' && email2 === '';

        const isAllUnique = (allEmpty || (isEmailUnique && isEmail1Unique && isEmail2Unique));

        if (isAllUnique) {
            $('.email, .email1, .email2').css('display', 'none');
            $("#user_add").prop('disabled', false);
        } else {
            if (isEmail1Unique) {
                $('.email1').css('display', 'none');
            } else {
                $('.email1').css('display', 'block');
            }

            if (isEmail2Unique) {
                $('.email2').css('display', 'none');
            } else {
                $('.email2').css('display', 'block');
            }

            if (isEmailUnique) {
                $('.email').css('display', 'none');
            } else {
                $('.email').css('display', 'block');
            }

            $("#user_add").prop('disabled', true);
        }
    }

    $('#email, #email1, #email2').on('input', checkEmailUniqueness);
   
    $(function() {
        $.validator.addMethod("customEmail", function(value, element) {
            // Define a regular expression to match a valid email with TLD.
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            return this.optional(element) || emailPattern.test(value);
        }, "Please enter a valid email address.");
       
        validator = $('#UserAdd').validate({
            rules: {
                user_name: {
                    required: true,
                    maxlength: 30,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,
                    alphanumericwithspace: true,


                },
                mobile: {
                    required: true,
                    number: true,
                    maxlength: 15,
                    minlength: 10,
                },
                "department[]": {
                    required: true,
                    noSpaceAtEdges: true,
                    noConsecutiveSpaces: true,
                },
                email: {
                    required: true,
                    customEmail: true,
                    maxlength: 50,
                    remote: {
                        url: "{{ admin_url('Useremailcheck') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            email: function() {
                                return $("input[name='email']").val();
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            if (json.msg == "true") {
                                return "\"" + "User email already exists" + "\"";
                            } else {
                                return 'true';
                            }
                        }
                    },
                },
                email1: {
                    required: true,
                    customEmail: true,
                    maxlength: 50,
                    remote: {
                        url: "{{ admin_url('Useremailcheck') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            email: function() {
                                return $("input[name='email']").val();
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            if (json.msg == "true") {
                                return "\"" + "User email already exists" + "\"";
                            } else {
                                return 'true';
                            }
                        }
                    },
                },

                email2: {
                    required: true,
                    customEmail: true,
                    maxlength: 50,
                    remote: {
                        url: "{{ admin_url('Useremailcheck') }}",
                        type: "post",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            email: function() {
                                return $("input[name='email']").val();
                            }
                        },
                        dataFilter: function(data) {
                            var json = JSON.parse(data);
                            if (json.msg == "true") {
                                return "\"" + "User email already exists" + "\"";
                            } else {
                                return 'true';
                            }
                        }
                    },
                },
                "business_type[]": {
                    required: true,
                },
                user_role: {
                    required: true,
                },
                category: {
                    required: true,
                },
                order: {
                    required: true,
                },
                member: {
                    required: true,
                },
                "manufacturer[]": {
                    required: true,
                }

            },
            messages: {
                user_name: {
                    required: "Please enter User Name",
                    maxlength: "You have reached your maximum limit of characters allowed",
                },
                mobile: {
                    required: "Please enter Contact Number",
                    number: "Please enter valid Contact Number",
                    maxlength: "You have reached your maximum limit of numbers allowed",
                    minlength: "Please enter atleast 10 phone number",
                },
                department: {
                    required: "Please select Department",
                },
                email: {
                    required: "Please enter Email",
                    email: "Please enter valid Email",
                    maxlength: "You have reached your maximum limit of characters allowed",
                },
                business_type: {
                    required: "Please select Business Type",
                },
                manufacturer: {
                    required: "Please select Business Type",
                },
                user_role: {
                    required: "Please select User Role",
                },
                category: {
                    required: "Please Select Funding Category",
                },
                order: {
                    required: "Please Select Redemption Limit Per Qty Order",
                },
                member: {
                    required: "Please Select Redemption Limit Per Qty Member",
                }
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');

            },
            submitHandler: function(form) {
                $("#user_add").prop('disabled', true); //disable 
                form.submit();
            }
        });
    });
</script>
@endpush
@stop