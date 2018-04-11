<?php
include_once __DIR__.'/database/index.php';
include_once __DIR__.'/function/main.php';
#注册插件
RegisterPlugin("os_wechat_connect","ActivePlugin_os_wechat_connect");

/**
 * 注册接收处理指令
 */
$GLOBALS['actions']['os_wechat_connect'] = 6;
function ActivePlugin_os_wechat_connect() {
    Add_Filter_Plugin('Filter_Plugin_ViewAuto_Begin','os_wechat_connect_Watch');
    Add_Filter_Plugin('Filter_Plugin_Cmd_Begin','os_wechat_connect_WatchCmd');
    Add_Filter_Plugin("Filter_Plugin_Mebmer_Avatar","os_wechat_connect_WatchAvatar");
    Add_Filter_Plugin('Filter_Plugin_Html_Js_Add', 'os_wechat_connect_Event_FrontOutput');
}

function os_wechat_connect_SubMenu($id){
	$arySubMenu = array(
		0 => array('应用设置', 'main', 'left', false),
		1 => array('用户列表', 'user-list', 'left', false),
	);

	foreach($arySubMenu as $k => $v){
		echo '<a href="./'.$v[1].'.php" '.($v[3]==true?'target="_blank"':'').'><span class="m-'.$v[2].' '.($id==$k?'m-now':'').'">'.$v[0].'</span></a>';
	}
}

function InstallPlugin_os_wechat_connect() {
    os_wechat_connect_CreateTable();
}

function UninstallPlugin_os_wechat_connect() {}

/**
 * 返回时间天数
 */
function os_wechat_connect_AgoTime($ptime) {
    // $ptime = strtotime($ptime);
    $etime = time() - $ptime;
    if($etime < 10) return '刚刚';
    $nowYear = date('Y');
    $setYear = date('Y',$ptime);
    if ($nowYear != $setYear) {
        return date('Y/m/d H:i', $ptime);
    }
    $nowMonth = date('m');
    $setMonth = date('m',$ptime);
    if ($nowMonth != $setMonth) {
        return date('m/d H:i', $ptime);
    }
    $interval = array (
        24 * 60 * 60            =>  '天前',
        60 * 60                 =>  '小时前',
        60                      =>  '分钟前',
        1                       =>  '秒前'
    );
    foreach ($interval as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . $str;
        }
    };
}
