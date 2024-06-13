<?php
include "./config.php";


$list_type = $_GET["type"];
$list_type = strtolower($_GET["type"]);
$list_type = preg_replace('/[^a-z]/', '', $list_type);

if ($list_type !== "hot" and $list_type !== "ignore") {
    echo "{\"error\": \"The supplied list type is invalid.\"}";
    exit();
}

$file = $manifest_config["files"][$list_type . "list"]["path"]; // This defines where the list is saved.
$list = json_decode(file_get_contents($file), true); // Load the hot list contents from the file.

$user = preg_replace("/[^A-Za-z0-9\-_]/", '', strval($_GET["user"]));
$id = preg_replace("/[^a-z0-9\-_]/", '', strval($_GET["list"]));
$key = preg_replace("/[^A-Za-z0-9\-_]/", '', strval($_GET["key"]));

if ($user == "" or $user == null) { // If no user is set, then simply return the default user.
    $user = $manifest_config["files"][$list_type . "list"]["active_id"];
}
if ($id == "" or $id == null) { // If no ID is set, then simply return the default database.
    $id = "default";
}

if (!in_array($id, array_keys($list["lists"][$user]))) {
    echo "{\"error\": \"The supplied list ID does not exist.\"}";
    exit();
}

if (strlen($list["lists"][$user][$id]["access_key"]) > 0) { // Check to see if an access key was set by this user.
    if ($key !== $list["lists"][$user][$id]["access_key"]) { // Check to see if the access key is incorrect.
        echo "{\"error\": \"The supplied access key is incorrect.\"}";
        exit();
    }
}
$list_data = $list["lists"][$user][$id]["contents"];

if ($list_data == null) {
    $list_data = array();
}

echo json_encode($list_data);

?>
