<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('os_wechat_connect')) {$zbp->ShowError(48);die();}

$type = GetVars("type", "GET");
switch ($type) {
    case 'base':
        $save_param = array(
            "active",
            "appid",
            "appkey",
            "user_auto_create",
            "user_reg_level",
            "source_switch",
        );

        foreach ($save_param as $v) {
            $zbp->Config('os_wechat_connect')->$v = GetVars($v, "post");
        }

        $zbp->SaveConfig('os_wechat_connect');
        $zbp->SetHint('good', "保存成功");
        Redirect("./main.php");
    break;
    default:
        Redirect("./main.php");
    break;
}
