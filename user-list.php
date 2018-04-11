<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action='root';
if (!$zbp->CheckRights($action)) {$zbp->ShowError(6);die();}
if (!$zbp->CheckPlugin('os_wechat_connect')) {$zbp->ShowError(48);die();}

$blogtitle='微信登录用户列表';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';

$third_users = os_wechat_connect_Event_GetUserList();
?>
<style>
    .user-list-container {
        -webkit-display: flex;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }
    .user-list-item {
        width: 250px;
        margin-right: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        overflow: hidden;
    }
    .user-list-item .item-header {
        position: relative;
        height: 90px;
        padding: 10px;
        padding-left: 84px;
    }
    .user-list-item .avatar {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 64px;
        height: 64px;
        border-radius: 50%;
    }
    .user-list-item .avatar img {
        display: block;
        width: 100%;
        border-radius: 50%;
    }
    .user-list-item .nickname {
        height: 28px;
        line-height: 28px;
        font-size: 18px;
        font-weight: 700;
        overflow: hidden;
    }
    .user-list-item .qq-nickname {
        height: 18px;
        line-height: 18px;
        font-size: 14px;
        font-style: italic;
        overflow: hidden;
    }
    .user-list-item .update-time {
        height: 14px;
        line-height: 14px;
        font-size: 12px;
        color: #888;
    }
    .user-list-item .item-options {
        position: relative;
        height: 41px;
        border-top: 1px solid #ddd;
        background: #f5f5f5;
        -webkit-display: flex;
        display: flex;
    }
    .user-list-item .item-options .unbind-btn,
    .user-list-item .item-options .lock-login-btn {
        width: 120px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        background: transparent;
        cursor: pointer;
    }
    .user-list-item .item-options .unbind-btn {
        border-right: 1px solid #ddd;
    }

    .user-list-pagebar {
        -webkit-display: flex;
        display: flex;
    }
    .user-list-pagebar .page-item {
        display: block;
        min-width: 30px;
        height: 30px;
        padding: 0 5px;
        margin-right: 5px;
        line-height: 28px;
        font-size: 14px;
        text-align: center;
        color: #333;
        border: 1px solid #555;
    }
    .user-list-pagebar .page-item.page-now {
        color: #555;
        background: #f5f5f5;
        border: 1px solid #f5f5f5;
    }
</style>
<div id="divMain">
    <div class="divHeader"><?php echo $blogtitle;?></div>
    <div class="SubMenu"><?php os_wechat_connect_SubMenu(1);?></div>
    <div id="divMain2">
        <div class="user-list-container">
            <?php
                foreach ($third_users['list'] as $item) {
            ?>
            <div class="user-list-item" data-id="<?php echo $item->ID ?>">
                <div class="item-header">
                    <div class="avatar">
                        <img src="<?php echo $item->Avatar ?>" />
                    </div>
                    <div class="nickname"><?php echo $item->User->StaticName ?></div>
                    <div class="qq-nickname" title="微博昵称"><?php echo $item->Nickname ?></div>
                    <div class="update-time">最后登录<?php echo os_wechat_connect_AgoTime($item->UpdateTime) ?></div>
                </div>
                <div class="item-options">
                    <span class="unbind-btn" data-id="<?php echo $item->ID ?>">解除绑定</span>
                    <?php if ($item->User->Status == 0) { ?>
                    <span class="lock-login-btn" data-id="<?php echo $item->ID ?>">限制登录</span>
                    <?php } else { ?>
                    <span class="lock-login-btn" data-id="<?php echo $item->ID ?>">恢复登录</span>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="user-list-pagebar">
            <?php
                if ($third_users['pagebar']->PageAll > 0) {
                    foreach ($third_users['pagebar']->buttons as $k => $v) {
                        $pagenum = (int) $k;
                        if ($pagenum > 0 && $k != $third_users['pagebar']->PageNow) {
                            echo '<a class="page-item" href="'.$v.'">'.$k.'</a>';
                        } else if ($pagenum > 0) {
                            echo '<span class="page-item page-now">'.$k.'</span>';
                        }
                    }
                }
            ?>
        </div>
    </div>
</div>
<script src="<?php echo $zbp->host ?>zb_users/plugin/os_wechat_connect/static/layer/layer.js"></script>
<script>
!function() {
    var option = function(type, id, cb) {
        $.ajax({
            type: "post",
            url: "<?php echo os_wechat_connect_Event_GetURL('manage') ?>",
            data: {
                type: type,
                id: id
            },
            dataType: "json",
            success: function(res) {
                if (res.code == 100000) {
                    layer.msg("操作成功")
                    cb && cb(null, res)
                } else {
                    layer.msg(res.message)
                    cb && cb({
                        code: res.code,
                        msg: res.message,
                    })
                }
            },
            error: function(e) {
                layer.msg("网络异常，Netword Code:"+e.status)
                cb && cb({
                    code: -1,
                    msg: "网络异常",
                })
            }
        })
    }

    $('.unbind-btn').on('click', function() {
        var id = $(this).attr("data-id");
        var $item = $(this).parents('.user-list-item')
        layer.open({
            title: "提示",
            content: "确认解除绑定？解除后无法恢复",
            btn: ["确定", "取消"],
            yes: function(index) {
                layer.close(index)
                option("unbind", id, function(err) {
                    if (!err) {
                        $item.remove()
                    }
                })
            }
        })
    })

    $('.lock-login-btn').on('click', function() {
        var id = $(this).attr("data-id");
        var $node = $(this)
        option("lock", id, function(err, res) {
            if (!err) {
                if (res.result == "1") {
                    $node.html("解除限制")
                } else {
                    $node.html("限制登录")
                }
            }
        })
    })
}();
</script>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
