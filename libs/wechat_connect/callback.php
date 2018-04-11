<?php
session_start();

include 'wechat.connect.class.php';

$appid = $zbp->Config('os_wechat_connect')->appid;
$secret = $zbp->Config('os_wechat_connect')->appkey;

$o = new WechatConnect($appid, $secret);

// code换算token
if (isset($_REQUEST['code'])) {
	$state = $_REQUEST['state'];
	$code = $_REQUEST['code'];
	$result = $o->getAccessToken($state, $code);
} else {
	echo '系统异常 1';
	exit;
}

if ($result) {
	$_SESSION['__wechat_connect_result'] = $result;
} else {
	echo '系统异常 2';
	exit;
}

// 第一步 查询绑定状态
$status = os_wechat_connect_Event_GetThirdInfo($result['openid']);
// 已绑定
if ($status) {
    // 执行第三方登录
    os_wechat_connect_Event_ThirdLogin($result['openid'], $result['access_token'], $o);
} else {
    // 未绑定 再判断是否登录 如果登录就直接绑定
    if ($zbp->user->ID > 0) {
        // 执行绑定方法
        os_wechat_connect_Event_ThirdBind($result['openid'], $result['access_token'], $o);
    } else {
        if (!session_id()) {
            session_start();
        }
        Redirect(os_wechat_connect_Event_GetURL('bind'));
    }
}

// 方法执行完毕后 回到对应页面
$sourceUrl = GetVars('sourceUrl', 'COOKIE');
if (empty($sourceUrl)) {
    $sourceUrl = $zbp->host;
}
Redirect($sourceUrl);
