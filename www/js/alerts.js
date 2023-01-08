function responseErrorAlert(message) {
    $("#customErrorPopup").empty();
    $("#customErrorPopup").append(message);

    $("#customErrorPopup").fadeIn(1000);
    $("#customErrorPopup").delay(4000).fadeOut(100);
}

function responseSuccessAlert(message) {
    $("#customErrorPopup").hide();
    $("#customSuccessPopupJs").empty();
    $("#customSuccessPopupJs").append(message);
    $("#customSuccessPopupJs").fadeIn(1000);
    $("#customSuccessPopupJs").delay(4000).fadeOut(100);
}
