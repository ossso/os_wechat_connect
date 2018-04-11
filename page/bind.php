<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge,chrome=1" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0"/>
    <meta name="renderer" content="webkit" />
    <meta name="robots" content="none" />
    <link rel="stylesheet" href="<?php echo $zbp->host ?>zb_users/plugin/os_wechat_connect/static/bind.css" type="text/css" />
	<script src="<?php echo $zbp->host ?>zb_system/script/jquery-2.2.4.min.js" type="text/javascript"></script>
	<script src="<?php echo $zbp->host ?>zb_system/script/zblogphp.js" type="text/javascript"></script>
	<script src="<?php echo $zbp->host ?>zb_system/script/c_html_js_add.php" type="text/javascript"></script>
    <script src="<?php echo $zbp->host ?>zb_system/script/md5.js" type="text/javascript"></script>
    <title>微信登录用户绑定 - <?php echo $zbp->name ?></title>
</head>
<body>
<div class="bg">
    <span class="logo"><?php echo $zbp->name ?></span>
</div>
<div class="login-group">
    <h1>绑定网站用户</h1>
    <form id="login-form" action="<?php echo os_wechat_connect_Event_GetURL('bind-account') ?>" onsubmit="return false">
        <div class="login-item">
            <label for="username">账号</label>
            <input type="text" name="username" id="username" class="login-item-input" />
        </div>
        <div class="login-item">
            <label for="password">密码</label>
            <input type="password" name="password" id="password" class="login-item-input" />
        </div>
        <button class="submit-btn">登录并绑定</button>
    </form>
    <?php
        if ($zbp->Config('os_wechat_connect')->user_auto_create == "1") {
    ?>
    <div class="login-account-create">
        还没有账户？点这儿<a href="<?php echo os_wechat_connect_Event_GetURL('create-account') ?>" class="login-create">生成账户</a>
    </div>
    <?php } ?>
</div>
<script src="<?php echo $zbp->host ?>zb_users/plugin/os_wechat_connect/static/layer/layer.js" type="text/javascript"></script>
<script>
!function() {
    var $form = $('#login-form');
    $form.on("submit", function() {
        var username = $('#username').val()
        var password = $('#password').val()
        if (!username.length) {
            layer.msg("请输入用户名");
            return this;
        } else if (password.length < 8) {
            layer.msg("请输入正确的密码");
            return this;
        }

        $.ajax({
            type: "post",
            url: $form.attr("action"),
            data: {
                username: username,
                password: MD5(password)
            },
            dataType: "json",
            success: function(res) {
                if (res.code == 100000) {
                    layer.open({
                        title: "提示",
                        content: "绑定成功",
                        yes: function() {
                            success()
                        },
                        end: function() {
                            success()
                        },
                        time: 3000
                    })
                } else {
                    layer.msg(res.message)
                }
            },
            error: function() {},
            complete: function() {
                layer.closeAll('loading')
            }
        })
    });

    var success = function() {
        var url = "<?php echo $zbp->host ?>";
        var sourceUrl = zbp.cookie.get('sourceUrl');
        window.location.href = sourceUrl?sourceUrl:url;
    }
}();
</script>
</body>
</html>
