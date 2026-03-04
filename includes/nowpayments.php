<?php
require_once __DIR__ . '/../config/config.php';

class NowPayments {
    private $api_key;
    private $base_url = "https://api.nowpayments.io/v1/";

    public function __construct() {
        $this->api_key = NOWPAYMENTS_API_KEY;
    }

    private function request($endpoint, $method = 'GET', $data = []) {
        $ch = curl_init();
        $url = $this->base_url . $endpoint;
        
        if ($method == 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing environments
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $this->api_key,
            'Content-Type: application/json'
        ]);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'Curl Error: ' . $error];
        }

        return json_decode($response, true);
    }

    public function createInvoice($amount, $currency = 'usd', $order_id = '', $description = '') {
        $data = [
            'price_amount' => $amount,
            'price_currency' => $currency,
            'order_id' => $order_id,
            'order_description' => $description,
            'ipn_callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/api/nowpayments_ipn.php',
            'success_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/payment-success.php',
            'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/user/upgrade.php'
        ];
        return $this->request('invoice', 'POST', $data);
    }

    public function getPaymentStatus($payment_id) {
        return $this->request('payment/' . $payment_id, 'GET');
    }

    public function getCurrencies() {
        return $this->request('currencies', 'GET');
    }

    public function getEstimate($amount, $currency_from, $currency_to) {
        return $this->request('estimate', 'GET', [
            'amount' => $amount,
            'currency_from' => $currency_from,
            'currency_to' => $currency_to
        ]);
    }
}

$nowPayments = new NowPayments();
?>
