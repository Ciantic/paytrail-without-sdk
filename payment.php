<?php

// This is a form handler for the payment

include 'lib.php';

$merchant = "375917";
$merchant_secret = "SAIPPUAKAUPPIAS";

// User input sanitization, very poor
$payload = json_decode(json_encode($_GET), FALSE);
unset($payload->_create_link);
unset($payload->_show_link);
paytrail_sanitize_pay($payload);

// Creates a link to show
if (!empty($_GET["_create_link"])) {
    $path = strtok($_SERVER['REQUEST_URI'], "?");
    header("Location: https://{$_SERVER['HTTP_HOST']}{$path}?_show_link=1&" . http_build_query($payload));
    exit;
}

// Callback urls, when returning from paytrail
$callback_url = "https://{$_SERVER['HTTP_HOST']}/" . strtok($_SERVER['REQUEST_URI'], '/') . "/response.php";
$payload->callbackUrls = $payload->redirectUrls = (object) [
    "success" => $payload->redirectUrls->success ?? $callback_url,
    "cancel" =>  $payload->redirectUrls->cancel ?? $callback_url
];

// Try to do the payment
try {
    $payment = paytrail_pay($payload, $merchant, $merchant_secret);
} catch (PaytrailStampException $e) {
    die("Link is already created, <a href='./'>create a new link</a>");
} catch (PaytrailException $e) {
    die($e->getMessage());
}

if (!empty($_GET["_show_link"])) {
    // Show the payment link
?>

    <h2>Link to the payment (payment works only once!)</h2>

    <p><input style="width: 100%" type="text" value="<?php echo htmlentities($payment->href) ?>" onclick="this.select();"></p>

    <details>
        <summary>Show payload and response</summary>

        <h3>Payload</h3>
        <pre><?php echo htmlentities(json_encode($payload, JSON_PRETTY_PRINT)); ?></pre>

        <h3>Response</h3>
        <pre><?php echo htmlentities(json_encode($payment, JSON_PRETTY_PRINT)); ?></pre>
    </details>
<?php
} else {
    // Redirect to payment
    header("Location: $payment->href");
}
