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
                $manifest_config["users"][$username]["settings"]["access_key"] = "";
                $manifest_config["users"][$username]["permissions"] = array();
                $manifest_config["users"][$username]["permissions"]["list_capacity_hot"] = -1;
                $manifest_config["users"][$username]["permissions"]["list_capacity_ignore"] = -1;
            }
            if ($_POST["submit"] == "Submit") {
                $valid = true; // This will be set to false if an invalid configuration value is encountered.

                $manifest_config["users"][$username]["settings"]["access_key"] = $_POST["access_key"];
                if (strlen($_POST["access_key"]) > 100) {
                    echo "<p>The specified access key is excessively long.</p>";
                    $valid = false;
                } else if (preg_replace("/[^A-Za-z0-9]/", '', $_POST["access_key"]) !== $_POST["access_key"]) {
                    echo "<p>The specified access key contains special characters.</p>";
                    $valid = false;
                }


                if ($valid == true) {
                    file_put_contents($manifest_config_database_name, json_encode($manifest_config, (JSON_UNESCAPED_SLASHES)));
                    echo "<p>Successfully updated configuration.</p>";
                } else {
                    echo "<p>The configuration was not updated.</p>";
                }
            }




            $formatted_admins_list = "";
            foreach ($manifest_config["auth"]["admins"] as $user) {
                $formatted_admins_list = $formatted_admins_list . "," . $user;
            }
            $formatted_admins_list = substr($formatted_admins_list, 1); // Remove the first character, since it will always be a comma.

            $formatted_whitelist = "";
            foreach ($manifest_config["auth"]["access"]["whitelist"] as $user) {
                $formatted_whitelist = $formatted_whitelist . "," . $user;
            }
            $formatted_whitelist = substr($formatted_whitelist, 1); // Remove the first character, since it will always be a comma.

            $formatted_blacklist = "";
            foreach ($manifest_config["auth"]["access"]["blacklist"] as $user) {
                $formatted_blacklist = $formatted_blacklist . "," . $user;
            }
            $formatted_blacklist = substr($formatted_blacklist, 1); // Remove the first character, since it will always be a comma.
            ?>
            <hr>
            <div class="basicform">
                <form method="POST">
                    <h3>Authentication</h3>
                    <label for="access_key" title="When set, this key is required to fetch the contents of your license plate lists.">Access Key:</label> <input type="password" name="access_key" id="access_key" placeholder="<?php echo generate_random_string(); ?>" value="<?php echo $manifest_config["users"][$username]["settings"]["access_key"]; ?>" autocomplete="new-password"><br>

                    <br><input class="button" type="submit" name="submit" id="submit" value="Submit">
                </form>
            </div>
        </main>
    </body>
</html>
