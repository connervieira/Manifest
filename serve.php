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


$key = $_GET["key"];

if ($key == "" or $key == null) { // If no key is set, then don't authenticate, and simply return the public database.
    echo json_encode($hotlist[$manifest_config["files"][$list_type . "list"]["active_id"]]["contents"]);
}

// TODO: Authenticate private users, and return private ignore lists.
?>
