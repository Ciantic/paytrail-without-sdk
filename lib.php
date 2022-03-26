<?php

class PaytrailStampException extends Exception
{
}
class PaytrailException extends Exception
{
}
/**
 * Generate HMAC signature
 * 
 * @param string $secret merchant secret, e.g. SAIPPUAKAUPPIAS
 * @param array $params usually $_GET 
 * @param string $body JSON body
 * @return string|false 
 */
function paytrail_hmac(string $secret, array $params, string $body = '')
{
    $keys = array_filter(array_keys($params), function ($key) {
        return preg_match('/^checkout-/', $key);
    });
    sort($keys, SORT_STRING);
    $rows = array_map(
        function ($key) use ($params) {
            return join(':', [$key, $params[$key]]);
        },
        $keys
    );
    $str = join("\n", [...$rows, $body]);
    return hash_hmac('sha256', $str, $secret);
}

/**
 * Sanitizes the payload given to `paytrail_pay`
 * 
 * Not very tested 
 * 
 * @param object $pdata Unsanitized value
 * @return void
 */
function paytrail_sanitize_pay(object &$pdata)
{
    if (empty($pdata->stamp)) {
        $pdata->stamp = uniqid("order");
    }

    $pdata->amount = (int) ($pdata->amount * 100);

    // Addresses
    if (empty($pdata->invoicingAddress->streetAddress)) {
        unset($pdata->invoicingAddress);
    }
    if (empty($pdata->deliveryAddress->streetAddress)) {
        unset($pdata->deliveryAddress);
    }

    // Product items
    if (!empty($pdata->items)) {
        foreach (array_reverse(array_keys($pdata->items)) as $n) {
            if (empty($pdata->items[$n]->productCode)) {
                unset($pdata->items[$n]);
            } else {
                $pdata->items[$n]->unitPrice = (int) ($pdata->items[$n]->unitPrice * 100);
                $pdata->items[$n]->units = (int) $pdata->items[$n]->units;
                $pdata->items[$n]->vatPercentage = (int) $pdata->items[$n]->vatPercentage;
            }
        }
    }
    if (empty($pdata->items)) {
        unset($pdata->items);
    }
}

/**
 * Initiate the payment on paytrail, returns payment url 
 * 
 * @param object $payload See https://docs.paytrail.com/#/?id=request-body
 * @param string $merchantId Numeric id as string e.g. 375917
 * @param string $secretKey Secret, e.g. SAIPPUAKAUPPIAS
 * @return string Returns the url which handles the payment
 * @throws Exception 
 */
function paytrail_pay(object $payload, string $merchantId, string $secretKey)
{
    $ch = curl_init();
    $body = json_encode($payload);
    $headers = array(
        // 'checkout-transaction-id' => '12345', // for existing transactions only
        'checkout-account' =>  $merchantId,
        'checkout-algorithm' => 'sha256',
        'checkout-method' => 'POST',
        'checkout-nonce' => uniqid(),
        'checkout-timestamp' => date(DATE_ISO8601),
        'content-type' => 'application/json; charset=utf-8',
        'platform-name' =>  'dingle dong',
    );
    $headers["signature"] = paytrail_hmac($secretKey, $headers, $body);

    curl_setopt($ch, CURLOPT_URL, "https://services.paytrail.com/payments");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(
        function ($key) use ($headers) {
            return join(':', [$key, $headers[$key]]);
        },
        array_keys($headers)
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($server_output);
    if ($json && isset($json->status) && $json->status === "error") {
        if (
            isset($json->meta) &&
            isset($json->meta[0]) &&
            $json->meta[0] === "instance.stamp or instance.item.stamp already exists for merchant."
        ) {
            throw new PaytrailStampException();
        }
        throw new PaytrailException($json->message);
    }
    return $json->href;
}
