function displayRegisterForm() 
{
    console.log("displaying form");
    $("#login_form").hide();
    $("#register_form").show();
    $("#registerButton").hide();
    $("#loginButton").show();

}

function displayLoginForm() {
    $("#register_form").hide();
    $("#login_form").show();
    $("#loginButton").hide();
    $("#registerButton").show();

}
