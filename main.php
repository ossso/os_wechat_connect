<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('os_wechat_connect')) {$zbp->ShowError(48);die();}

$blogtitle='微信登录设置';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<style>
.edit-input {
    display: block;
    width: 100%;
    height: 40px;
    line-height: 24px;
    font-size: 14px;
    padding: 8px;
    box-sizing: border-box;
}
</style>
<div id="divMain">
    <div class="divHeader"><?php echo $blogtitle;?></div>
    <div class="SubMenu"><?php os_wechat_connect_SubMenu(0);?></div>
    <div id="divMain2">
        <form action="./save.php?type=base" method="post">
            <table border="1" class="tableFull tableBorder tableBorder-thcenter" style="max-width: 1000px">
                <thead>
                    <tr>
                        <th width="200px">配置名称</th>
                        <th>配置内容</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>启用开关</td>
                        <td>
                            <input name="active" type="text" class="checkbox" style="display:none;" value="<?php echo $zbp->Config('os_wechat_connect')->active; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>App Key</td>
                        <td>
                            <input name="appid" type="text" class="edit-input" value="<?php echo $zbp->Config('os_wechat_connect')->appid; ?>" placeholder="请填写微信公众号AppID" />
                        </td>
                    </tr>
                    <tr>
                        <td>App Secret</td>
                        <td>
                            <input name="appkey" type="text" class="edit-input" value="<?php echo $zbp->Config('os_wechat_connect')->appkey; ?>" placeholder="请填写微信公众号AppSecret" />
                        </td>
                    </tr>
                    <tr>
                        <td>自动生成账号</td>
                        <td>
                            <input name="user_auto_create" type="text" class="checkbox" style="display:none;" value="<?php echo $zbp->Config('os_wechat_connect')->user_auto_create; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td>自动注册身份</td>
                        <td>
                            <select name="user_reg_level" class="edit">
                                <?php
                                    $level = $zbp->Config('os_wechat_connect')->user_reg_level;
                                    if (!isset($level)) {
                                        $level = 6;
                                    }
                                    echo OutputOptionItemsOfMemberLevel($level);
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>跳转至来源页</td>
                        <td>
                            <input name="source_switch" type="text" class="checkbox" style="display:none;" value="<?php echo $zbp->Config('os_wechat_connect')->source_switch; ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" value="保存配置" style="margin: 0; font-size: 1em;" />
        </form>
        <style>
            .readme {
                max-width: 1000px;
                padding: 10px;
                margin-bottom: 10px;
                background: #f9f9f9;
            }
            .readme h3 {
                font-size: 16px;
                font-weight: normal;
                color: #000;
            }
            .readme ul li {
                margin-bottom: 5px;
                line-height: 30px;
            }
            .readme a {
                color: #333 !important;
                text-decoration: underline;
            }
            .readme code {
                display: inline-block;
                margin: 0 5px;
                padding: 0 8px;
                line-height: 25px;
                font-size: 12px;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                color: #1a1a1a;
                border-radius: 4px;
                background: #eee;
            }
            .readme code.copy {
                cursor: pointer;
            }
            .readme-item {
                -webkit-display: flex;
                display: flex;
                margin-bottom: 10px;
            }
            .readme-item .name {
                display: block;
                width: 100px;
                height: 24px;
                line-height: 24px;
            }
            .readme-item .preview {
                display: block;
                width: 300px;
            }
            .readme-item .options {
                display: block;
                width: 300px;
                height: 24px;
            }
            .readme-item .code-pre {
                display: none;
            }
            .readme-item .copy-btn {
                display: inline-block;
                width: 64px;
                height: 24px;
                margin: 0;
                margin-left: 10px;
                padding: 0;
                line-height: 24px;
                font-size: 13px;
                color: #fff;
                border: none;
                border-radius: 2px;
                background: #3a6ea5;
                cursor: pointer;
            }
            .readme-item .copy-btn:active,
            .readme-item .copy-btn:focus {
                outline: 0;
            }
            .readme-item .copy-btn:active {
                opacity: .95;
            }
        </style>
        <div class="readme">
            <h3>插件配置说明</h3>
            <ul></ul>
        </div>
        <div class="readme">
            <h3>调用内容</h3>
        </div>
    </div>
</div>
<script src="<?php echo $zbp->host ?>zb_users/plugin/os_wechat_connect/static/clipboard/clipboard-polyfill.js"></script>
<script src="<?php echo $zbp->host ?>zb_users/plugin/os_wechat_connect/static/layer/layer.js"></script>
<script>
$('.readme code.copy').on('click', function() {
    var str = $.trim($(this).html())
    str = str.replace(/&amp;/g, '&')
    clipboard.writeText(str)
    layer.msg("已复制到剪贴板")
})

$('.options input[type="checkbox"]').on('click', function() {
    var $item = $(this).parents('.readme-item')
    if (this.checked) {
        $item.find('.preview a').attr('target', '_blank')
    } else {
        $item.find('.preview a').removeAttr('target')
    }
    $item.find('.code-pre').val($item.find('.preview').html())
})

$('.options .copy-btn').on('click', function() {
    var str = $(this).parents('.readme-item').find('.code-pre').val()
    str = $.trim(str)
    clipboard.writeText(str)
    layer.msg("已复制到剪贴板")
})
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
