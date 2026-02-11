<?php declare(strict_types=1);

return [
    'broker' => env('MQTT_BROKER', 'broker.hivemq.com'),
    'port' => (int) env('MQTT_PORT', 8883),
    'use_tls' => (bool) env('MQTT_USE_TLS', true),
];
