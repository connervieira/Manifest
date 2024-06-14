<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";

if (in_array($username, $manifest_config["auth"]["admins"]) == false) { // Check to see if this user is authorized to be here.
    echo "<p>This tool is for use only by administrators!</p>";
    exit();
}

$selected_user = preg_replace("/[^a-zA-Z0-9_\-]/", '', $_GET["user"]);
if (strlen($selected_user) > 100) {
    echo "<p>The selected username is excessively long.</p>";
    exit();
}

include "./loadlists.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo htmlspecialchars($manifest_config["product_name"]); ?> - Manage Permissions</title>
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
            <h2>Permissions</h2>
            <hr>
            <h3>Select User</h3><br>
            <?php
            $known_users = array_unique(array_merge(array_keys($hotlist["lists"]), array_keys($ignorelist["lists"]), array_keys($manifest_config["users"]))); // Get a list of all known users by combining the list of users with lists, and the list of users in the configuration.
            foreach ($known_users as $known_user) {
                echo "<a class='button' href='?user=" . $known_user . "'>" . $known_user . "</a>";
            }
            ?><br><br>
            <h3>Edit Permissions</h3>
            <?php
            if ($_POST["submit"] == "Submit") {
                $valid = true; // This will be set to false if an invalid configuration value is encountered.

                $user = preg_replace("/[^a-zA-Z0-9_\-]/", '', $_POST["user"]);
                $list_capacity_hot = intval($_POST["list_capacity>hot"]);
                $list_capacity_ignore = intval($_POST["list_capacity>ignore"]);
                $list_count_hot = intval($_POST["list_count>hot"]);
                $list_count_ignore = intval($_POST["list_count>ignore"]);


                if (strlen($user) > 1 and strlen($user) < 100) {
                    if ($list_capacity_hot < -1) { echo "<p>The max hot-list capacity is lower than expected.</p>"; $valid = false;
                    } else if ($list_capacity_hot > 10000000) { echo "<p>The max hot-list capacity is excessively high.</p>"; $valid = false; }

                    if ($list_capacity_ignore < -1) { echo "<p>The max ignore-list capacity is lower than expected.</p>"; $valid = false;
                    } else if ($list_capacity_ignore > 10000000) { echo "<p>The max ignore-list capacity is excessively high.</p>"; $valid = false; }

                    if ($list_count_hot < -1) { echo "<p>The max hot-list count is lower than expected.</p>"; $valid = false;
                    } else if ($list_count_hot > 10000) { echo "<p>The max hot-list count is excessively high.</p>"; $valid = false; }

                    if ($list_count_ignore < -1) { echo "<p>The max ignore-list count is lower than expected.</p>"; $valid = false;
                    } else if ($list_count_ignore > 10000) { echo "<p>The max ignore-list count is excessively high.</p>"; $valid = false; }

                    $manifest_config["users"][$user]["permissions"]["list_capacity"]["hot"] = $list_capacity_hot;
                    $manifest_config["users"][$user]["permissions"]["list_capacity"]["ignore"] = $list_capacity_ignore;
                    $manifest_config["users"][$user]["permissions"]["list_count"]["hot"] = $list_count_hot;
                    $manifest_config["users"][$user]["permissions"]["list_count"]["ignore"] = $list_count_ignore;


                    if ($valid == true) {
                        file_put_contents($manifest_config_database_name, json_encode($manifest_config, (JSON_UNESCAPED_SLASHES)));
                        echo "<p>Successfully updated permissions.</p>";
                    } else {
                        echo "<p>The user's permissions were not updated.</p>";
                    }
                } else {
                    echo "<p>The length of the specified username is outside of the expected range.</p>";
                }
            }


                $list_capacity_ignore = intval($POST["list_capacity>ignore"]);
                $list_count_hot = intval($POST["list_count>hot"]);
                $list_count_ignore = intval($POST["list_count>ignore"]);
            ?>
            <div class="basicform">
                <form method="POST">
                    <label for="user">User:</label> <input type="string" name="user" id="user" maxlength="100" placeholder="Username" value="<?php echo $selected_user; ?>" required><br>
                    <label for="list_capacity>hot">Hot-List Capacity:</label> <input type="number" step="1" min="-1" max="10000000" name="list_capacity>hot" id="list_capacity>hot" value="<?php echo $manifest_config["users"][$selected_user]["permissions"]["list_capacity"]["hot"]; ?>" required><br>
                    <label for="list_capacity>ignore">Ignore-List Capacity:</label> <input type="number" step="1" min="-1" max="10000000" name="list_capacity>ignore" id="list_capacity>ignore" value="<?php echo $manifest_config["users"][$selected_user]["permissions"]["list_capacity"]["ignore"]; ?>" required><br>
                    <br>
                    <label for="list_count>hot">Hot-List Count:</label> <input type="number" step="1" min="-1" max="1000" name="list_count>hot" id="list_count>hot" value="<?php echo $manifest_config["users"][$selected_user]["permissions"]["list_count"]["hot"]; ?>" required><br>
                    <label for="list_count>ignore">Ignore-List Count:</label> <input type="number" step="1" min="-1" max="1000" name="list_count>ignore" id="list_count>ignore" value="<?php echo $manifest_config["users"][$selected_user]["permissions"]["list_count"]["ignore"]; ?>" required><br>
                    <br><input class="button" type="submit" name="submit" id="submit" value="Submit">
                </form>
            </div>
        </main>
    </body>
</html>
