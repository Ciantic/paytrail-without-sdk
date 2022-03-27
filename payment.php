<?php
// FORM HANDLER

include 'lib.php';

$merchant = "375917";
$merchant_secret = "SAIPPUAKAUPPIAS";

// User input sanitization, very poor
$pdata = json_decode(json_encode($_GET), FALSE);
unset($pdata->_create_link);
unset($pdata->_show_link);
paytrail_sanitize_pay($pdata);

// Creates a link to show
if (!empty($_GET["_create_link"])) {
    $path = strtok($_SERVER['REQUEST_URI'], "?");
    header("Location: https://{$_SERVER['HTTP_HOST']}{$path}?_show_link=1&" . http_build_query($pdata));
    exit;
}

// Callback urls, when returning from paytrail
$callback_url = "https://{$_SERVER['HTTP_HOST']}/" . strtok($_SERVER['REQUEST_URI'], '/') . "/response.php";
$pdata->callbackUrls = $pdata->redirectUrls = (object) [
    "success" => $pdata->redirectUrls->success ?: $callback_url,
    "cancel" =>  $pdata->redirectUrls->cancel ?: $callback_url
];

// Redirect to payment
try {
    $url = paytrail_pay($pdata, $merchant, $merchant_secret);
} catch (PaytrailStampException $e) {
    die("Link is already created, <a href='./'>create a new link</a>");
} catch (PaytrailException $e) {
    die($e->getMessage());
}

if (!empty($_GET["_show_link"])) {
?>
    <h2>Link to the payment (payment works only once!)</h2>

    <input style="width: 100%" type="text" value="<?php echo htmlentities($url) ?>" onclick="this.select();">
<?php
} else {
    header("Location: $url");
}
