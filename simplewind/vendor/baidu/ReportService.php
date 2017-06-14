<?php
/**
 * class ReportService
 */
require_once('DataApiConnection.php');

/**
 * ReportService
 */
class ReportService {
    private $apiUrl;
    private $userName;
    private $token;
    private $ucid;
    private $st;
    
    /**
     * construct
     * @param string $apiUrl
     * @param string $userName
     * @param string $token
     * @param string $ucid
     * @param string $st
     */
    public function __construct($apiUrl, $userName, $token, $ucid, $st) {
        $this->apiUrl = $apiUrl;
        $this->userName = $userName;
        $this->token = $token;
        $this->ucid = $ucid;
        $this->st = $st;
    }
    
    /**
     * get site list
     * @return array
     */
    public function getSiteList() {
        echo '----------------------get site list----------------------' . PHP_EOL;
        $apiConnection = new DataApiConnection();
        $apiConnection->init($this->apiUrl . '/getSiteList', $this->ucid);

        $apiConnectionData = array(
            'header' => array(
                'username' => $this->userName,
                'password' => $this->st,
                'token' => $this->token,
                'account_type' => ACCOUNT_TYPE,
            ),
            'body' => null,
        );
        $apiConnection->POST($apiConnectionData);
        
        return array(
            'header' => $apiConnection->retHead,
            'body' => $apiConnection->retBody,
            'raw' => $apiConnection->retRaw,
        );
    }

    /**
     * get data
     * @param array $parameters
     * @return array
     */
    public function getData($parameters) {
        //echo '----------------------get data----------------------' . PHP_EOL;
        $apiConnection = new DataApiConnection();
        $apiConnection->init($this->apiUrl . '/getData', $this->ucid);

        $apiConnectionData = array(
            'header' => array(
                'username' => $this->userName,
                'password' => $this->st,
                'token' => $this->token,
                'account_type' => ACCOUNT_TYPE,
            ),
            'body' => $parameters,
        );
        $apiConnection->POST($apiConnectionData);
        
        return array(
            'header' => $apiConnection->retHead,
            'body' => $apiConnection->retBody,
            'raw' => $apiConnection->retRaw,
        );
    }
}
