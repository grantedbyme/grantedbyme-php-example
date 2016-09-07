<?php
$f3 = require_once(__DIR__ . '/../vendor/bcosca/fatfree/lib/base.php');
$f3->set('DEBUG', 3);
$f3->set('CACHE','redis=localhost:6379/5');
$f3->route('GET @account: /account.php',
    function() {
        include('templates/account.html');
    }
);
new Session();
if(!$f3->get('SESSION.logged_in')) {
    $f3->reroute('login.php');
}
$f3->run();
