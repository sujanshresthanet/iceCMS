<?php
/**
 * iceCMS v0.1
 * Created by Sergey Peshalov https://github.com/desfpc
 * PHP framework and CMS based on it.
 * https://github.com/desfpc/iceCMS
 *
 * User Class
 *
 */

namespace ice\Models;

use ice\iceObject;
use ice\DB\DB;

class User extends iceObject {


    public function registerUser(array $params){
        //проверяем необходимые параметры
        if(isset($params['login_email']) && isset($params['password_input']))
        {

            //проверяем существующих пользюков с такими email и tel
            $query='SELECT count(id) cid FROM users WHERE login_email = '."'".$params['login_email']."'";

            if(!is_null($params['login_phone']))
            {
                $query.= ' OR login_phone = '."'".$params['login_phone']."'";
            }

            if($res=$this->DB->query($query))
            {
                if(count($res) > 0)
                {
                    if($res[0]['cid'] > 0)
                    {
                        $this->errors[]='Пользователь с таким email или телефоном уже существует';
                        return false;
                    }
                }
            }

            //генерируем хэш пароля
            $params['password']=password_hash($params['password_input'], PASSWORD_DEFAULT);

            //пробуем сохранить пользюка
            if($this->createRecord($params))
            {
                $this->errors[]='Не удалось зарегистрировать пользователя';
                return true;
            }

        }

        return false;

    }

    public function authorizeUser($pass, $email){
        if((is_null($pass) || $pass == '') && (is_null($email) || $email == ''))
        {
            //еси еть php сессия
            if(isset($_SESSION['authorize']))
            {
                if($_SESSION['authorize'])
                {
                    $this->id=$_SESSION['authorize'];
                    if($this->getRecord($this->id))
                        return($this->id);
                }
            }
            else
            {
                $_SESSION['authorize']=false;
            }
            return false;
        }

        //проверяем запись пользователя
        $query='SELECT * FROM users WHERE status_id = 1 AND login_email = \''.$this->DB->mysqli->real_escape_string($email).'\'';

        //visualijop($query);

        if($res = $this->DB->query($query))
        {
            if(count($res) > 0)
            {
                $user=$res[0];

                //проверяем пароль
                if($ver=password_verify($pass, $user['password']))
                {
                    $this->id=$user['id'];
                    $this->getRecord($this->id);

                    $_SESSION['authorize']=$this->id;

                    return($this->id);
                }
            }
        }
        return false;
    }

    public function deauthorizeUser(){
        unset($_SESSION['authorize']);
        $this->id = null;
        return true;
    }

    public function changeUserStatus($status){
        if($this->isGotten){
            $query = 'UPDATE users SET status_id = '.$status.' WHERE id = '.$this->params['id'];
            if($res = $this->DB->query($query)){
                $this->uncacheRecord();

                //TODO удаление сессии пользователя при отключении

                return true;
            }
        }
        return false;
    }

    public function disableUser(){
        return $this->changeUserStatus(2);
    }

    public function enableUser(){
        return $this->changeUserStatus(1);
    }

    public function getRole(){
        $query='SELECT * from user_roles WHERE id = '.$this->params['user_role'];
        if($res=$this->DB->query($query))
        {
            $this->params['role']=$res[0];
        }
    }

    //метод для переработки в конкретном объекте
    public function fullRecord(){
        $this->getRole();
    }

    public function __construct(DB $DB, $id=null, $settings=null)
    {
        $this->doConstruct($DB, 'users', $id, $settings);
    }
}