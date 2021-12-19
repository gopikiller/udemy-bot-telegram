<?php
$db = mysqli_connect("localhost", "saveitsa_gopi", "PASSWORD", "saveitsa_bot");
if (!$db) {
    echo 'not connected';
    die();
}
