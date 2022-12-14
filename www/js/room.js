var roomID = $("#awesome").val();
//
var room = { name: null, users_online: null, roomStatus: null };
var otherUsers = [];
var myCards;
var bluffCards = [];
var resetPasses = false;
var isWinnerAnnounced = false;
var playSpecificCard = null;
var getStatus = null;

$(function () {
    get_room_info();
    getStatus = setInterval(getGameStatus, 2000);
    roomID = $("#awesome").val();
    if (room.users_online > 1) {
        getOtherUsersInRoom();
    }

    if (room.roomStatus === "full") {
        $("#actionRow").css("display", "block");
        if (isOwner()) startGame();
        else getMyCards();
        getGameInfo();
        var gameStatus = setInterval(getGameInfo, 4000);
        clearInterval(getStatus);
    }
    $(".deckCard").on("click", selectCards);
    $("#chooseYourBluffBtn").on("click", openBluffModal);
    $(".bluffValueBtn").on("click", submitYourBluff);
    $("#callBluffBtn").on("click", callBluff);
    $("#passBtn").on("click", passAction);
    $("#goHomeAfterGame").on("click", returnHomeAfterGameIsFinished);
});

function get_room_info() {
    $.ajax({
        url: "bluff.php/game/getInfo",
        method: "POST",
        async: false,
        data: {
            room_id: $("#awesome").val(),
        },
        success: function (response) {
            var obj = jQuery.parseJSON(response);
            if (obj.hasOwnProperty("errormesg")) {
                responseErrorAlert(obj.errormesg);
                return;
            }
            $("#roomTitle").text(obj.name);
            room.name = obj.name;
            room.users_online = obj.users_online;
            room.roomStatus = obj.status;
            if (obj.users_online < 4) {
                $("#actionRow").css("display", "none");
            }
        },
    });
}

function getOtherUsersInRoom() {
    $.ajax({
        url: "bluff.php/game/getRoomPlayers",
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
            responseErrorAlert(response.error);
        },
    });
}

