<?php
$headers = [
    'X-Auth-Email: CloudFlare-Email-Account',
    'X-Auth-Key: CloudFlare-API-Key',
    'Content-Type: application/json'
];

$domain = "Root-Domain";
$record = "Domain Record";
$ip = file_get_contents('https://api.ipify.org');

$data = [
    'type' => 'A',
    'name' => $record,
    'content' => $ip,
    'proxied' => true
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones?name=$domain");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    exit('Error: ' . curl_error($ch));
}
curl_close ($ch);

$json = json_decode($result, true);

$ZoneID = $json['result']['0']['id'];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$ZoneID/dns_records?name=$record");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    exit('Error: ' . curl_error($ch));
}
curl_close ($ch);

$json = json_decode($result, true);

$DNSID = $json['result']['0']['id'];

$old_ip = $json['result']['0']['content'];

if ($old_ip === $ip) {
    echo "CloudFlare IP: $old_ip" . PHP_EOL;
    echo "Current IP: $ip" . PHP_EOL;
    echo "The IP doesn't have to be changed!" . PHP_EOL;
}
else {
    echo "CloudFlare IP: $old_ip" . PHP_EOL;
    echo "Current IP: $ip" . PHP_EOL;
    echo "The IP has to be changed!" . PHP_EOL;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.cloudflare.com/client/v4/zones/$ZoneID/dns_records/$DNSID");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        exit('Error: ' . curl_error($ch));
    }
    echo "The IP has changed from $old_ip to $ip!" . PHP_EOL;
}
