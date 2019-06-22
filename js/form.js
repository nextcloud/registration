// var instead of let/const for better older browsers compatibility
var passwordTextField;
function togglePasswordTextFieldVisibility() {
    if (passwordTextField.attr('type') == "password") {
        passwordTextField.attr('type', 'text');
    } else {
        passwordTextField.attr('type', 'password');
    }
}

$(document).ready(function() {
    passwordTextField = $("#password");
    $("#show").change(togglePasswordTextFieldVisibility);
    $("#showadminpass").change(togglePasswordTextFieldVisibility);
});
