var roomID = $("#awesome").val();
//
var room = { name: null, users_online: null, roomStatus: null };
var otherUsers = [];
var myCards;
var bluffCards = [];

$(function () {
    //fill room object
    get_room_info();

    //room id
    roomID = $("#awesome").val();

    if (room.users_online > 1) {
        getOtherUsersInRoom();
        //show their names
        console.log(otherUsers);
    }

    if (room.roomStatus === "full")
        if (isOwner()) startGame();
        else getMyCards();

    $(".deckCard").on("click", selectCards);
});

function get_room_info() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getInfo",
        method: "POST",
        async: false,
        data: {
            room_id: $("#awesome").val(),
        },
        success: function (response) {
            var obj = jQuery.parseJSON(response);

            $("#roomTitle").append(obj.name);
            room.name = obj.name;
            room.users_online = obj.users_online;
            room.roomStatus = obj.status;
        },
    });
}

function getOtherUsersInRoom() {
    //get other users (usernames) in room
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getRoomPlayers",
        type: "POST",
        async: false,
        data: {
            roomId: roomID,
        },
        success: function (response) {
            otherUsers = JSON.parse(response);
            $("#userTwo").text(otherUsers[1].name);
            $("#userThree").text(otherUsers[2].name);
            $("#userFour").text(otherUsers[3].name);
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function startGameBase() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/start",
        type: "GET",
        async: false,
        success: function (response) {
            console.log(response);
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function isOwner() {
    var isOwner = false;
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getGameOwner",
        type: "GET",
        async: false,
        success: function (response) {
            if (response === "true") isOwner = true;
            console.log(response);
        },
        error: function (response) {
            console.log(response.error);
        },
    });

    return isOwner;
}

function startGame() {
    startGameBase();
    //update everyones cards
}

function getMyCards() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getMyCards",
        method: "GET",
        async: false,
        success: function (response) {
            myCards = JSON.parse(response);
            showMyCards(myCards);
        },
    });
}

function showMyCards(myCards) {
    $("#myCardsDisplay").empty();
    $.each(myCards, function (index, value) {
        if (value.card_style == "♦" || value.card_style == "♥") $("#myCardsDisplay").append('<div class="deckCard red" id="' + value.id + '" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
        else $("#myCardsDisplay").append('<div class="deckCard black" id="' + value.id + '" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
    });
}

function selectCards() {
    if ($(this).hasClass("selectedCards")) {
        bluffCards.splice($.inArray($(this).attr("id"), bluffCards), 1);
        $(this).removeClass("selectedCards");
        return;
    }
    if (bluffCards.length < 4) {
        $(this).addClass("selectedCards");
        bluffCards.push($(this).attr("id"));
    }
}
