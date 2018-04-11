<?php
/**
 * 获取链接地址
 */
function os_wechat_connect_Event_GetURL($type) {
    global $zbp;

    if ($zbp->option['ZC_STATIC_MODE'] == 'REWRITE') {
        $third_url = $zbp->host . 'os_wechat_connect/';
    } else {
        $third_url = $zbp->host . 'zb_system/cmd.php?act=os_wechat_connect&type=';
    }

    switch ($type) {
        case 'login':
            $third_url .= 'login';
        break;
        case 'callback':
            $third_url .= 'callback';
        break;
        case 'bind':
            $third_url .= 'bind';
        break;
        case 'bind-account':
            $third_url .= 'bind_account';
        break;
        case 'create-account':
            $third_url .= 'create_account';
        break;
        case 'manage':
            $third_url .= 'manage';
        break;
    }

    return $third_url;
}

/**
 * 社交账户绑定
 */
function os_wechat_connect_Event_ThirdBind($openid, $token, $o) {
    global $zbp;

    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }

    $t = new OS_Wechat_Connect;
    $t->Type = 1;
    $t->OpenID = $openid;
    $t->Token = $token;
    $t->UID = $zbp->user->ID;
    $t->Save();

    os_wechat_connect_Event_ThirdSyncInfo($openid, $token, $o);

    return true;
}

/**
 * 查询是否绑定
 */
function os_wechat_connect_Event_GetThirdInfo($openid) {
    global $zbp;

    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }

    $t = new OS_Wechat_Connect;
    $status = $t->LoadInfoByOpenID($openid, 1);
    if (!$status) {
        return false;
    }

    $m = new Member;
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        return false;
    }

    return true;
}

/**
 * os_wechat_connect_Event_ThirdSyncInfo
 * 同步用户的微博信息回来
 */
function os_wechat_connect_Event_ThirdSyncInfo($uid, $token, $o) {
    global $zbp;

    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }

    $t = new OS_Wechat_Connect;
    $status = $t->LoadInfoByOpenID($uid, 1);
    if (!$status) {
        return false;
    }

    $result = $o->getUserinfo();

    if (!empty($result->errcode)) {
        return false;
    }

    // 保存资料
    $t->Nickname = $result['nickname'];
    $t->Avatar = $result['headimgurl'];
    $t->Save();

    // 确认是否需要同步资料
    $m = new Member();
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        return false;
    }
    $update_status = false;
    // 同步头像 -> Weibo
    $m->Metas->os_wechat_connect_avatar = $t->Avatar;
    // 判断用户是否需要同步昵称
    if ($m->Metas->os_wechat_connect_third_info == '1') {
        $m->Alias = $t->Nickname;
        $m->Metas->Del('os_wechat_connect_third_info');
    }
    $m->Save();
    return true;
}

/**
 * 第三方的登录方法
 */
function os_wechat_connect_Event_ThirdLogin($openid, $token, $o = null) {
    global $zbp;

    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }

    $t = new OS_Wechat_Connect;
    $status = $t->LoadInfoByOpenID($openid, 0);
    if (!$status) {
        echo 'Login Error 1, 登录异常';
        exit;
    }

    $m = new Member;
    $status = $m->LoadInfoByID($t->UID);
    if (!$status) {
        echo 'Login Error 2, 登录异常';
        exit;
    }

    // 将用户信息载入$zbp中
    $zbp->user = $m;
    $un = $m->Name;
    $ps = $m->PassWord_MD5Path;

    $sdt = 0;
    $addinfo = array();
    $addinfo['chkadmin'] = (int) $zbp->CheckRights('admin');
    $addinfo['chkarticle'] = (int) $zbp->CheckRights('ArticleEdt');
    $addinfo['levelname'] = $m->LevelName;
    $addinfo['userid'] = $m->ID;
    $addinfo['useralias'] = $m->StaticName;
    if(HTTP_SCHEME == 'https://'){
        setcookie("username", $un, $sdt, $zbp->cookiespath, '', true, false);
        setcookie("password", $ps, $sdt, $zbp->cookiespath, '', true, true);
        setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath, '', true, false);
    } else {
        setcookie("username", $un, $sdt, $zbp->cookiespath);
        setcookie("password", $ps, $sdt, $zbp->cookiespath);
        setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath);
    }

    // 挂载上接口 会传入third
    if(isset($GLOBALS['hooks']['Filter_Plugin_VerifyLogin_Succeed'])){
        foreach ($GLOBALS['hooks']['Filter_Plugin_VerifyLogin_Succeed'] as $fpname => &$fpsignal) {
            $fpname('third');
        }
    }

    os_wechat_connect_Event_ThirdSyncInfo($openid, $token, $o);

    return true;
}

