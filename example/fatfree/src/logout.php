<?php
$f3 = require_once(__DIR__ . '/../vendor/bcosca/fatfree/lib/base.php');
$f3->set('DEBUG', 3);
$f3->set('CACHE','redis=localhost:6379/5');

new Session();
$f3->set('SESSION.logged_in', false);
$f3->reroute('/login.php');
$f3->run();
