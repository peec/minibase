<?php
// Should not be in public dir.
// This is the console. Try running "php cli.php".

// Require the app so we get the $app instance.
require __DIR__ . '/configure.php';

// Creates a new app object.
$mb = Minibase\MB::cli();
configureMBApp($mb);

$mb->start();