<?php
/**
 * 微信登录控制类
 */
class OS_Wechat_Connect extends Base {
    public function __construct() {
        global $zbp;
        parent::__construct($zbp->table['os_wechat_connect'], $zbp->datainfo['os_wechat_connect'], __CLASS__);

        $this->PostTime = time();
        $this->UpdateTime = time();
    }

    /**
     * @param $name
     * @return array|int|mixed|null|string
     */
    public function __get($name) {
        global $zbp;
        if ($name == 'User') {
            $m = $zbp->GetMemberByID($this->UID);
            return $m;
        }
        return parent::__get($name);
    }

    /**
     * @param string $s
     * @return bool|string
     */
    public function Time($s = 'Y-m-d H:i:s') {
        return date($s, (int) $this->AddTime);
    }

    /**
     * 获取数据库内指定OPENID的数据
     * @param int $openid 指定ID
     * @param int $type 社交类型
     * @return bool
     */
    public function LoadInfoByOpenID($openid, $type) {

        $s = $this->db->sql->Select($this->table, array('*'), array(array('=', 'third_OpenID', $openid), array('=', 'third_Type', $type)), null, null, null);

        $array = $this->db->Query($s);
        if (count($array) > 0) {
            $this->LoadInfoByAssoc($array[0]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取数据库内指定UID的数据
     * @param int $uid 指定ID
     * @param int $type 社交类型
     * @return bool
     */
    public function LoadInfoByUID($uid, $type) {

        $s = $this->db->sql->Select($this->table, array('*'), array(array('=', 'third_UID', $uid), array('=', 'third_Type', $type)), null, null, null);

        $array = $this->db->Query($s);
        if (count($array) > 0) {
            $this->LoadInfoByAssoc($array[0]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function Save() {
        global $zbp;
        $this->UpdateTime = time();
        return parent::Save();
    }
}
