$(function() {
    $.validator.addMethod("requireSymbol", function(value) {
        return /[ !"#$%&'()*+,\-.:;<=>?@\[\]\\^_`{|}~]/.test(value);
    }, 'Please use at least one special character.');

    $.validator.addMethod("requireDigit", function(value) {
        return /[0-9]/.test(value);
    }, 'Please use at least one digit.');

    $.validator.addMethod("requireUppercase", function(value) {
        return /[A-Z]/.test(value);
    }, 'Please use at least one uppercase character.');

    $.validator.addMethod("requireLowercase", function(value) {
        return /[a-z]/.test(value);
    }, 'Please use at least one lowercase character.');

    $('#signupForm').validate({
        rules: {
            password: {
                requireSymbol: true,
                requireDigit: true,
                requireUppercase: true,
                requireLowercase: true,
            }
        },
        errorPlacement: function(error, element) {
            error.insertBefore(element);
        }
    });

});