<?php

class LoginController extends \Base\ApplicationController {

    public function before() {
        
    }

    public function after() {
        
    }

    public function indexAction() {
        $login = \Business\LoginModel::getInstance();
        $member = $login->getCurrentUser();
        $redirectUrl = \Ku\Tool::filter($this->getParam('redirect_url', ''));

        if ($member instanceof \UsersModel) {
            $this->redirect($this->getRedirectUrl($redirectUrl));
            exit;
        }

    }


    public function iAction() {
        $request = $this->getRequest();
        $callback = \Ku\Tool::filter($request->get('callback', null));

        if ($this->useLimit('user.resource.limit.login.' . \Ku\Tool::getClientIp(true), 20, 600) === true) {
            $this->returnData('登录操作太频繁了, 请休息下吧', 23101, false, array(
                'using_captcha' =>0
            ), $callback);
            return false;
        }

        $username = $this->getParam('username', '');
        $password = $this->getParam('password', '');
        $remember = $this->getParam('isremember', 0);
        $login = \Business\LoginModel::getInstance();
        $res = $login->login($username, $password, $remember);

        if ($res === false) {
            $message = $login->getMessage();
            return $this->returnData($message, 0,false,$data=array(
                'using_captcha' =>0
            ), $callback);
        }
        $rememberLogin = (int) $request->get('remember_login', 0);
        if ($rememberLogin === 1) {
            $login->rememberLogin();
        }
        $user = $login->getLoginUser();
        if(empty($user->getNew_password())){
            $user->setNew_password(Ku\Tool::encryption($password));
            \Mapper\UsersModel::getInstance()->update($user);
        }
        $redirectUrl = \Ku\Tool::filter($request->get('redirect_url', null));
        return $this->returnData('登录成功', 23200, true, array(
            'url' => $this->getRedirectUrl($redirectUrl)
        ), $callback);

    }


}
