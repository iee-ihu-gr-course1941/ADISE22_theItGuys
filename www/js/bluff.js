var me = { token: null };

function log_in_to_game() {
    if ($("#puckedUsername").val() == "") {
        alert("You have to set a username");
        return;
    }
}

function login_result() {
    if ($("#successMessage").length == 0) {
        $("body").append('<div id="successMessage"></div>');
        $("#successMessage").css({
            "background-color": "green",
            color: "white",
            position: "absolute",
            top: "10%",
        });
        $("#successMessage").empty();
        $("#successMessage").append("<p>You have logged in successfully</p>");
    }
}

function login_error() {
    if ($("#successMessage").length == 0) {
        $("body").append('<div id="successMessage"></div>');
        $("#successMessage").css({
            "background-color": "red",
            color: "white",
            position: "absolute",
            top: "10%",
        });
        $("#successMessage").empty();
        $("#successMessage").append("<p>You have logged in successfully</p>");
    }
}

function has_user_logged_in() {
    console.log(me.token);
    if (me.token === null) {
        console.log("fucl");
        $("#usernameModal").toggle();
    } else console.log(me);
}
