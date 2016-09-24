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
        if (file_exists($base_dir . 'private_key.pem')) {
            $private_key = file_get_contents($base_dir . 'private_key.pem');
            $server_key = file_get_contents($base_dir . 'server_key.pem');
        } else {
            $private_key = false;
            $server_key = false;
        }
        $api_url = \GBM\ApiSettings::$HOST;
        $config = array();
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
        $response['error'] = 0;
        if(isset($_POST['signature']) && isset($_POST['payload'])) {
            // $headers = getallheaders();
            $encrypted_request = array();
            $encrypted_request['signature'] = $_POST['signature'];
            $encrypted_request['payload'] = $_POST['payload'];
            if(isset($_POST['message'])) {
                $encrypted_request['message'] = $_POST['message'];
            }
            if(isset($_POST['alg'])) {
                $encrypted_request['alg'] = $_POST['alg'];
            }
            $decrypted_request = self::init_sdk()->getCrypto()->decrypt_json($encrypted_request);
            if(isset($decrypted_request['operation'])) {
                if($decrypted_request['operation'] == 'ping') {
                    $response['success'] = true;
                } else if($decrypted_request['operation'] == 'unlink_account') {
                    // TODO: implement
                    $response['success'] = false;
                } else if($decrypted_request['operation'] == 'rekey_account') {
                    // TODO: implement
                    $response['success'] = false;
                } else if($decrypted_request['operation'] == 'revoke_challenge') {
                    // TODO: implement
                    $response['success'] = false;
                } else {
                    $response['success'] = false;
                }
                $response = self::init_sdk()->getCrypto()->encrypt_json($response);
            }
        }
        die(json_encode($response));
    }

}
