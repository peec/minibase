<?php
require __DIR__ . '/vendor/autoload.php';

$mb = Minibase\MB::cli()->loadConfigFile(__DIR__ . '/app/app.json', __DIR__ . '/app');
$mb->start();
