var roomID = $("#awesome").val();
//
var room = { name: null, users_online: null, roomStatus: null };
var otherUsers = [];
var myCards;
var bluffCards = [];
var resetPasses = false;

$(function () {
    //fill room object
    get_room_info();

    roomID = $("#awesome").val();

    if (room.users_online > 1) {
        getOtherUsersInRoom();
    }

    if (room.roomStatus === "full") {
        if (isOwner()) startGame();
        else getMyCards();

        var gameStatus = setInterval(getGameInfo, 4000);
    }
    $(".deckCard").on("click", selectCards);
    $("#chooseYourBluffBtn").on("click", openBluffModal);
    $(".bluffValueBtn").on("click", submitYourBluff);
    $("#callBluffBtn").on("click", callBluff);
    $("#passBtn").on("click", passAction);
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
    getMyCards();
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

function openBluffModal() {
    if (bluffCards.length === 0) alert("you must select at least one card");
    else $("#chooseYourBluff").modal("toggle");
}

function submitYourBluff() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/playYourBluff",
        type: "POST",
        data: {
            valueOfCardsPlayed: $(this).text(),
            cardsPlayed: bluffCards,
        },
        success: function (response) {
            console.log(response);
            for (var i = 0; i < bluffCards.length; ++i) {
                $("#" + bluffCards[i]).remove();
            }
            $("#chooseYourBluff").modal("toggle");
            resetGamePasses();
            addCartsToBank();
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function getGameInfo() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getGameInfo",
        type: "GET",
        success: function (response) {
            var obj = JSON.parse(response);
            $("#bluffPlayedByHeader").text(obj.played_by + "  played...:");
            $("#bluffInfoHeader").text(obj.num_of_cards_played + "  x  " + obj.value_of_cards_played);
            if (obj.playing_now === $("#playerUsername").text()) {
                $("#chooseYourBluffBtn").prop("disabled", false);
                $("#callBluffBtn").prop("disabled", false);
                if (obj.passes < 3) $("#passBtn").prop("disabled", false);
                else {
                    $("#passBtn").prop("disabled", true);
                    resetPasses = true;
                }
            } else {
                $("#chooseYourBluffBtn").prop("disabled", true);
                $("#callBluffBtn").prop("disabled", true);
                $("#passBtn").prop("disabled", true);
            }

            console.log(obj);
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function callBluff() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/callBluff",
        type: "GET",
        success: function (response) {
            $("#callBluffBtn").prop("disabled", true);
            var obj = JSON.parse(response);
            $(".playedCardsGame").css("display", "none");
            $.each(obj.cards, function (index, value) {
                if (value.card_style == "♦" || value.card_style == "♥") $("#gameDeck").append('<div class="deckCard red showBluffCardsOnCall" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
                else $("#gameDeck").append('<div class="deckCard black showBluffCardsOnCall" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
            });
            collectBluffCards(obj.playerForBank);
            resetGamePasses();
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function collectBluffCards(playerToCollect) {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/getCalledBluffCards",
        type: "POST",
        data: {
            userToCollectBank: playerToCollect,
        },
        success: function (response) {
            $(".showBluffCardsOnCall").delay(3000).remove();
            $(".playedCardsGame").css("display", "block");
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function passAction() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/passOnBluff",
        type: "GET",
        success: function (response) {
            console.log(response);
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function resetGamePasses() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/resetPasses",
        type: "POST",
        success: function (response) {
            resetPasses = false;
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}

function addCartsToBank() {
    $.ajax({
        url: "http://127.0.0.1/ADISE22_theItGuys/www/bluff.php/game/addCardsToBank",
        type: "POST",
        success: function (response) {
            //take bank num
        },
        error: function (response) {
            console.log(response.error);
        },
    });
}
