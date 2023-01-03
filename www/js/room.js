var roomID = $("#awesome").val();
//
var room = { name: null, users_online: null, roomStatus: null };
var otherUsers = [];
var deckOfCardsArray = [];

$(function () {
    deckOfCardsArray = shuffle(deckOfCards());

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
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

//cards stuff
function deckOfCards() {
    let suits = ["♦", "♥", "♣", "♠"];
    let values = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];

    let deckOfCardsArray = [];
    for (var i = 0; i < suits.length; i++) for (var j = 0; j < values.length; j++) deckOfCardsArray.push(values[j] + suits[i]);

    return deckOfCardsArray;
}

//shuffle array of cards
function shuffle(array) {
    let currentIndex = array.length,
        randomIndex;

    while (currentIndex != 0) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;

        [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
    }

    return array;
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
    console.log(deckOfCardsArray);
    //update everyones cards
}

function getMyCards() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getMyCards",
        method: "GET",
        async: false,
        success: function (response) {
            console.log(JSON.parse(response));
        },
    });
}
