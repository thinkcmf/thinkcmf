<?php
/**
 * class LoginService, provide PreLogin, DoLogin, DoLogout methods
 */
require_once('LoginConnection.php');

/**
 * LoginService
 */
class LoginService {
    /**
     * @var string
     */
    private $loginUrl;
    
    /**
     * @var string
     */
    private $uuid;
    
    /**
     * construct
     * @param string $loginUrl
     * @param string $uuid
     */
    public function __construct($loginUrl, $uuid) {
        $this->loginUrl = $loginUrl;
        $this->uuid = $uuid;
    }
    
    /**
     * preLogin
     * @param string $userName
     * @param string $token
     * @return boolean 
     */
    public function preLogin($userName, $token,$path) {
        //echo '----------------------preLogin----------------------' . PHP_EOL;
        //echo '[notice] start preLogin!' . PHP_EOL;

        $preLogin = new LoginConnection();
        $preLogin->init($this->loginUrl);
        $preLoginData = array(
            'username' => $userName,
            'token' => $token,
            'functionName' => 'preLogin',
            'uuid' => $this->uuid,
            'request' => array(
                'osVersion' => 'windows',
                'deviceType' => 'pc',
                'clientVersion' => '1.0',
            ),
        );
        $preLogin->POST($preLoginData,$path);

        if ($preLogin->returnCode === 0) {
            $retData = gzdecode($preLogin->retData, strlen($preLogin->retData));
            $retArray = json_decode($retData, true);
            if (!isset($retArray['needAuthCode']) || $retArray['needAuthCode'] === true) {
                //echo "[error] preLogin return data format error: {$retData}" . PHP_EOL;
               // echo '--------------------preLogin End--------------------'. PHP_EOL;
                return false;
            }
            else if ($retArray['needAuthCode'] === false) {
                //echo '[notice] preLogin successfully!' . PHP_EOL;
                //echo '--------------------preLogin End--------------------' . PHP_EOL;
                return true;
            }
            else {
                echo "[error] unexpected preLogin return data: {$retData}" . PHP_EOL;
                echo '--------------------preLogin End--------------------' . PHP_EOL;
                return false;
            }
        }
        else {
            //echo "[error] preLogin unsuccessfully with return code: {$preLogin->returnCode}" . PHP_EOL;
            //echo '--------------------preLogin End--------------------' . PHP_EOL;
            return false;
        }
    }

    /**
     * doLogin
     * @param string $userName
     * @param string $password
     * @param string $token
     * @return array
     */
    public function doLogin($userName, $password, $token,$path) {
        //echo '----------------------doLogin----------------------' . PHP_EOL;
        //echo '[notice] start doLogin!' . PHP_EOL;

        $doLogin = new LoginConnection();
        $doLogin->init($this->loginUrl);
        $doLoginData = array(
            'username' => $userName,
            'token' => $token,
            'functionName' => 'doLogin',
            'uuid' => $this->uuid,
            'request' => array(
                'password' => $password,
            ),
        );
        $doLogin->POST($doLoginData,$path);

        if ($doLogin->returnCode === 0) {
            $retData =gzinflate(substr($doLogin->retData,10,-8)); ;//gzdecode($doLogin->retData, strlen($doLogin->retData));
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode']) || !isset($retArray['ucid']) || !isset($retArray['st'])) {
                echo "[error] doLogin return data format error: {$retData}" . PHP_EOL;
                echo '--------------------doLogin End--------------------' . PHP_EOL;
                return null;
            }
            else if ($retArray['retcode'] === 0) {
                //echo '[notice] doLogin successfully!' . PHP_EOL;
                //echo '--------------------doLogin End--------------------' . PHP_EOL;
                return array(
                    'ucid' => $retArray['ucid'],
                    'st' => $retArray['st'],
                );
            }
            else {
                echo "[error] doLogin unsuccessfully with retcode: {$retArray['retcode']}" . PHP_EOL;
                echo '--------------------doLogin End--------------------' . PHP_EOL;
                return null;
            }
        }
        else {
            echo "[error] doLogin unsuccessfully with return code: {$doLogin->returnCode}" . PHP_EOL;
            echo '--------------------doLogin End--------------------' . PHP_EOL;
            return null;
        }
    }

    /**
     * doLogout
     * @param string $userName
     * @param string $token
     * @param string $ucid
     * @param string $st
     * @return boolean
     */
    public function doLogout($userName, $token, $ucid, $st,$path) {
        //echo '----------------------doLogout----------------------' . PHP_EOL;
        //echo '[notice] start doLogout!' . PHP_EOL;

        $doLogout = new LoginConnection();
        $doLogout->init($this->loginUrl);
        $doLogoutData = array(
            'username' => $userName,
            'token' => $token,
            'functionName' => 'doLogout',
            'uuid' => $this->uuid,
            'request' => array(
                'ucid' => $ucid,
                'st' => $st,
            ),
        );
        $doLogout->POST($doLogoutData,$path);

        if ($doLogout->returnCode === 0) {
            $retData = gzdecode($doLogout->retData, strlen($doLogout->retData));
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode'])) {
                echo "[error] doLogout return data format error: {$retData}" . PHP_EOL;
                echo '--------------------doLogout End--------------------' . PHP_EOL;
                return false;
            }
            else if ($retArray['retcode'] === 0 ) {
                //echo '[notice] doLogout successfully!' . PHP_EOL;
                //echo '--------------------doLogout End--------------------' . PHP_EOL;
                return true;
            }
            else {
                //echo "[error] doLogout unsuccessfully with retcode: {$retArray['retcode']}" . PHP_EOL;
                //echo '--------------------doLogout End--------------------' . PHP_EOL;
                return false;
            }
        }
        else {
            echo "[error] doLogout unsuccessfully with return code: {$doLogout->returnCode}" . PHP_EOL;
            echo '--------------------doLogout End--------------------' . PHP_EOL;
            return false;
        }
    }
}
