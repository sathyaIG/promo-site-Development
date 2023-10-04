$(function () {
    $.validator.addMethod("greaterThan",
        function (value, element, params) {

            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val()) ||
                (Number(value) > Number($(params).val()));
        }, 'Must be greater than From Date.');

    $.validator.addMethod("pwcheck", function (value) {
        return /^[A-Za-z0-9\d=!\-@._*]*$/.test(value) // consists of only these
            &&
            /[a-z]/.test(value) // has a lowercase letter
            &&
            /[A-Z]/.test(value) // has a lowercase letter
            &&
            /\d/.test(value) // has a digit
    });

    $.validator.addMethod("passcheck", function (value) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#$@!%&*?])[A-Za-z\d#$@!%&*?]{12,30}$/.test(
            value) // consists of only these

    });

    $.validator.addMethod('filesize', function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    });

    $.validator.addMethod("namewithspace", function (value) {
        return /^[a-zA-Z]+$/.test(value) // consists of only these
    }, 'Please enter Valid Input');

    $.validator.addMethod("nameandspace", function (value) {
        return /^[a-zA-Z\s]+$/.test(value) // consists of only these
    }, 'Please enter Valid Input');

    $.validator.addMethod("alphanumericwithspace", function (value) {
        return /^[a-zA-Z0-9,\-\s.]+$/.test(value) // consists of only these
    }, 'Please enter valid input');

    $.validator.addMethod("usnumber", function (value) {
        return /^[\([0-9]{3}\) |[0-9]{3}-[0-9]{3}-[0-9]{4}]+$/.test(value) // consists of only these
    }, 'Please enter valid input');

    $.validator.addMethod("us_number", function (value) {
        return /^[1?(\d{3})(\d{3})(\d{4})$]+$/.test(value) // consists of only these
    }, 'Please enter valid US  input');

    $.validator.addMethod("validation_number", function (value) {
        return /^[0-9]+$/.test(value) // consists of only these
    }, 'Please enter valid US  input');

    $.validator.addMethod("noSpace", function (value) {
        return /^[^\s]+$/.test(value) // consists of only these
    }, 'Please enter valid input');

    // jQuery.validator.addMethod("allRequired", function(value, elem){
    //     // Use the name to get all the inputs and verify them
    //     var name = elem.name;
    //     return  $('input[name="'+name+'"]').map(function(i,obj){return $(obj).val();}).get().every(function(v){ return v; });
    // });

    $.validator.addMethod("alphaOnly", function (value, element) {
        return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
    }, "Please enter letters only.");

    $.validator.addMethod("noSpaceAtEdges", function (value, element) {
        return this.optional(element) || /^\S(.*\S)?$/.test(value);
    }, "No spaces at the beginning or end of the string.");
   

    $.validator.addMethod("noConsecutiveSpaces", function(value, element) {
        return this.optional(element) || !/\s{2}/.test(value);
      }, "No consecutive spaces allowed.");

      $.validator.addMethod("emailwithdot", function (value) {
        return /^[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(value) // consists of only these
    }, 'Please enter valid input');

    $.validator.addMethod("phoneno", function (value) {
        return /^[+]*[(]{0,1}[0-9]{1,3}[)]{0,1}[-\s\./0-9]*$/.test(value) // consists of only these
    }, 'Please enter valid input');
    $.validator.addMethod("email", function (value) {
        return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
        .test(value) // consists of only these
    }, 'Please enter valid input');

    $.validator.addMethod("mytst", function (value, element) {
        var flag = true;

        $("[name^=high]").each(function (i, j) {
            $(this).removeClass('is-invalid');
            if ($.trim($(this).val()) == '') {
                flag = false;
                $(this).addClass('is-invalid');
                console.log($(this).parent('div').closest('span').html());
                $(this).parent('div').closest('span').html('This field is required.');
            }
        });


        return flag;


    }, "");

});
