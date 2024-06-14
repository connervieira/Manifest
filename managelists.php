<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";
include "./loadlists.php";


if ($manifest_config["users"][$username]["permissions"]["list_count"]["hot"] > -1) { // Check to see if an individual override is set for this user's list capacity.
    $users_max_list_count_hot = $manifest_config["users"][$username]["permissions"]["list_count"]["hot"];
} else { // Otherwise, use the default list capacity.
    $users_max_list_count_hot = $manifest_config["permissions"]["max_count"]["hot"];
}

if ($manifest_config["users"][$username]["permissions"]["list_count"]["ignore"] > -1) { // Check to see if an individual override is set for this user's list capacity.
    $users_max_list_count_ignore = $manifest_config["users"][$username]["permissions"]["list_count"]["ignore"];
} else { // Otherwise, use the default list capacity.
    $users_max_list_count_ignore = $manifest_config["permissions"]["max_count"]["ignore"];
}
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
            <h2>List Management</h2>
            <hr>


            <h3 id="create">Create</h3>
            <?php
            if ($_POST["submit"] == "Create") {
                $list_type = $_POST["list_type"];
                $list_id = preg_replace("/[^a-z0-9_\-]/", '', $_POST["list_id"]);
                $list_name = preg_replace("/[^A-Za-z0-9_\- \']/", '', $_POST["list_name"]);
                $list_access_key = preg_replace("/[^A-Za-z0-9_\-]/", '', $_POST["list_access_key"]);
                if (strlen($list_id) <= 20) {
                    if ($list_id == $_POST["list_id"]) {
                        if (strlen($list_name) <= 40) {
                            if ($list_name == $_POST["list_name"]) {
                                if (strlen($list_access_key) <= 50) {
                                    if ($list_access_key == $_POST["list_access_key"]) {
                                        if ($list_name == "") { $list_name = "New List"; } // Use a default list name if one was not set.
                                        if ($list_type == "hotlist") {
                                            $list_contents = &$hotlist;
                                        } else if ($list_type == "ignorelist") {
                                            $list_contents = &$ignorelist;
                                        } else {
                                            echo "<p>Invalid list type selected.</p>";
                                            exit();
                                        }
                                        $list_path = $manifest_config["files"][$list_type]["path"];
                                        if (!in_array($list_id, array_keys($list_contents["lists"][$username]))) { // Check to make sure the provided list ID is not a duplicate.
                                            if (sizeof(array_keys($hotlist["lists"][$username])) < $users_max_list_count_hot) {
                                                $list_contents["lists"][$username][$list_id]["name"] = $list_name;
                                                $list_contents["lists"][$username][$list_id]["description"] = "This is a new list.";
                                                $list_contents["lists"][$username][$list_id]["access_key"] = $list_access_key;
                                                $list_contents["lists"][$username][$list_id]["contents"] = array();

                                                file_put_contents($list_path, json_encode($list_contents, JSON_PRETTY_PRINT));
                                                echo "<p>Successfully created a new list.</p>";
                                            } else {
                                                echo "<p>You have reached the maximum number of allowed lists for your account.</p>";
                                            }
                                        } else {
                                            echo "<p>The provided list ID already exists.</p>";
                                        }
                                    } else {
                                        echo "<p>The provided list access key contains disallowed characters.</p>";
                                    }
                                } else {
                                    echo "<p>The provided list access key is excessively long.</p>";
                                }
                            } else {
                                echo "<p>The provided list name contains disallowed characters.</p>";
                            }
                        } else {
                            echo "<p>The provided list name is excessively long.</p>";
                        }
                    } else {
                        echo "<p>The provided list ID contains disallowed characters.</p>";
                    }
                } else {
                    echo "<p>The provided list ID is excessively long.</p>";
                }
            }
            ?>
            <p style="margin-bottom:0px;"><?php echo sizeof(array_keys($hotlist["lists"][$username])) . "/" . $users_max_list_count_hot . " hot-lists used."; ?></p>
            <p style="margin-top:0px;"><?php echo sizeof(array_keys($ignorelist["lists"][$username])) . "/" . $users_max_list_count_ignore . " ignore-lists used."; ?></p>
            <form method="post">
                <label for="list_type">List Type:</label> <select name="list_type" id="list_type">
                    <option value="hotlist">Hot-List</option>
                    <option value="ignorelist">Ignore-List</option>
                </select><br>
                <label for="list_id">List ID:</label> <input name="list_id" id="list_id" pattern="[a-z0-9_\-]{1,20}" placeholder="my_list" required><br>
                <label for="list_name">List Name:</label> <input name="list_name" id="list_name" pattern="[A-Za-z0-9_\- \']{1,40}" placeholder="My List"><br>
                <label for="list_access_key">Access Key:</label> <input type="text" name="list_access_key" id="list_access_key" maxlength="50" pattern="[A-Za-z0-9_\-]{0,50}" placeholder="abcde12345" value="<?php echo $manifest_config["users"][$username]["settings"]["defaults"]["lists"]["access_key"]; ?>"><br>
                <input class="button" id="submit" name="submit" type="submit" value="Create">
            </form>

            <br><hr style="width:70%"><h3 id="delete">Delete</h3>
            <?php
            if ($_POST["submit"] == "Delete Hot-List") {
                $list_contents = &$hotlist;
                $list_path = $manifest_config["files"]["hotlist"]["path"];
            } else if ($_POST["submit"] == "Delete Ignore-List") {
                $list_contents = &$ignorelist;
                $list_path = $manifest_config["files"]["ignorelist"]["path"];
            }
            if ($_POST["submit"] == "Delete Hot-List" or $_POST["submit"] == "Delete Ignore-List") {
                $list_id = preg_replace("/[^A-Za-z0-9_\-]/", '', $_POST["list_id"]);
                if (in_array($list_id, array_keys($list_contents["lists"][$username]))) {
                    unset($list_contents["lists"][$username][$list_id]);
                    file_put_contents($list_path, json_encode($list_contents, JSON_PRETTY_PRINT));
                    echo "<p>Successfully deleted a list.</p>";
                } else {
                    echo "<p>The selected list does not exist.</p>";
                }
            }

            ?>
            <div style="width: 100%;">
                <div style="float:left;width:50%">
                    <h4>Hot Lists</h4><br>
                    <form method="post">
                        <label for="list_id">List ID:</label> <select id="list_id" name="list_id" required>
                            <?php
                            foreach ($hotlist["lists"][$username] as $list => $data) {
                                echo "<option value=" . $list. "'";
                                if ($list == "default") { echo " disabled"; }
                                echo ">" . $data["name"] . " (" . $list . ")</option>";
                            }
                            ?>
                        </select><br>
                        <input class="button" id="submit" name="submit" type="submit" value="Delete Hot-List">
                    </form>
                </div>
                <div style="float:right;width:50%;">
                    <h4>Ignore Lists</h4><br>
                    <form method="post">
                        <label for="list_id">List ID:</label> <select id="list_id" name="list_id" required>
                            <?php
                            foreach ($ignorelist["lists"][$username] as $list => $data) {
                                echo "<option value=" . $list. "'";
                                if ($list == "default") { echo " disabled"; }
                                echo ">" . $data["name"] . " (" . $list . ")</option>";
                            }
                            ?>
                        </select><br>
                        <input class="button" id="submit" name="submit" type="submit" value="Delete Ignore-List">
                    </form>
                </div>
            </div>
            <div style="clear:both"></div>

            <br><hr style="width:70%"><h3 id="edit">Edit</h3>
            <div style="width: 100%;">
                <?php
                if ($_POST["submit"] == "Edit") {
                    $list_type = $_POST["list_type"];
                    $list_id = preg_replace("/[^a-z0-9_\-]/", '', $_POST["list_id"]);
                    $list_name = preg_replace("/[^A-Za-z0-9_\- \']/", '', $_POST["list_name"]);
                    $list_access_key = preg_replace("/[^A-Za-z0-9_\-]/", '', $_POST["list_access_key"]);
                    if (strlen($list_id) <= 20) {
                        if ($list_id == $_POST["list_id"]) {
                            if (strlen($list_name) <= 40) {
                                if ($list_name == $_POST["list_name"]) {
                                    if (strlen($list_access_key) <= 50) {
                                        if ($list_access_key == $_POST["list_access_key"]) {
                                            if ($list_name == "") { $list_name = "New List"; } // Use a default list name if one was not set.
                                            if ($list_type == "hotlist") {
                                                $list_contents = &$hotlist;
                                            } else if ($list_type == "ignorelist") {
                                                $list_contents = &$ignorelist;
                                            } else {
                                                echo "<p>Invalid list type selected.</p>";
                                                exit();
                                            }
                                            $list_path = $manifest_config["files"][$list_type]["path"];
                                            if (in_array($list_id, array_keys($list_contents["lists"][$username]))) { // Check to make sure the provided list ID already exists.
                                                $list_contents["lists"][$username][$list_id]["name"] = $list_name;
                                                $list_contents["lists"][$username][$list_id]["access_key"] = $list_access_key;

                                                file_put_contents($list_path, json_encode($list_contents, JSON_PRETTY_PRINT));
                                                echo "<p>Successfully edited list.</p>";
                                            } else {
                                                echo "<p>The provided list ID doesn't exist.</p>";
                                            }
                                        } else {
                                            echo "<p>The provided list access key contains disallowed characters.</p>";
                                        }
                                    } else {
                                        echo "<p>The provided list access key is excessively long.</p>";
                                    }
                                } else {
                                    echo "<p>The provided list name contains disallowed characters.</p>";
                                }
                            } else {
                                echo "<p>The provided list name is excessively long.</p>";
                            }
                        } else {
                            echo "<p>The provided list ID contains disallowed characters.</p>";
                        }
                    } else {
                        echo "<p>The provided list ID is excessively long.</p>";
                    }
                }
                ?>
                <div style="float:left;width:50%">
                    <h4>Hot Lists</h4><br>
                    <?php
                    if (sizeof($hotlist["lists"][$username]) > 0){
                        foreach ($hotlist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='?type=hotlist&list=" . $list . "#edit'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no hot-lists.</i></p>"; }
                    ?>
                </div>
                <div style="float:right;width:50%;">
                    <h4>Ignore Lists</h4><br>
                    <?php
                    if (sizeof($ignorelist["lists"][$username]) > 0){
                        foreach ($ignorelist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='?type=ignorelist&list=" . $list . "'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no ignore-lists.</i></p>"; }
                    ?>
                </div>
            </div>
            <div style="clear:both">
                <form method="post" action="#edit">
                    <?php
                    if ($_POST["submit"] !== "Edit") {
                        if (strlen($_GET["type"]) > 0 and strlen($_GET["list"]) > 0) {
                            if ($_GET["type"] == "hotlist") {
                                $loaded_list = $hotlist;
                            } else if ($_GET["type"] == "ignorelist") {
                                $loaded_list = $ignorelist;
                            } else {
                                echo "<p>Invalid list type selected.</p>";
                                $loaded_list = array();
                                $loaded_list["lists"][$username] = array();
                            }
                            if (in_array($_GET["list"], array_keys($loaded_list["lists"][$username]))) {
                                echo "<label for='list_type'>List Type:</label> <select id='list_type' name='list_type' readonly>";
                                echo "    <option value='hotlist'"; if ($_GET["type"] == "hotlist") { echo " selected"; } echo ">Hot-List</option>";
                                echo "    <option value='ignorelist'"; if ($_GET["type"] == "ignorelist") { echo " selected"; } echo ">Ignore-List</option>";
                                echo "</select><br>";
                                echo '<label for="list_id">List ID:</label> <input type="text" id="list_id" name="list_id" max="30" placeholder="List ID" value="' . $_GET["list"] . '" required readonly><br>';
                                echo '<label for="list_name">List Name:</label> <input type="text" id="list_name" name="list_name" max="30" placeholder="List Name" value="' . $loaded_list["lists"][$username][$_GET["list"]]["name"] . '"><br>';
                                echo '<label for="list_access_key">Access Key:</label> <input type="text" id="list_access_key" name="list_access_key" maxlength="100" placeholder="abcde12345" value="' . $loaded_list["lists"][$username][$_GET["list"]]["access_key"] . '"><br>';
                                echo '<input class="button" id="submit" name="submit" type="submit" value="Edit">';
                            } else {
                                echo "<p>The selected list does not exist.</p>";
                            }
                        } else {
                            echo "<p><i>Select a list to edit.</i></p>";
                        }
                    }
                    ?>
                </form>
            </div>

            <br><hr style="width:70%"><h3 id="manage">Manage</h3>
            <div style="width: 100%;">
                <div style="float:left;width:50%">
                    <h4>Hot Lists</h4><br>
                    <?php
                    if (sizeof($hotlist["lists"][$username]) > 0){
                        foreach ($hotlist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='managehotlists.php?list=" . $list . "'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no hot-lists.</i></p>"; }
                    ?>
                </div>
                <div style="float:right;width:50%;">
                    <h4>Ignore Lists</h4><br>
                    <?php
                    if (sizeof($ignorelist["lists"][$username]) > 0){
                        foreach ($ignorelist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='manageignorelists.php?list=" . $list . "'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no ignore-lists.</i></p>"; }
                    ?>
                </div>
            </div>
            <div style="clear:both"></div>
        </main>
    </body>
</html>
