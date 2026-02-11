<?php declare(strict_types=1);

return [
    'host' => env('MQTT_HOST', 'broker.hivemq.com'),
    'port' => (int) env('MQTT_PORT', 8883),
    'scheme' => env('MQTT_SCHEME', 'tls'),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
];
