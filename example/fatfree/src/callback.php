<?php

// load composer class loader
require_once(__DIR__ . '/../vendor/autoload.php');
$f3 = \Base::instance();
$f3->set('DEBUG', 3);
$f3->set('CACHE','redis=localhost:6379/5');

$callback = new GrantedByMeCallback();

class GrantedByMeCallback
{
    private static $ALLOWED_FUNCTIONS = array('');
    private static $gbm;

    public static function init_sdk()
    {
        if(isset(self::$gbm)) {
            return self::$gbm;
        }
        $base_dir = __DIR__ . '/../data/';
        if (file_exists($base_dir . 'service_key.pem')) {
            // TODO: load your private pem, the server public key and the service API key
            $private_key = file_get_contents($base_dir . 'private_key.pem');
            $server_key = file_get_contents($base_dir . 'server_key.pem');
            $service_key = file_get_contents($base_dir . 'service_key.pem');
        } else {
            $private_key = false;
            $server_key = false;
            $service_key = false;
        }
        $api_url = \GBM\ApiSettings::$HOST;
        $config = array();
        $config['service_key'] = $service_key;
        $config['private_key'] = $private_key;
        $config['public_key'] = $server_key;
        $config['api_url'] = $api_url;
        self::$gbm = new \GBM\ApiRequest($config);
        return self::$gbm;
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $response = array();
        $response['success'] = false;
        $response['error'] = 'Restricted access';
        if(isset($_POST['signature']) && isset($_POST['payload'])) {
            self::init_sdk();
            $headers = getallheaders();
            $signature = $_POST['signature'];
            $payload = $_POST['payload'];
            $message = $_POST['message'];
        }
        die(json_encode($response));
    }

}
