<?php

class Application_Model_DbTable_Admins extends Zend_Db_Table_Abstract
{

    protected $_name = 'admins';

    public static function login($login, $password) {
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
        $authAdapter->setTableName('admins')
            ->setIdentityColumn('login')
            ->setCredentialColumn('password')
            ->setIdentity($login)
            ->setCredential($password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($authAdapter);
        if ($result->isValid()) {
            $authIdentity = $authAdapter->getResultRowObject();
            if ($authIdentity->status == 2) {
                $authStorage = $auth->getStorage();
                $authStorage->write($authIdentity);
                return true;
            } else
                return -2;
        } else
            return -1;
    }

    public function getList() {
        return $this->fetchAll()->toArray();
    }

}

