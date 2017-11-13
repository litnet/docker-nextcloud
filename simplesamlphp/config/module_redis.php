<?php
/**
 * Configuration template for the Redis module for simpleSAMLphp
 */
$config = [
    // Predis client parameters
    'parameters' => ['tcp://redis'],

    // Predis client options
    'options' => [],

    // Key prefix
    'prefix' => 'cloud_simplesamlphp',

    // Lifitime for all non expiring keys
    'lifetime' => 7200
];