/**
 * os_wechat_connect_Event_GetUserThird 社交信息查询
 */
function os_wechat_connect_Event_GetUserThird($uid = false) {
    global $zbp;
    $w = array();
    $w[] = array('=', 'third_Type', 1);
    if (!$uid) {
        $uid = $zbp->user->ID;
    }
    $w[] = array('=','third_UID', $uid);
    $sql = $zbp->db->sql->Select($zbp->table['os_wechat_connect'], '*', $w);
    $result = $zbp->GetListType('os_wechat_connect', $sql);
    return $result;
}

/**
 * 第三方绑定登录
 */
function os_wechat_connect_Event_ThirdBindLogin() {
    global $zbp;
    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }
    $json = array();
    $username = trim(GetVars("username", "POST"));
    $password = trim(GetVars("password", "POST"));
    if ($zbp->Verify_MD5(GetVars('username', 'POST'), GetVars('password', 'POST'), $m)) {
        $zbp->user = $m;
        $un = $m->Name;
        $ps = $m->PassWord_MD5Path;
        if ($zbp->user->Status != 0) {
            $json['code'] = 200100;
            $json['message'] = "已被限制登录";
        } else {

            $sdt = 0;
            $addinfo = array();
            $addinfo['chkadmin'] = (int) $zbp->CheckRights('admin');
            $addinfo['chkarticle'] = (int) $zbp->CheckRights('ArticleEdt');
            $addinfo['levelname'] = $m->LevelName;
            $addinfo['userid'] = $m->ID;
            $addinfo['useralias'] = $m->StaticName;
            if(HTTP_SCHEME == 'https://'){
                setcookie("username", $un, $sdt, $zbp->cookiespath, '', true, false);
                setcookie("password", $ps, $sdt, $zbp->cookiespath, '', true, true);
                setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath, '', true, false);
            } else {
                setcookie("username", $un, $sdt, $zbp->cookiespath);
                setcookie("password", $ps, $sdt, $zbp->cookiespath);
                setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath);
            }

            if (!session_id()) {
                session_start();
            }
            $access_token = $_SESSION['__wechat_connect_result']['access_token']; // 用户识别
            $openid = $_SESSION['__wechat_connect_result']['openid']; // 用户ID
            if (empty($openid) || empty($access_token)) {
                $json['code'] = 200101;
                $json['message'] = "绑定失败，授权信息遗失";
            } else {
                // 执行绑定
                include ZBP_PATH . 'zb_users/plugin/os_wechat_connect/libs/wechat_connect/wechat.connect.class.php';
                $appid = $zbp->Config('os_wechat_connect')->appid;
                $secret = $zbp->Config('os_wechat_connect')->appkey;
                $o = new WechatConnect($appid, $secret, $access_token, $openid);
                os_wechat_connect_Event_ThirdBind($openid, $access_token, $o);
                $json['code'] = 100000;
                $json['message'] = "绑定成功";
            }
        }
    } else {
        $json['code'] = 200000;
        $json['message'] = "用户名或密码错误";
    }

    echo json_encode($json);
    exit;
}

/**
 * 绑定自动生成的账户
 */
