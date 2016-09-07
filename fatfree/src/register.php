<?php
$f3 = require_once(__DIR__ . '/../vendor/bcosca/fatfree/lib/base.php');
$f3->set('DEBUG', 3);
$f3->set('CACHE','redis=localhost:6379/5');
$f3->route('GET @register: /register.php',
    function() {
        include('templates/register.html');
    }
);
new Session();
if($f3->get('SESSION.logged_in')) {
    $f3->reroute('/');
}
$f3->run();
