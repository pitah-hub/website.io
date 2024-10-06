<?php
//INCLUDE THE ACCESS TOKEN FILE
include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch the phone number and amount from the POST request
    $phone = $_POST['phone'];
    $money = $_POST['amount'];

    // Validate phone number format
    if (!preg_match('/^254\d{9}$/', $phone)) {
        die("Invalid phone number format. Please use 254xxxxxxxxx format.");
    }

    $processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $callbackurl = 'https://fe79-196-250-215-134.ngrok-free.app/login%202/callback.php/callback.php';
    $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
    $BusinessShortCode = '174379';
    $Timestamp = date('YmdHis');

    // ENCRYPT DATA TO GET PASSWORD
    $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
    $PartyA = $phone; // Phone number entered manually
    $PartyB = $BusinessShortCode; // Business shortcode
    $AccountReference = 'JAMES SOFTWARES';
    $TransactionDesc = 'stkpush test';
    $Amount = $money; // Amount entered manually

    // STK push request headers
    $stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];

    // INITIATE CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); //setting custom header

    $curl_post_data = array(
        // Fill in the request parameters with valid values
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $Password,
        'Timestamp' => $Timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $Amount,
        'PartyA' => $PartyA,
        'PartyB' => $PartyB,
        'PhoneNumber' => $PartyA,
        'CallBackURL' => $callbackurl,
        'AccountReference' => $AccountReference,
        'TransactionDesc' => $TransactionDesc
    );

    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    // Execute the request
    $curl_response = curl_exec($curl);

    // Check if curl_exec() failed
    if ($curl_response === false) {
        die("Curl failed: " . curl_error($curl));
    }

    // Decode the JSON response
    $data = json_decode($curl_response);

    // Handle response
    if (isset($data->ResponseCode) && $data->ResponseCode == "0") {
        // Successful transaction
        $CheckoutRequestID = $data->CheckoutRequestID;
        echo "The CheckoutRequestID for this transaction is: " . $CheckoutRequestID;
    } else {
        // Transaction failed
        echo "Transaction failed. Response: " . json_encode($data);
    }

    curl_close($curl);
}