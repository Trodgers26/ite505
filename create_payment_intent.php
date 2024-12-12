<?php
require 'stripe_config.php';

header('Content-Type: application/json');

// Retrieve the JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate incoming data
if (empty($data['amount']) || empty($data['payment_method_id'])) {
    echo json_encode(['error' => 'Missing required parameters.']);
    http_response_code(400); // Bad Request
    exit;
}

try {
    // Create the PaymentIntent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $data['amount'],
        'currency' => 'usd',
        'payment_method' => $data['payment_method_id'],
        'confirmation_method' => 'automatic',
        'confirm' => true,
        'return_url' => 'http://localhost/ite505/payment_success.php', // Replace with your actual return URL
    ]);

    // Respond with the client_secret and paymentIntent ID
    echo json_encode([
        'client_secret' => $paymentIntent->client_secret,
        'id' => $paymentIntent->id,
        'status' => $paymentIntent->status
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Stripe API errors
    echo json_encode(['error' => $e->getError()->message]);
    http_response_code(400);
} catch (Exception $e) {
    // General errors
    echo json_encode(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
    http_response_code(500);
}
?>

