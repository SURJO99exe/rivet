<?php
require_once __DIR__ . '/../config/config.php';

class Adsterra {
    private $api_key;
    private $base_url = "https://publishers.adsterra.com/api/v1.0/";

    public function __construct($pdo) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'adsterra_api_key'");
        $stmt->execute();
        $this->api_key = $stmt->fetchColumn();
    }

    public function getStats() {
        if (empty($this->api_key)) return [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . "stats.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Key: " . $this->api_key]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) return ['error' => 'API returned status ' . $http_code];

        return json_decode($response, true);
    }

    public function getPlacements() {
        if (empty($this->api_key)) return [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . "placements.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Key: " . $this->api_key]);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

$adsterra = new Adsterra($pdo);
?>
