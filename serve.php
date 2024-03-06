<?php
$file = "../plates/emergency.json"; // This defines where the hotlist is saved.
$hotlist = json_decode(file_get_contents($file), true); // Load the hot list contents from the file.

$key = $_GET["key"];

if ($key == "public" or $key == "" or $key == null) { // If the key is set to 'public', then don't authenticate, and simply return the public database.
    echo json_encode($hotlist);
}

// TODO: Authenticate private users, and return private ignore lists.
?>
