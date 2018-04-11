<?php
if (empty($zbp)) {
    echo '管理员已经关闭了登录';
    die();
} else if ($zbp->Config('os_wechat_connect')->active != '1') {
    echo '管理员已经关闭了微信登录';
    die();
}

session_start();

include 'wechat.connect.class.php';

$appid = $zbp->Config('os_wechat_connect')->appid;
$secret = $zbp->Config('os_wechat_connect')->appkey;

$o = new WechatConnect($appid, $secret);

$code_url = $o->getAuthorizeURL(os_wechat_connect_Event_GetURL('callback'));

// 转向到微信登录
Redirect($code_url);
