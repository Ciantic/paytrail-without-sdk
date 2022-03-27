<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Paytrail payment</title>
    <style>
        label {
            display: block;
            padding-bottom: 1em;
        }

        label span {
            display: block;
        }
    </style>
</head>

<body>

    <form method="GET" action="payment.php">
        <h1>Make payment</h1>

        <p>Total of the payment, if you have products listed these need to match.</p>
        <label>
            <span>Total amount (cents) *</span>
            <input name="amount" type="number" value="100" step="1" />
        </label>
        <label>
            <span>Email *</span>
            <input name="customer[email]" type="email" value="matti@example.com" required />
        </label>
        <label>
            <span>First name</span>
            <input name="customer[firstName]" type="text" />
        </label>
        <label>
            <span>Last name</span>
            <input name="customer[lastName]" type="text" />
        </label>
        <label>
            <span>Phone</span>
            <input name="customer[phone]" type="text" />
        </label>
        <label>
            <span>Vat ID</span>
            <input name="customer[vatId]" type="text" />
        </label>
        <label>
            <span>Order reference * (generated if not given)</span>
            <input name="reference" type="text" value="" />
        </label>
        <label>
            <span>Stamp * (generated if not given)</span>
            <input name="stamp" type="text" value="" />
        </label>

        <h2>Products</h2>
        <p>Products are optional</p>
        <table>
            <tr>
                <th>Code *</th>
                <th>Description</th>
                <th>VAT% *</th>
                <th>Price (cents) *</th>
                <th>Quantity *</th>
            </tr>
            <?php foreach (range(0, 4) as $i) : ?>

                <tr>
                    <td><input name="items[<?php echo $i; ?>][productCode]" type="text" /> </td>
                    <td><input name="items[<?php echo $i; ?>][description]" type="text" /> </td>
                    <td><input name="items[<?php echo $i; ?>][vatPercentage]" type="number" /> </td>
                    <td><input name="items[<?php echo $i; ?>][unitPrice]" type="number" /> </td>
                    <td><input name="items[<?php echo $i; ?>][units]" type="number" /> </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h2>Invoicing address</h2>
        <p>This is optional</p>
        <label>
            <span>Street *</span>
            <input name="invoicingAddress[streetAddress]" type="text" />
        </label>
        <label>
            <span>Postal code *</span>
            <input name="invoicingAddress[postalCode]" type="text" />
        </label>
        <label>
            <span>City *</span>
            <input name="invoicingAddress[city]" type="text" />
        </label>
        <label>
            <span>County</span>
            <input name="invoicingAddress[county]" type="text" value="" />
        </label>
        <label>
            <span>Country (Alpha-2 country code, e.g. FI) *</span>
            <input name="invoicingAddress[country]" type="text" value="FI" />
        </label>

        <h2>Delivery address</h2>
        <p>This is optional</p>
        <label>
            <span>Street *</span>
            <input name="deliveryAddress[streetAddress]" type="text" />
        </label>
        <label>
            <span>Postal code *</span>
            <input name="deliveryAddress[postalCode]" type="text" />
        </label>
        <label>
            <span>City * </span>
            <input name="deliveryAddress[city]" type="text" />
        </label>
        <label>
            <span>County</span>
            <input name="deliveryAddress[county]" type="text" value="" />
        </label>
        <label>
            <span>Country (Alpha-2 country code, e.g. FI) *</span>
            <input name="deliveryAddress[country]" type="text" value="FI" />
        </label>

        <h2>Settings</h2>
        <label>
            <span>Language * (FI, SV, EN)</span>
            <input name="language" type="text" value="FI" />
        </label>
        <label>
            <span>Currency *</span>
            <input name="currency" type="text" value="EUR" />
        </label>
        <label>
            <span>Redirect on success url * (generated if not given)</span>
            <input name="redirectUrls[success]" type="url" value="" />
        </label>
        <label>
            <span>Redirect on cancel url * (generated if not given)</span>
            <input name="redirectUrls[cancel]" type="url" value="" />
        </label>

        <label>
            <input name="_create_link" type="checkbox" value="1" />
            <em>Create payment link (does not go to payment)</em>
        </label>

        <div>
            <input type="submit" value="Proceed" />
        </div>
    </form>
</body>

</html>