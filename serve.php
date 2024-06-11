<?php
include "./config.php";


$list_type = $_GET["type"];
$list_type = strtolower($_GET["type"]);
$list_type = preg_replace('/[^a-z]/', '', $list_type);

if ($list_type !== "hot" and $list_type !== "ignore") {
    echo "{}";
    exit();
}

$file = $manifest_config["files"][$list_type . "list"]["path"]; // This defines where the hotlist is saved.
$hotlist = json_decode(file_get_contents($file), true); // Load the hot list contents from the file.


$id = preg_replace("/[^A-Za-z0-9]/", '', strval($_GET["id"]));
$key = preg_replace("/[^A-Za-z0-9]/", '', strval($_GET["key"]));

if ($id == "" or $id == null) { // If no ID is set, then simply return the default database.
    $id = $manifest_config["files"][$list_type . "list"]["active_id"];
}

if (strlen($manifest_config["users"][$id]["settings"]["access_key"]) > 0) { // Check to see if an access key was set by this user.
    if ($key == $manifest_config["users"][$id]["settings"]["access_key"]) { // Check to see if the access key is correct.
        $list_data = $hotlist["lists"][$id]["contents"];
    } else {
        echo "{\"error\": \"The supplied access key is incorrect.\"}";
    }
} else { // Otherwise, no authentication is required.
    $list_data = $hotlist["lists"][$id]["contents"];
}

if ($list_data == null) {
    $list_data = array();
}

echo json_encode($list_data);

?>
