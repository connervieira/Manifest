<?php
include "./config.php";

$force_login_redirect = true;
include strval($manifest_config["auth"]["provider"]);

include "./authentication.php";
include "./loadlists.php";
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


            <h2>Create</h2>
            <?php
            if ($_POST["submit"] == "Create") {
                preg_replace("/[^A-Za-z0-9]/", '', $_POST["access_key"]);
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
                                        if ($list_name == "") { $list_name == "New List"; } // Use a default list name if one was not set.
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
                                            $list_contents["lists"][$username][$list_id]["name"] = $list_name;
                                            $list_contents["lists"][$username][$list_id]["description"] = "This is a new list.";
                                            $list_contents["lists"][$username][$list_id]["access_key"] = $list_access_key;
                                            $list_contents["lists"][$username][$list_id]["contents"] = array();

                                            file_put_contents($list_path, json_encode($list_contents, JSON_PRETTY_PRINT));
                                            echo "<p>Successfully created a new list.</p>";
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
            <form method="post">
                <label for="list_type">List Type:</label> <select name="list_type" id="list_type">
                    <option value="hotlist">Hot-List</option>
                    <option value="ignorelist">Ignore-List</option>
                </select><br>
                <label for="list_id">List ID:</label> <input name="list_id" id="list_id" pattern="[a-z0-9_\-]{1,20}" placeholder="my_list" required><br>
                <label for="list_name">List Name:</label> <input name="list_name" id="list_name" pattern="[A-Za-z0-9_\- \']{1,40}" placeholder="My List"><br>
                <label for="list_access_key">Access Key:</label> <input name="list_access_key" id="list_access_key" max="50" pattern="[A-Za-z0-9_\-]{0,50}" placeholder="abcde12345"><br>
                <input class="button" id="submit" name="submit" type="submit" value="Create">
            </form>

            <br><br><h2>Delete</h2>
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

            <br><br><h2>Edit</h2>
            <div style="width: 100%;">
                <div style="float:left;width:50%">
                    <h4>Hot Lists</h4><br>
                    <?php
                    if (sizeof($hotlist["lists"][$username]) > 0){
                        foreach ($hotlist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='managehotlists.php?list=" . $list. "'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no hot-lists.</i></p>"; }
                    ?>
                </div>
                <div style="float:right;width:50%;">
                    <h4>Ignore Lists</h4><br>
                    <?php
                    if (sizeof($ignorelist["lists"][$username]) > 0){
                        foreach ($ignorelist["lists"][$username] as $list => $data) {
                            echo "<a class='button' href='manageignorelists.php?list=" . $list. "'>" . $data["name"] . "</a><br><br style='margin-top:5px;'>";
                        }
                    } else { echo "<p><i>You have no ignore-lists.</i></p>"; }
                    ?>
                </div>
            </div>
            <div style="clear:both"></div>
        </main>
    </body>
</html>
