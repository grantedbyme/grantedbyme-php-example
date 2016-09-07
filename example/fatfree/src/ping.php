<?php
$f3 = require_once(__DIR__ . '/../vendor/bcosca/fatfree/lib/base.php');
$f3->set('DEBUG', 3);
$f3->set('CACHE','redis=localhost:6379/5');
$f3->route('GET @index: /ping.php',
    function() {
        echo '{"success": true}';
    }
);
$f3->run();
