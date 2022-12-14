<?php include_once "./inc/notifications.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <link rel="stylesheet" href="./css_external.css">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="js/alerts.js"></script>
    <script src="js/room.js"></script>
    <title>Room</title>
</head>

<body>
    <?php
    session_start();
    if (isset($_SESSION['user'])) {
        $json = json_decode($_SESSION['user']);
        $name =  (string)$json->name;
    }
    if (isset($_COOKIE["room"])) {
        $room = $_COOKIE["room"];
    }
    ?>

    <div class="container-fluid bg-image">
        <div class="row">
            <input type="hidden" id="awesome" value="<?php if (isset($_COOKIE["room"]) && !is_null($_COOKIE["room"])) echo $_COOKIE["room"]; ?>">
            <div class="col-12 text-center text-white d-flex flex-row justify-content-between p-3 infoHeading">
                <h3 id="roomTitle"></h3>

                <?php
                if (isset($_SESSION['user'])) {
                    echo '<h5>Username:  <span id="playerUsername">' . $name . '</span></h5>';
                }
                ?>
            </div>
        </div>
        <!-- emfanisi xristwn -->
        <div class="row" id="gameTopRow">
            <div class="col-12">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <div>
                        <img src="userImg/user_icon_red.png" class="userIcon m-auto" alt="">
                        <p id="userThree" class="text-light"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="height:60vh" id="gameCenterRow">
            <div class="col-2 d-flex justify-content-center align-items-center">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <div>
                        <img src="userImg/user_icon_yellow.png" class="userIcon m-auto" alt="">
                        <p id="userFour" class="text-light otherUsersDispl"></p>
                    </div>
                </div>
            </div>
            <div class="col-8 d-flex flex-column align-items-center text-center">
                <div class="m-auto d-flex" id="gameDeck">
                    <div class="playedCardsGame m-auto text-center"></div>
                </div>
                <h3 id="bluffPlayedByHeader" class="text-light"></h3>
                <h3 id="bluffInfoHeader" class="text-light"></h3>
            </div>
            <div class="col-2 d-flex justify-content-center align-items-center">
                <div class="m-auto d-flex flex-column text-center" style="width:fit-content;">
                    <div>
                        <img src="userImg/user_icon_white.png" class="userIcon m-auto" alt="">
                        <p id="userTwo" class="text-light otherUsersDispl"></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="actionRow">
            <div class="col-12 bg-dark text-center" style="height: 25vh;">
                <div id="myCardsDisplay" class="d-flex justify-content-center">
                </div>
                <button type="button" id="chooseYourBluffBtn" class="btn btn-warning mt-4">Play your cards</button>
                <button type="button" id="callBluffBtn" class="btn btn-danger mt-4">Call bluff</button>
                <button type="button" id="passBtn" class="btn btn-info mt-4">Pass</button>
            </div>
        </div>
        <!-- -->
    </div>
    <!-- Modal -->
    <div class="modal fade modal-lg" id="chooseYourBluff" tabindex="-1" aria-labelledby="chooseYourBluffLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="chooseYourBluffLabel">Choose your Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">A</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">2</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">3</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">4</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">5</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">6</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">7</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">8</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">9</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">10</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">J</button>
                    <button type="button" class="btn btn-secondary mt-1 ml-1 bluffValueBtn">Q</button>
                    <button type="button" class="btn btn-info mt-1 ml-1 bluffValueBtn">K</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Winner Modal -->
    <div class="modal fade modal-lg" id="showWinnerModal" tabindex="-1" aria-labelledby="showWinnerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showWinnerModalLabel">Game has ended</h5>
                </div>
                <div class="modal-body text-center">
                    <h3 id="announceWinnerHeader"></h3>
                    <button class="btn btn-success mt-2" id="goHomeAfterGame">Return Home</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>