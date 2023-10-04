<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BB Royal Honey Purity Result Page</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" integrity="undefined" crossorigin="anonymous">
        <style>
            .container{}
            .container button{background:#d34b01; font-size:19px; font-weight:bolder; color:#000000; width:44%;     margin-left: 29%;}
            .container button:hover{background:#f88a0f;  }
            body {
                background-color: rgb(239, 239, 239);
                max-width: 1170px;
                margin: auto auto;
                padding: 0;
            }
            .card{background:none;}
            input{    height: 46px; border-radius:20px; border:none; width:49% !important; margin-left: 26%;
                      box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);
                      -webkit-box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);
                      -moz-box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);

            }
            input:focus, textarea:focus, select:focus{
                outline: none;
            }
            .image{width:85%;}

            .form-group span{
                text-align: center;
            }

            @media only screen and (max-width: 600px) {

                .image{
                    width:100% !important;
                }
                input{    
                    height: 46px; 
                    border-radius:20px; 
                    border:none; width:60% !important; 
                    margin-left: 20% !important;
                    box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);
                    -webkit-box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);
                    -moz-box-shadow: -2px 5px 39px -11px rgba(138,130,130,0.75);
                }

                .container .para{ 
                    padding:0 0% !important; 
                    font-size:13px !important;
                }
            }

            .container button{
                background:#d34b01; 
                font-size:15px!important;
            }    
        </style>
    </head>

    <body>
        <div class="container" style=" margin:0  auto;">
            <img src="{{asset('public/asset/images/user/product_1/header.jpg')}}" style="width:100%;"/>
            <div class="container" style="background:url({{asset('public/asset/images/user/product_1/background.jpg')}}); background-size:100% 100%;">
                <div class="card card0 border-0">
                    <div class="row d-flex" style=" ">
                        <div class="col-lg-12" style="   ">
                            <div class="">
                                <div class="py-4"> <a href="#"><img src="{{asset('public/asset/images/user/product_1/back-button.png')}}"></a> </div>
                                <p class="text-center para" style="font-size:16px; font-weight:bold; padding: 0 22%;">We take pride in the 100% purity of our BB Royal Honey as , the FSSAI standards. Check the purity certificate of your batch honey by entering the batch number printed on the product packaging as shown below. </p>
                                <div class="row  justify-content-center mt-4 mb-5 border-line"> <img src="{{asset('public/asset/images/user/product_1/webheaderimg.jpg')}}" class="image"> 

                                    <div class="card2 "  >
                                        <form method="post" action="{{url('PurityBatchCheckSubmit')}}" id="puritySearch"  novalidate>
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{$bbcode}}">
                                            <div class="row px-3 mb-4 form-group"> 
                                                <label class="mb-1"></label> 
                                                <input class="" type="text" name="batch_no" placeholder="Batch Number*" style="font-size: 14px;"> </div>
                                            <div class="row px-3 form-group">
                                                <label class="mb-1"></label>
                                                <input type="text" name="name" placeholder="User name" style="font-size: 14px;"> </div>
                                            <br/>

                                            <div class="row mb-3 px-3 text"> 
                                                <button type="submit" class="btn btn-blue text-center">SUBMIT</button> 
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="purityfile" style="display:none;">

            <div id="purityfilelist" style="width:100%;text-align: center;margin: 0 auto;">

            </div>


        </div>
        <div style="margin-top:10px;margin-bottom: 10px">
            <div style="padding-top:10px;padding-bottom: 10px">

            </div>

        </div>


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js"></script> -->
        <script src="{{ asset('public/assets/js/jquery.validate.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js" integrity="sha512-efUTj3HdSPwWJ9gjfGR71X9cvsrthIA78/Fvd/IN+fttQVy7XWkOAXb295j8B3cmm/kFKVxjiNYzKw9IQJHIuQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


        <script>

$("html").on("contextmenu", function (e) {
    //return false;
});

$(document).keydown(function (event) {
    if (event.keyCode == 123) { // Prevent F12
        return false;
    } else if (event.ctrlKey && event.shiftKey && event.keyCode == 73) { // Prevent Ctrl+Shift+I        
        return false;
    }
});

$('#puritySearch').validate({
    rules: {

        batch_no: {
            required: true,
            maxlength: 30,
        },
        name: {
            maxlength: 30,
        },

    },

    messages: {

        batch_no: {
            required: "Please enter Batch no",
            maxlength: "You have reached your maximum limit of characters allowed",
        },
        name: {

            maxlength: "You have reached your maximum limit of characters allowed",
        },

    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    },
    submitHandler: function (form) {

        $('#purityfilelist').html('');

        $.ajax({
            type: "POST",
            url: "{{url('PurityBatchCheckSubmit')}}",
            data: $(form).serialize(), // serializes form input

            success: function (data) {

                $("#purityfile").show();

                $.notify("Please check the purity certificate", "success");

                $('#purityfilelist').append(data.file_list);


                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#purityfile").offset().top
                }, 2000);

            },
            error: function (data) {
                $("#purityfile").hide();
                $.notify("Batch Number not found Please check ", "error");
            }


        });

    }

});

        </script>
    </body>

</html>
