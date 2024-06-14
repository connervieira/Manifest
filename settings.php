<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";


function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string = $random_string . $characters[random_int(0, strlen($characters) - 1)];
    }
    return $random_string;
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Settings</title>
        <script async defer data-domain="v0lttech.com" src="/js/plausible.js"></script>
        
        <link rel="stylesheet" href="./fonts/lato/latofonts.css">
        <link rel="stylesheet" href="./styles/main.css">
    </head>
    <body class="truebody">
        <main class="mainbody centeredsection">
            <div class="navbar">
                <a class="button" href="index.php">Back</a>
            </div>
            <h1><?php echo htmlspecialchars($manifest_config["product_name"]); ?></h1>
            <h2>Configure</h2>
            <?php
            if (in_array($username, array_keys($manifest_config["users"])) == false) {  // Check to see if this user's settings need to be initialized.
                $manifest_config["users"][$username] = array();
                $manifest_config["users"][$username]["settings"] = array();
                $manifest_config["users"][$username]["settings"]["defaults"]["lists"]["access_key"] = "";
                $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["author"] = $username;
                $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["year"] = 0;
                $manifest_config["users"][$username]["permissions"] = array();
                $manifest_config["users"][$username]["permissions"]["list_capacity"]["hot"] = -1;
                $manifest_config["users"][$username]["permissions"]["list_capacity"]["ignore"] = -1;
                $manifest_config["users"][$username]["permissions"]["list_count"]["hot"] = -1;
                $manifest_config["users"][$username]["permissions"]["list_count"]["ignore"] = -1;
                file_put_contents($manifest_config_database_name, json_encode($manifest_config, (JSON_UNESCAPED_SLASHES)));
            }
            if ($_POST["submit"] == "Submit") {
                $valid = true; // This will be set to false if an invalid configuration value is encountered.

                $default_access_key = $_POST["default>access_key"];
                $default_author = $_POST["default>author"];
                $default_vehicle_year = intval($_POST["default>vehicle_year"]);

                if (strlen($default_access_key) > 100) {
                    echo "<p>The specified default access key is excessively long.</p>";
                    $valid = false;
                } else if (preg_replace("/[^A-Za-z0-9]/", '', $default_access_key) !== $default_access_key) {
                    echo "<p>The specified default access key contains special characters.</p>";
                    $valid = false;
                }

                if (strlen($default_author) > 100) {
                    echo "<p>The specified default author is excessively long.</p>";
                    $valid = false;
                } else if (preg_replace("/[^A-Za-z0-9 \-_\']/", '', $default_author) !== $default_author) {
                    echo "<p>The specified author contains disallowed characters.</p>";
                    $valid = false;
                }
                if ($default_vehicle_year > 3000 or $default_vehicle_year < 0) {
                    echo "<p>The specified vehicle year is outside of the expected range.</p>";
                    $valid = false;
                }

                $manifest_config["users"][$username]["settings"]["defaults"]["lists"]["access_key"] = $default_access_key;
                $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["author"] = $default_author;
                $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["vehicle_year"] = $default_vehicle_year;


                if ($valid == true) {
                    file_put_contents($manifest_config_database_name, json_encode($manifest_config, (JSON_UNESCAPED_SLASHES)));
                    echo "<p>Successfully updated configuration.</p>";
                } else {
                    echo "<p>The configuration was not updated.</p>";
                }
            }
            ?>
            <hr>
            <div class="basicform">
                <form method="POST">
                    <h3>Defaults</h3>
                    <label for="default>access_key">Access Key:</label> <input type="text" name="default>access_key" id="default>access_key" placeholder="<?php echo generate_random_string(); ?>" value="<?php echo $manifest_config["users"][$username]["settings"]["defaults"]["lists"]["access_key"]; ?>" autocomplete="new-password"><br>
                    <label for="default>author">Author:</label> <input type="text" name="default>author" id="default>author" placeholder="Author Name" value="<?php echo $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["author"]; ?>"><br>
                    <label for="default>vehicle_year">Vehicle Year:</label> <input type="text" name="default>vehicle_year" id="default>vehicle_year" placeholder="<?php echo rand(1990, 2025); ?>" value="<?php echo $manifest_config["users"][$username]["settings"]["defaults"]["entries"]["vehicle_year"]; ?>"><br>

                    <br><input class="button" type="submit" name="submit" id="submit" value="Submit">
                </form>
            </div>
        </main>
    </body>
</html>
