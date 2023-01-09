var me = { token: null };

$(function () {
    $("#submitUsername").click(log_in_to_game);
    restoreRooms();
});

function log_in_to_game() {
    if ($("#pickedUsername").val() == "") {
        alert("You have to set a username");
        return;
    }

    $.ajax({
        url: "bluff.php/players/",
        method: "POST",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify({
            username: $("#pickedUsername").val(),
        }),
        success: function (response) {
            me = response /* [0] */;
            responseSuccessAlert("You have logged in successfully");
            $("#usernameModal").toggle();
            location.href = "http://127.0.0.1/ADISE22_theItGuys/www/";
        },
        error: responseErrorAlert("Something went wrong.."),
    });
}

function has_user_logged_in() {
    if (me.token === null) $("#usernameModal").toggle();
}

function restoreRooms() {
    $.ajax({
        url: "bluff.php/players/handleRoom",
        method: "POST",
        dataType: "json",
        contentType: "application/json",
        error: function (response) {
            responseErrorAlert(response);
        },
    });
}
