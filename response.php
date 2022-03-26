<?php
include 'lib.php';

$merchant_secret = "SAIPPUAKAUPPIAS";

// Callback to validate payment (used only when returning from Paytrail)
if (paytrail_hmac($merchant_secret, $_GET) !== $_GET["signature"]) {
    die("Signature check failed");
}
if ($_GET["checkout-status"] === "ok") {
    die("Payment succeeded");
} else if ($_GET["checkout-status"] === "fail") {
    die("Payment failed");
}
