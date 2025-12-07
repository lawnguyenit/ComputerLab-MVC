<?php

return [
    'default' => [
        'host' => env('MQTT_HOST', 'broker.emqx.io'),
        'port' => env('MQTT_PORT', 1883),
        'client_id' => env('MQTT_CLIENT_ID', 'laravel_mqtt_client'),
        'clean_session' => env('MQTT_CLEAN_SESSION', true),
        'username' => env('MQTT_USERNAME', ''),
        'password' => env('MQTT_PASSWORD', ''),
        'connection_timeout' => env('MQTT_CONNECTION_TIMEOUT', 60),
        'keep_alive_interval' => env('MQTT_KEEP_ALIVE_INTERVAL', 60),
        'reconnect_period' => env('MQTT_RECONNECT_PERIOD', 1000),
    ],
];