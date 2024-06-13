<?php
include "./config.php";

$force_login_redirect = false;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
        <link rel="stylesheet" href="./styles/main.css">
    </head>
    <body class="truebody">
        <div class="navbar">
            <?php
            if (in_array($username, $manifest_config["auth"]["admins"]) == true) { // Check to see if this user is authorized to configure this instance.
                echo '<a class="button" href="./configure.php">Configure</a>';
            }
            ?>
            <a class="button" href="./settings.php">Settings</a>
        </div>
        <main class="mainbody centeredsection">
            <h1><?php echo htmlspecialchars($manifest_config["product_name"]); ?></h1>
            <p>This tool allows administrators to manage license plate lists for Predator instances. It should be noted that this tool only affects Predator instances that have this server in their remote alert list sources.</p>
            <hr>
            <?php
            if (strlen($username) > 0) { // Check to see if the user is currently signed in.
                echo '<div class="centered">';
                echo '    <a class="button" href="./managelists.php">Manage Lists</a>';
                echo '</div>';
            } else { // Otherwise, the user is not currently signed in.
                echo "<p><i>You are not currently signed in. To create and manage license plate lists, please log in.</i></p><br>";
                echo "<p><a href='https://v0lttech.com/predator.php'>Predator ALPR</a> supports hot-lists and ignore-lists, which are a powerful way to control how the system handles certain license plates when they are detected. Predator supports the ability to load these lists from remote sources, such that the latest lists are automatically fetched from a remote server when the system starts. This also allows a single list source to be automatically distributed to multiple Predator clients.</p>";
                echo "<p>Manifest is standalone service to manage and deploy Predator hot-lists and ignore-lists using an intuitive interface. While V0LT maintains an <a href='https://v0lttech.com/predator/manifest/'>official instance</a>, anyone can host their own copy of Manifest using its source code. To start creating your own Predator license plate lists, simply login and return to this page!</p>";
            }
            ?>
        </main>
    </body>
</html>