function os_wechat_connect_Event_ThirdBindCreate() {
    global $zbp;
    if ($zbp->Config('os_wechat_connect')->active != "1") {
        return false;
    }
    if ($zbp->Config('os_wechat_connect')->user_auto_create != "1") {
        return false;
    }
    if (!session_id()) {
        session_start();
    }
    $access_token = $_SESSION['__wechat_connect_result']['access_token']; // 用户识别
    $openid = $_SESSION['__wechat_connect_result']['openid']; // 用户ID
    if (empty($openid) || empty($access_token)) {
        return false;
    }
    // 生成唯一Name
    $md5ID = md5($openid.time());
    $md5ID = substr($md5ID, 8, 16);

    $level = 6;
    if ($zbp->Config('os_wechat_connect')->user_reg_level) {
        $level = $zbp->Config('os_wechat_connect')->user_reg_level;
    }

    $mem = new Member;
    $mem->Name = "third_weibo_".$md5ID;
    $mem->Level = $level;
    $mem->IP = GetGuestIP();
    $mem->Guid = GetGuid();
    $mem->PostTime = time();
    $mem->Password = Member::GetPassWordByGuid($access_token, $mem->Guid);
    // 自动同步昵称
    $mem->Metas->os_wechat_connect_third_info = "1";
    $mem->Save();

    CountMember($mem, array(null, null, null, null));

    $zbp->user = $mem;
    $un = $mem->Name;
    $ps = $mem->PassWord_MD5Path;

    $sdt = 0;
    $addinfo = array();
    $addinfo['chkadmin'] = (int) $zbp->CheckRights('admin');
    $addinfo['chkarticle'] = (int) $zbp->CheckRights('ArticleEdt');
    $addinfo['levelname'] = $mem->LevelName;
    $addinfo['userid'] = $mem->ID;
    $addinfo['useralias'] = $mem->StaticName;
    if(HTTP_SCHEME == 'https://'){
        setcookie("username", $un, $sdt, $zbp->cookiespath, '', true, false);
        setcookie("password", $ps, $sdt, $zbp->cookiespath, '', true, true);
        setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath, '', true, false);
    } else {
        setcookie("username", $un, $sdt, $zbp->cookiespath);
        setcookie("password", $ps, $sdt, $zbp->cookiespath);
        setcookie("addinfo" . str_replace('/', '', $zbp->cookiespath), json_encode($addinfo), $sdt, $zbp->cookiespath);
    }

    // 执行绑定
    include ZBP_PATH . 'zb_users/plugin/os_wechat_connect/libs/wechat_connect/wechat.connect.class.php';
    $appid = $zbp->Config('os_wechat_connect')->appid;
    $secret = $zbp->Config('os_wechat_connect')->appkey;
    $o = new WechatConnect($appid, $secret, $access_token, $openid);
    os_wechat_connect_Event_ThirdBind($openid, $access_token, $o);

    // 方法执行完毕后 回到对应页面
    $sourceUrl = GetVars('sourceUrl', 'COOKIE');
    if (empty($sourceUrl)) {
        $sourceUrl = $zbp->host;
    }
    Redirect($sourceUrl);
}

/**
 * 显示绑定用户列表
 */
function os_wechat_connect_Event_GetUserList() {
    global $zbp;
    $page = GetVars("page", "GET");
    $page = (int)$page>0?(int)$page:1;
    $pagebar = new Pagebar('{%host%}zb_users/plugin/os_wechat_connect/user-list.php?page={%page%}', false);
    $pagebar->PageCount = 20;
    $pagebar->PageNow = $page;
    $pagebar->PageBarCount = $zbp->pagebarcount;
    $pagebar->UrlRule->Rules['{%page%}'] = $page;

    $w = array();
    $w = array("=", "third_Type", "1");

    $limit = array(($pagebar->PageNow - 1) * $pagebar->PageCount, $pagebar->PageCount);
    $option = array('pagebar' => $pagebar);

    $sql = $zbp->db->sql->Select(
        $zbp->table['os_wechat_connect'],
        array("*"),
        $w,
        null,
        $limit,
        $option
    );
    $result = $zbp->GetListType('os_wechat_connect', $sql);

    return array(
        "list"     => $result,
        "pagebar"  => $pagebar,
    );
}

/**
 * 管理操作
 */
function os_wechat_connect_Event_ManageUser() {
    global $zbp;
    $json = array();

    if ($zbp->user->Level > 1) {
        $json['code'] = 200200;
        $json['message'] = "您的权限不足";
        echo json_encode($json);
        exit;
    }

    $id = GetVars('id', "POST");
    $type = GetVars('type', "POST");
    $t = new OS_Wechat_Connect;
    $t->LoadInfoByID($id);

    if ($type == "unbind") {
        $t->Del();
        $json['code'] = 100000;
        $json['message'] = "操作成功";
    } elseif ($type == "lock") {
        $t->User->Status = $t->User->Status==1?0:1;
        $t->User->Save();
        $json['code'] = 100000;
        $json['message'] = "操作成功";
        $json['result'] = $t->User->Status;
    }

    echo json_encode($json);
    exit;
}


/**
 * 前台插入cookie来源
 */
function os_wechat_connect_Event_FrontOutput() {
    global $zbp;
    if ($zbp->Config('os_wechat_connect')->source_switch != "1") {
        return null;
    }
    echo "\r\n".'!function() {$(document).on("click", ".os-wechat-connect-link", function() { zbp.cookie.set("sourceUrl", window.location.href); })}();'."\r\n";
}
