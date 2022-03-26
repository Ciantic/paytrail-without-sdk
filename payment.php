<?php
// FORM HANDLER

include 'lib.php';

$merchant = "375917";
$merchant_secret = "SAIPPUAKAUPPIAS";

// Special case, user wants just the link
/*
if (!empty($_GET["_create_link"])) {
    // ------------------------------------------------------
    // Show link 
    $args = json_decode(json_encode($_GET), FALSE);
    unset($args->_create_link);
    paytrail_sanitize_pay($args);
    $query = http_build_query($args);
    $path = strtok($_SERVER['REQUEST_URI'], "?");
    $url = "https://{$_SERVER['HTTP_HOST']}" . $path . "?" . $query;
?>
    <h2>Link to the payment:</h2>
    <input style="width: 100%" type="text" value="<?php echo htmlentities($url) ?>" onclick="this.select();">
<?php
    exit;
}
*/

// User input sanitization, very poor
$pdata = json_decode(json_encode($_GET), FALSE);
unset($pdata->_create_link);
paytrail_sanitize_pay($pdata);

// Callback urls, when returning from paytrail
$callback_url = "https://{$_SERVER['HTTP_HOST']}/" . strtok($_SERVER['REQUEST_URI'], '/') . "/response.php";
$pdata->callbackUrls = $pdata->redirectUrls = (object) [
    "success" => $callback_url,
    "cancel" =>  $callback_url
];

// Redirect to payment
try {
    $url = paytrail_pay($pdata, $merchant, $merchant_secret);
} catch (PaytrailStampException $e) {
    die("Link is already created, <a href='./'>create a new link</a>");
} catch (PaytrailException $e) {
    die($e->getMessage());
}


if (!empty($_GET["_create_link"])) { ?>
    <h2>Link to the payment (payment works only once!)</h2>
    <input style="width: 100%" type="text" value="<?php echo htmlentities($url) ?>" onclick="this.select();">
<?php
} else {
    header("Location: $url");
}
