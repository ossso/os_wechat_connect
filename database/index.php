<?php
/**
 * 数据库信息列表
 */
$os_wechat_connect_database = array(
    /**
     * 社交账户绑定表
     */
    'os_wechat_connect'      => array(
        'name'          => '%pre%os_wechat_connect',
        'info'          => array(
            'ID'          => array('third_ID','integer','',0),
            'UID'         => array('third_UID','integer','',0),
        	'Type'        => array('third_Type','integer','',0), // 0|QQ 1|wechat 2|weibo
        	'Token'       => array('third_Token','string',255,''),
        	'OpenID'      => array('third_OpenID','string',255,''),
        	'Nickname'    => array('third_Nickname','string',255,''),
        	'Avatar'      => array('third_Avatar','string',255,''),
        	'PostTime'    => array('third_PostTime','integer','', 0),
        	'UpdateTime'  => array('third_UpdateTime','integer','', 0),
        	'Other'       => array('third_Other','string','',''),
        	'Meta'        => array('third_Meta','string','',''),
        ),
    ),
);

foreach ($os_wechat_connect_database as $k => $v) {
    $table[$k] = $v['name'];
    $datainfo[$k] = $v['info'];
}
/**
 * 检查是否有创建数据库
 */
function os_wechat_connect_CreateTable() {
    global $zbp, $os_wechat_connect_database;
    foreach ($os_wechat_connect_database as $k => $v) {
        if (!$zbp->db->ExistTable($v['name'])) {
        	$s = $zbp->db->sql->CreateTable($v['name'],$v['info']);
        	$zbp->db->QueryMulit($s);
        }
    }
}
