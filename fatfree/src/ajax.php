<?php

// load composer class loader
require_once(__DIR__ . '/../vendor/autoload.php');

$ajax = new GrantedByMeAjax();

class GrantedByMeAjax
{
    private static $ALLOWED_OPERATIONS = array('getAccountToken', 'getAccountState', 'getSessionToken', 'getSessionState', 'getRegisterToken', 'getRegisterState');
    private static $gbm;
    private static $db;
    private static $f3;

    public static function init_sdk()
    {
        if(isset(self::$gbm)) {
            # Return GrantedByMe SDK reference
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
        self::$f3 = \Base::instance();
        self::$f3->set('DEBUG', 3);
        # http://fatfreeframework.com/cache
        # http://fatfreeframework.com/optimization#cache-engine
        self::$f3->set('CACHE','redis=localhost:6379/5');
        # http://fatfreeframework.com/databases
        # self::$db = new \DB\SQL('sqlite:' . $base_dir . 'db.sqlite');
        # http://fatfreeframework.com/session
        new Session();
        # Return GrantedByMe SDK reference
        return self::$gbm;
    }

    /**
     * Constructor
     */
    function __construct()
    {
        // get all request headers
        $headers = getallheaders();
        // get function (action)
        $operation = $_POST['operation'];
        // validate request
        if (!isset($operation)
            || !in_array($operation, self::$ALLOWED_OPERATIONS)
            || !isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'
            //|| !isset($headers['X-CSRFToken'])
            //|| !wp_verify_nonce($headers['X-CSRFToken'], 'csrf-token')
            || (($operation == 'getAccountState' || $operation == 'getSessionState' || $operation == 'getRegisterState')
                && (!isset($_POST['challenge']) || !is_string($_POST['challenge']) || empty($_POST['challenge'])))
        ) {
            header('HTTP/1.0 400 Bad Request');
            $this->gbm_error();
        }
        // call api
        if ($operation == 'getAccountToken') {
            $response = self::init_sdk()->getChallenge(\GBM\ApiRequest::$TOKEN_ACCOUNT);
            die(json_encode($response));
        } else if ($operation == 'getAccountState') {
            $this->gbm_get_account_state();
        } else if ($operation == 'getSessionToken') {
            $response = self::init_sdk()->getChallenge(\GBM\ApiRequest::$TOKEN_SESSION);
            die(json_encode($response));
        } else if ($operation == 'getSessionState') {
            $this->gbm_get_session_state();
        } else if ($operation == 'getRegisterToken') {
            $response = self::init_sdk()->getChallenge(\GBM\ApiRequest::$TOKEN_ACTIVATE);
            die(json_encode($response));
        } else if ($operation == 'getRegisterState') {
            $this->gbm_get_register_state();
        } else {
            $this->gbm_error();
        }
    }

    /**
     * TBD
     *
     * @throws \GBM\ApiRequestException
     */
    private function gbm_get_account_state()
    {
        $response = self::init_sdk()->getChallengeState($_POST['challenge']);
        if (isset($response['status']) && $response['status'] == \GBM\ApiRequest::$STATUS_VALIDATED) {
            $authenticator_secret = \GBM\ApiRequest::generateAuthenticatorSecret();
            $result = self::init_sdk()->linkAccount($_POST['challenge'], $authenticator_secret);
            if (isset($result['success']) && $result['success'] == true) {
                //
                // CHANGE BY SERVICE IMPLEMENTOR BELOW
                //
                // TODO: Link current logged in user with $authenticator_secret
            }
        }
        die(json_encode($response));
    }

    /**
     * TBD
     *
     * @throws \GBM\ApiRequestException
     */
    private function gbm_get_register_state()
    {
        $response = self::init_sdk()->getChallengeState($_POST['challenge']);
        if (isset($response['status']) && $response['status'] == \GBM\ApiRequest::$STATUS_VALIDATED) {
            $authenticator_secret = \GBM\ApiRequest::generateAuthenticatorSecret();
            $result = self::init_sdk()->linkAccount($_POST['challenge'], $authenticator_secret);
            if (isset($result['success']) && $result['success'] == true) {
                //
                // CHANGE BY SERVICE IMPLEMENTOR BELOW
                //
                // TODO: Insert new user with $data
            }
        }
        die(json_encode($response));
    }

    /**
     * TBD
     *
     * @throws \GBM\ApiRequestException
     */
    private function gbm_get_session_state()
    {
        $response = self::init_sdk()->getChallengeState($_POST['challenge']);
        if (isset($response['status']) && $response['status'] == \GBM\ApiRequest::$STATUS_VALIDATED) {
            if (isset($response['authenticator_secret'])) {
                // do not send secret to frontend
                unset($response['authenticator_secret']);
                //
                // CHANGE BY SERVICE IMPLEMENTOR BELOW
                //
                // TODO: Login user here by $response['authenticator_secret']
                self::$f3->set('SESSION.logged_in', true);
            }
        }
        die(json_encode($response));
    }

    /**
     * Generic error handler
     */
    private function gbm_error()
    {
        $response = array();
        $response['success'] = false;
        $response['error'] = 'Restricted access';
        die(json_encode($response));
    }

}
