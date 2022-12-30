var me = { token: null };

$(function () {
    $("#submitUsername").click(log_in_to_game);
});

function log_in_to_game() {
    if ($("#pickedUsername").val() == "") {
        alert("You have to set a username");
        return;
    }

    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/players/",
        method: "POST",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify({
            username: $("#pickedUsername").val(),
        }),
        success: function (response) {
            me = response[0];
            login_result;
            $("#usernameModal").toggle();
        },
        error: login_error,
    });
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
    if ($("#errorMessage").length == 0) {
        $("body").append('<div id="errorMessage"></div>');
        $("#errorMessage").css({
            "background-color": "red",
            color: "white",
            position: "absolute",
            top: "10%",
        });
        $("#errorMessage").empty();
        $("#errorMessage").append("<p>Username field can not be empty</p>");
    }
}

function has_user_logged_in() {
    if (me.token === null) $("#usernameModal").toggle();
}
