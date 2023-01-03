<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <style link rel="stylesheet" href="css_external.css"></style>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="js/room.js"></script>
    <!--  -->
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
            <div class="col-12 text-center text-white d-flex flex-row justify-content-between">
                <!-- bg-text -->
                <h3 id="roomTitle"></h3>

                <?php
                if (isset($_SESSION['user'])) {
                    echo '<h5>Username:  ' . $name . '</h5>';
                }
                ?>
            </div>
        </div>
    </div>

    <style>
        .bg-image {
            /* The image used */
            background-image: url("room.jpg");
            height: 100vh;
            background-size: cover;
        }

        .bg-text {
            background-color: rgba(0, 0, 0, 0.038);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.086);
            /* Black w/opacity/see-through */
            color: white;
            font-weight: bold;
            border: 3px solid #f1f1f1;
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            width: 80%;
            padding: 10px;
            text-align: center;
        }
    </style>



</body>

</html>