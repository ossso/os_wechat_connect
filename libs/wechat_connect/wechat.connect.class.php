<?php
/**
 * 微信登录类
 * Author By 橙色阳光
 * 瞎几把写的一个微信互联类
 */
class WechatConnect {
    // APPID
    $appid = '';
    // Secret
    $secret = '';
    // access_token
    $access_token = '';
    // openid
    $openid = '';

    function __construct($appid, $secret, $access_token = '', $openid = '') {
        $this->appid = $appid;
        $this->secret = $secret;

        if (empty($appid) || empty($secret)) {
            $this->showError("-1", "请设置appid和secret");
        }

        $this->access_token = $access_token;
        $this->openid = $openid;
    }

    function showError($code = -1, $msg = "") {
        echo "<meta charset=\"UTF-8\">";
        echo "<h3>error:</h3>$code";
        echo "<h3>msg  :</h3>$msg";
        exit();
    }

    /**
     * 区分设备返回地址
     */
    function authorizeURL() {
        $regex = '/android|adr|iphone|windows\sphone|kindle|gt\-p|gt\-n|rim\stablet|opera|meego/i';
        $ua = GetVars('HTTP_USER_AGENT', 'SERVER');
        if (preg_match($regex, $ua)) {
            return "https://open.weixin.qq.com/connect/oauth2/authorize";
        }
        return "https://open.weixin.qq.com/connect/qrconnect";
    }

    /**
     * 获取授权链接
     */
    function getAuthorizeURL($callback, $response_type = 'code', $type = '0') {
        $state = md5(uniqid(rand(), TRUE));
        if (!session_id()) {
            session_start();
        }
        $_SESSION['__WechatConnect_state'] = $state;
        $params = array();
        $params['appid'] = $this->appid;
        $params['redirect_uri'] = urlencode($callback);
        $params['response_type'] = $response_type;
        if ($type == "1") {
            $params['scope'] = "snsapi_userinfo";
        } else {
            $params['scope'] = "snsapi_base";
        }
        $params['state'] = $state;

        return $this->authorizeURL() . '?' . http_build_query($params) . "#wechat_redirect";
    }

    /**
     * 获取AccessToken的链接
     */
    function getAccessTokenURL($code) {
        $params = array();
        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;
        $params['code'] = $code;
        $params['grant_type'] = "authorization_code";

        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . http_build_query($params);
    }

    /**
     * 获取用户信息的链接
     */
    function getUserinfoURL($openid, $access_token) {
        if (empty($openid) || empty($access_token)) {
            $this->showError("200100", "授权的验证信息异常");
        }

        $params = array();
        $params['access_token'] = $access_token;
        $params['openid'] = $openid;
        $params['lang'] = "zh_CN";

        return "https://api.weixin.qq.com/sns/userinfo?" . http_build_query($params);
    }

    /**
     * 获取AccessToken和openid
     */
    function getAccessToken($state, $code) {
        try {
            if (!empty($_SESSION['__WechatConnect_state']) && $_SESSION['__WechatConnect_state'] == $state) {
                $this->showError("200000", "state验证不通过");
            }
        } catch (Exception $e) {
            $this->showError("200001", "state验证不通过");
        }

        unset($_SESSION['__WechatConnect_state']);

        $response = $this->http($this->getAccessTokenURL($code));
        $response = json_decode($response, true);
        if (is_array($response) && !isset($response['error'])) {
            $this->access_token = $response['access_token'];
            $this->openid = $response['openid'];
            return $response;
        } else {
            $this->showError("200002", "获取AccessToken失败");
        }
    }

    /**
     * 获取用户信息
     */
    function getUserinfo() {
        $response = $this->http($this->getUserinfoURL($this->openid, $this->access_token));
        $response = json_decode($response);
        return $response;
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    function http($url, $method = NULL, $postfields = NULL, $headers = array()) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        if (version_compare(phpversion(), '5.4.0', '<')) {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
        } else {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
            break;
        }

        $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];

        curl_setopt($ci, CURLOPT_URL, $url );
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            print_r($headers);

            echo '=====request info====='."\r\n";
            print_r( curl_getinfo($ci) );

            echo '=====response====='."\r\n";
            print_r( $response );
        }
        curl_close ($ci);
        return $response;
    }
}
