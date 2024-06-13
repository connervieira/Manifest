<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";

if (in_array($username, $manifest_config["auth"]["admins"]) == false) { // Check to see if this user is authorized to be here.
    echo "<p>This tool is for use only by administrators!</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Configuration</title>
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
            if ($_POST["submit"] == "Submit") {
                $valid = true; // This will be set to false if an invalid configuration value is encountered.

                if (file_exists($_POST["auth>provider"]) == true) {
                    $manifest_config["auth"]["provider"] = $_POST["auth>provider"];
                } else {
                    echo "<p>The specified authentication provider does not exist.</p>";
                    $valid = false;
                }

                $manifest_config["auth"]["admins"] = explode(",", $_POST["auth>admins"]);
                foreach ($manifest_config["auth"]["admins"] as $key => $user) {
                    $manifest_config["auth"]["admins"][$key] = trim($user);
                    if ($manifest_config["auth"]["admins"][$key] == "") {
                        unset($manifest_config["auth"]["admins"][$key]);
                    }
                }
                if (strtolower($_POST["auth>access>mode"]) == "whitelist" or strtolower($_POST["auth>access>mode"]) == "blacklist") {
                    $manifest_config["auth"]["access"]["mode"] = strtolower($_POST["auth>access>mode"]);
                } else {
                    echo "<p>The specified access mode is invalid.</p>";
                    $valid = false;
                }
                $manifest_config["auth"]["access"]["whitelist"] = explode(",", $_POST["auth>access>whitelist"]);
                foreach ($manifest_config["auth"]["access"]["whitelist"] as $key => $user) {
                    $manifest_config["auth"]["access"]["whitelist"][$key] = trim($user);
                    if ($manifest_config["auth"]["access"]["whitelist"][$key] == "") {
                        unset($manifest_config["auth"]["access"]["whitelist"][$key]);
                    }
                }
                $manifest_config["auth"]["access"]["blacklist"] = explode(",", $_POST["auth>access>blacklist"]);
                foreach ($manifest_config["auth"]["access"]["blacklist"] as $key => $user) {
                    $manifest_config["auth"]["access"]["blacklist"][$key] = trim($user);
                    if ($manifest_config["auth"]["access"]["blacklist"][$key] == "") {
                        unset($manifest_config["auth"]["access"]["blacklist"][$key]);
                    }
                }

                if (intval($_POST["permissions>max_size>hot"]) >= -1) {
                    $manifest_config["permissions"]["max_size"]["hot"] = intval($_POST["permissions>max_size>hot"]);
                } else {
                    echo "<p>The max ignore list size is lower than expected.</p>";
                    $valid = false;
                }
                if (intval($_POST["permissions>max_size>ignore"]) >= -1) {
                    $manifest_config["permissions"]["max_size"]["ignore"] = intval($_POST["permissions>max_size>ignore"]);
                } else {
                    echo "<p>The max ignore list size is lower than expected.</p>";
                    $valid = false;
                }

                if (intval($_POST["permissions>max_count>hot"]) >= -1) {
                    $manifest_config["permissions"]["max_count"]["hot"] = intval($_POST["permissions>max_count>hot"]);
                } else {
                    echo "<p>The max ignore list count is lower than expected.</p>";
                    $valid = false;
                }
                if (intval($_POST["permissions>max_count>ignore"]) >= -1) {
                    $manifest_config["permissions"]["max_count"]["ignore"] = intval($_POST["permissions>max_count>ignore"]);
                } else {
                    echo "<p>The max ignore list count is lower than expected.</p>";
                    $valid = false;
                }

                $manifest_config["files"]["hotlist"]["path"] = $_POST["files>hotlist>path"];
                $manifest_config["files"]["hotlist"]["active_id"] = $_POST["files>hotlist>active_id"];
                $manifest_config["files"]["ignorelist"]["path"] = $_POST["files>ignorelist>path"];
                $manifest_config["files"]["ignorelist"]["active_id"] = $_POST["files>ignorelist>active_id"];


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
                    <label for="auth>provider">Provider:</label> <input type="string" name="auth>provider" id="auth>provider" placeholder="../dropauth/authentication.php" value="<?php echo $manifest_config["auth"]["provider"] ; ?>"><br>
                    <label for="auth>admins">Admins:</label> <input type="string" name="auth>admins" id="auth>admins" placeholder="user1,user2" value="<?php echo $formatted_admins_list; ?>"><br>
                    <label for="auth>access>mode">Access Mode:</label> <select id="auth>access>mode" name="auth>access>mode">
                        <option value="whitelist" <?php if ($manifest_config["auth"]["access"]["mode"] == "whitelist") { echo "selected"; } ?>>Whitelist</option>
                        <option value="blacklist" <?php if ($manifest_config["auth"]["access"]["mode"] == "blacklist") { echo "selected"; } ?>>Blacklist</option>
                    </select><br>
                    <label for="auth>access>whitelist">Whitelist:</label> <input type="string" name="auth>access>whitelist" id="auth>access>whitelist" placeholder="user1,user2" value="<?php echo $formatted_whitelist; ?>"><br>
                    <label for="auth>access>blacklist">Blacklist:</label> <input type="string" name="auth>access>blacklist" id="auth>access>blacklist" placeholder="user1,user2" value="<?php echo $formatted_blacklist; ?>"><br>

                    <br><h3>Permissions</h3>
                    <label for="permissions>max_size>hot">Max Hot-List Entries:</label> <input type="number" name="permissions>max_size>hot" id="permissions>max_size>hot" placeholder="10" step="1" value="<?php echo $manifest_config["permissions"]["max_size"]["hot"]; ?>"><br>
                    <label for="permissions>max_size>ignore">Max Ignore-List Entries:</label> <input type="number" name="permissions>max_size>ignore" id="permissions>max_size>ignore" placeholder="10" step="1" value="<?php echo $manifest_config["permissions"]["max_size"]["ignore"]; ?>"><br>

                    <label for="permissions>max_count>hot">Max Hot-List Count:</label> <input type="number" name="permissions>max_count>hot" id="permissions>max_count>hot" placeholder="5" step="1" value="<?php echo $manifest_config["permissions"]["max_count"]["hot"]; ?>"><br>
                    <label for="permissions>max_count>ignore">Max Ignore-List Count:</label> <input type="number" name="permissions>max_count>ignore" id="permissions>max_count>ignore" placeholder="5" step="1" value="<?php echo $manifest_config["permissions"]["max_count"]["ignore"]; ?>"><br>

                    <br><h3>Files</h3>
                    <h4>Hot List</h4>
                    <label for="files>hotlist>path">Path:</label> <input type="string" name="files>hotlist>path" id="files>hotlist>path" placeholder="./listhot.json" value="<?php echo $manifest_config["files"]["hotlist"]["path"]; ?>"><br>
                    <label for="files>hotlist>active_id">Default ID:</label> <input type="string" name="files>hotlist>active_id" id="files>hotlist>active_id" placeholder="publichot" value="<?php echo $manifest_config["files"]["hotlist"]["active_id"]; ?>"><br>
                    <br><h4>Ignore List</h4>
                    <label for="files>ignorelist>path">Path:</label> <input type="string" name="files>ignorelist>path" id="files>ignorelist>path" placeholder="./listignore.json" value="<?php echo $manifest_config["files"]["ignorelist"]["path"]; ?>"><br>
                    <label for="files>ignorelist>active_id">Default ID:</label> <input type="string" name="files>ignorelist>active_id" id="files>ignorelist>active_id" placeholder="publicignore" value="<?php echo $manifest_config["files"]["ignorelist"]["active_id"]; ?>"><br>

                    <br><input class="button" type="submit" name="submit" id="submit" value="Submit">
                </form>
            </div>
        </main>
    </body>
</html>
