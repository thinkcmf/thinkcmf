<?php
/**
 * class LoginConnection, provide POST method: send POST request to Login URL
 */
require_once('Utility.php');

/**
 * LoginConnection
 */
class LoginConnection{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $headers;

    /**
     * @var string
     */
    private $postData;

    /**
     * @var string
     */
    public $returnCode;

    /**
     * @var string
     */
    public $retData;

    /**
     * init
     * @param string $url
     */
    public function init($url) {
        $this->url = $url;
        $this->headers = array('UUID: '.UUID, 'account_type: '.ACCOUNT_TYPE, 'Content-Type:  data/gzencode and rsa public encrypt;charset=UTF-8');
    }

    /**
     * generate post data
     * @param array $data
     */
    public function genPostData($data,$path) {
        $gzData = gzencode(json_encode($data), 9);

        $rsa = new RsaPublicEncrypt($path);
        for ($index = 0, $enData = ''; $index < strlen($gzData); $index += 117) {
            $gzPackData = substr($gzData, $index, 117);
            $enData .= $rsa->pubEncrypt($gzPackData);
        }

        $this->postData = $enData;
    }

    /**
     * post
     * @param array $data
     */
    public function POST($data,$path='./') {
        $this->genPostData($data,$path);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl). PHP_EOL;
        }
        curl_close($curl);

        $this->returnCode = ord($tmpInfo[0])*64 + ord($tmpInfo[1]);

        if ($this->returnCode === 0) {
            $this->retData = substr($tmpInfo, 8);
        }
    }
}
