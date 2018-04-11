<?php
/**
 * 监听路由
 */
function os_wechat_connect_Watch($url) {
    global $zbp;
    $status = strripos($url, '/os_wechat_connect');
    if ($status == -1) {
        return false;
    }
    // 匹配路由
    $regexp = "/\/os_wechat_connect\/([a-z0-9\-\_]*)/";
    $routes = array();
    preg_match_all($regexp, $url, $routes);

    $type = null;
    if (isset($routes[1]) && count($routes[1]) > 0) {
        $type = $routes[1][0];
    }

    $status = os_wechat_connect_WatchHandler($type);

    if (!$status) return false;

    // 阻断后面内容
    $GLOBALS['hooks']['Filter_Plugin_ViewAuto_Begin']['os_wechat_connect_Watch'] = 'return';
}

/**
 * 监听cmd接口
 */
function os_wechat_connect_WatchCmd() {
    global $zbp;
    $action = GetVars('act','GET');
    if ($action != "os_wechat_connect") {
        return false;
    }

    $type = GetVars('type','GET');

    os_wechat_connect_WatchHandler($type);
}

/**
 * 处理相关事件
 */
function os_wechat_connect_WatchHandler($type) {
    global $zbp;
    switch ($type) {
        case 'login':
            include ZBP_PATH . 'zb_users/plugin/os_wechat_connect/libs/weibo_connect/index.php';
            return true;
        case 'callback':
            include ZBP_PATH . 'zb_users/plugin/os_wechat_connect/libs/weibo_connect/callback.php';
            return true;
        case 'bind':
            if ($zbp->Config('os_wechat_connect')->active == '1') {
                include ZBP_PATH . 'zb_users/plugin/os_wechat_connect/page/bind.php';
            } else {
                return false;
            }
            /**
             * 不可删除版权声明，否则视为不尊重版权，不再提供任何服务支持
             */
            echo "<!--本插件由橙色阳光提供，https://www.os369.com/-->\r\n";
            return true;
        case 'bind_account':
            os_wechat_connect_Event_ThirdBindLogin();
            return true;
        case 'create_account':
            os_wechat_connect_Event_ThirdBindCreate();
            return true;
        case 'manage':
            os_wechat_connect_Event_ManageUser();
            return true;
    }
    return false;
}

/**
 * 处理用户头像输出
 */
function os_wechat_connect_WatchAvatar($member) {
    global $zbp;
    $s = $zbp->usersdir . 'avatar/' . $member->ID . '.png';
    if (is_readable($s)) {
        return $zbp->host . 'zb_users/avatar/' . $member->ID . '.png';
    } else if ($member->Metas->os_wechat_connect_avatar) {
        return $member->Metas->os_wechat_connect_avatar;
    }
}
