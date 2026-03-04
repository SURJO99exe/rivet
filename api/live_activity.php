<?php
require_once __DIR__ . '/../config/config.php';

// Expanded list of countries
$countries = ['United States', 'Bangladesh', 'United Kingdom', 'Russia', 'Canada', 'Germany', 'France', 'India', 'Japan', 'Pakistan', 'Nigeria', 'Philippines', 'Brazil', 'Vietnam', 'Indonesia', 'Turkey', 'Italy', 'Spain', 'South Africa', 'Australia', 'Mexico', 'Egypt', 'Malaysia', 'United Arab Emirates'];

// Expanded list of diverse activities
$activities = [
    ['action' => 'earned', 'amount' => '$0.0500', 'type' => 'from premium survey'],
    ['action' => 'withdrawn', 'amount' => '$10.00', 'type' => 'via PayPal'],
    ['action' => 'earned', 'amount' => '$0.0200', 'type' => 'from video ad'],
    ['action' => 'withdrawn', 'amount' => '$25.00', 'type' => 'via Bkash'],
    ['action' => 'withdrawn', 'amount' => '$5.00', 'type' => 'via Payeer'],
    ['action' => 'earned', 'amount' => '$1.0000', 'type' => 'Welcome Bonus'],
    ['action' => 'upgraded to', 'amount' => 'Starter Plan', 'type' => ''],
    ['action' => 'withdrawn', 'amount' => '$15.00', 'type' => 'via Nagad'],
    ['action' => 'earned', 'amount' => '$0.1200', 'type' => 'from gold task'],
    ['action' => 'earned', 'amount' => '$0.5000', 'type' => 'from referral commission'],
    ['action' => 'upgraded to', 'amount' => 'Pro Plan', 'type' => ''],
    ['action' => 'withdrawn', 'amount' => '$50.00', 'type' => 'via Binance (USDT)'],
    ['action' => 'earned', 'amount' => '$0.0800', 'type' => 'from survey'],
    ['action' => 'withdrawn', 'amount' => '$2.50', 'type' => 'via JazzCash'],
    ['action' => 'earned', 'amount' => '$0.0150', 'type' => 'from daily login bonus'],
    ['action' => 'withdrawn', 'amount' => '$12.00', 'type' => 'via GCash'],
    ['action' => 'earned', 'amount' => '$0.2500', 'type' => 'from sponsored task'],
    ['action' => 'upgraded to', 'amount' => 'Ultimate Plan', 'type' => ''],
    ['action' => 'withdrawn', 'amount' => '$100.00', 'type' => 'via Bank Transfer']
];

$country = $countries[array_rand($countries)];
$activity = $activities[array_rand($activities)];
$time = rand(1, 59) . " seconds ago";

header('Content-Type: application/json');
echo json_encode([
    'country' => $country,
    'action' => $activity['action'],
    'amount' => $activity['amount'],
    'type' => $activity['type'],
    'time' => $time
]);
?>