function startGameBase() {
    $.ajax({
        url: "bluff.php/game/start",
        type: "GET",
        async: false,
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function isOwner() {
    var isOwner = false;
    $.ajax({
        url: "bluff.php/game/getGameOwner",
        type: "GET",
        async: false,
        success: function (response) {
            if (response === "true") isOwner = true;
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });

    return isOwner;
}

function startGame() {
    startGameBase();
    getMyCards();
}

function getMyCards() {
    $.ajax({
        url: "bluff.php/game/getMyCards",
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
        if (value.card_style == "???" || value.card_style == "???") $("#myCardsDisplay").append('<div class="deckCard red" id="' + value.id + '" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
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
    if (bluffCards.length === 0) responseErrorAlert("you must select at least one card");
    else $("#chooseYourBluff").modal("toggle");
}

function submitYourBluff() {
    if (playSpecificCard != null) {
        if ($(this).text() !== playSpecificCard) {
            responseErrorAlert("You can only bluff " + playSpecificCard);
            return;
        }
    }
    $.ajax({
        url: "bluff.php/game/playYourBluff",
        type: "POST",
        data: {
            valueOfCardsPlayed: $(this).text(),
            cardsPlayed: bluffCards,
        },
        success: function (response) {
            for (var i = 0; i < bluffCards.length; ++i) {
                $("#" + bluffCards[i]).remove();
            }
            $("#chooseYourBluff").modal("toggle");
            resetGamePasses();
            bluffCards = [];
            getGameInfo();
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function getGameInfo() {
    seeIfYouWin();
    $.ajax({
        url: "bluff.php/game/getGameInfo",
        type: "GET",
        success: function (response) {
            if (response === "") return;
            var obj = JSON.parse(response);
            if (obj.hasOwnProperty("errormesg")) {
                responseErrorAlert(obj.errormesg);
                return;
            }
            if (obj.first_winner_id != null) {
                if (isWinnerAnnounced) return;
                showWinner();
                return;
            }
            if (obj.played_by != null && obj.num_of_cards_played != null) {
                $("#bluffPlayedByHeader").text(obj.played_by + "  played...:");
                $("#bluffInfoHeader").text(obj.num_of_cards_played + "  x  " + obj.value_of_cards_played);
            }
            if (obj.playing_now === $("#playerUsername").text()) {
                $("#chooseYourBluffBtn").prop("disabled", false);
                if (obj.passes < 3) $("#passBtn").prop("disabled", false);
                else {
                    $("#passBtn").prop("disabled", true);
                    resetPasses = true;
                }
                if (obj.bluffed === 1 /* || obj.hasOwnProperty("bluffed") */) {
                    $("#callBluffBtn").prop("disabled", true);
                    $("#passBtn").prop("disabled", true);
                } else {
                    $("#callBluffBtn").prop("disabled", false);
                    $("#passBtn").prop("disabled", false);
                }
            } else {
                $("#chooseYourBluffBtn").prop("disabled", true);
                $("#callBluffBtn").prop("disabled", true);
                $("#passBtn").prop("disabled", true);
            }
            showCurrentUserPlaying(obj.playing_now);

            if (obj.round_moves > 1 && obj.round_moves <= 4) playSpecificCard = obj.value_of_cards_played;

            if (obj.round_moves == 1) playSpecificCard = null;
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function callBluff() {
    $.ajax({
        url: "bluff.php/game/callBluff",
        type: "GET",
        success: function (response) {
            $("#callBluffBtn").prop("disabled", true);
            var obj = JSON.parse(response);
            if (obj.hasOwnProperty("errormesg")) {
                responseErrorAlert(obj.errormesg);
                return;
            }
            $(".playedCardsGame").css("display", "none");
            $.each(obj.cards, function (index, value) {
                if (value.card_style == "???" || value.card_style == "???") $("#gameDeck").append('<div class="deckCard red showBluffCardsOnCall" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
                else $("#gameDeck").append('<div class="deckCard black showBluffCardsOnCall" data-value="' + value.card_number + value.card_style + '">' + value.card_style + "</div>");
            });
            collectBluffCards(obj.playerForBank);
            resetGamePasses();
            $("#myCardsDisplay").empty();
            getMyCards();
            bluffCards = [];
            getGameInfo();
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function collectBluffCards(playerToCollect) {
    $.ajax({
        url: "bluff.php/game/getCalledBluffCards",
        type: "POST",
        data: {
            userToCollectBank: playerToCollect,
        },
        success: function (response) {
            $(".showBluffCardsOnCall").delay(3000).remove();
            $(".playedCardsGame").css("display", "block");
            $("#myCardsDisplay").empty();
            getMyCards();
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function passAction() {
    $.ajax({
        url: "bluff.php/game/passOnBluff",
        type: "GET",
        success: function () {
            getGameInfo();
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function resetGamePasses() {
    $.ajax({
        url: "bluff.php/game/resetPasses",
        type: "POST",
        success: function () {
            resetPasses = false;
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function addCartsToBank() {
    $.ajax({
        url: "bluff.php/game/addCardsToBank",
        type: "POST",
        success: function (response) {
            //take bank num
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function showCurrentUserPlaying(name) {
    if ($("div").hasClass("currentPlayingUserDisplay")) $("div").removeClass("currentPlayingUserDisplay");
    if ($("#myCardsDisplay").hasClass("loggedInUsersTurn")) $("#myCardsDisplay").removeClass("loggedInUsersTurn");

    if ($("#userTwo").text() === name) $("#userTwo").parent().addClass("currentPlayingUserDisplay");
    if ($("#userThree").text() === name) $("#userThree").parent().addClass("currentPlayingUserDisplay");
    if ($("#userFour").text() === name) $("#userFour").parent().addClass("currentPlayingUserDisplay");
    if ($("#playerUsername").text() === name) $("#myCardsDisplay").addClass("loggedInUsersTurn");
}

function showWinner() {
    $.ajax({
        url: "bluff.php/game/getWinner",
        type: "POST",
        success: function (response) {
            var winnerObj = JSON.parse(response);
            if (winnerObj.hasOwnProperty("errormesg")) {
                responseErrorAlert(obj.errormesg);
                return;
            }
            isWinnerAnnounced = true;
            $("#actionRow").css("display", "none");
            $("#gameCenterRow").css("display", "none");
            $("#gameTopRow").css("display", "none");
            $("#showWinnerModal").modal({ backdrop: "static", keyboard: false });
            $("#showWinnerModal").modal("toggle");
            $("#announceWinnerHeader").text("???? Congratulations " + winnerObj.winner + " you won the game!! ????????");
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function returnHomeAfterGameIsFinished() {
    $.ajax({
        url: "bluff.php/game/restoreRoom",
        type: "POST",
        success: function () {
            location.href = "http://127.0.0.1/ADISE22_theItGuys/www/";
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function seeIfYouWin() {
    $.ajax({
        url: "bluff.php/game/checkIfYouWin",
        type: "POST",
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}

function getGameStatus() {
    $.ajax({
        url: "bluff.php/game/getGameStatus",
        type: "POST",
        data: {
            id: $("#awesome").val(),
        },
        success: function (response) {
            room.roomStatus = JSON.parse(response).status;
            if (room.roomStatus === "full") {
                $("#actionRow").css("display", "block");
                if (isOwner()) startGame();
                else getMyCards();
                getGameInfo();
                var gameStatus = setInterval(getGameInfo, 4000);
                clearInterval(getStatus);
            }
        },
        error: function (response) {
            responseErrorAlert(response.error);
        },
    });
}
