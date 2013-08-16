<?php

class Application_Model_DbTable_Logger extends Zend_Db_Table_Abstract
{

    protected $_name = 'logger';
    private $paginatorNumberPerPage = 50;

    public function saveData($data) {
        $this->insert($data);
    }



    public function getList($data = false) {
        if ($data) {
            $email = $data['search'];
            $rows =  $this->fetchAll("url like '%$email%'",'id desc');
        }
        else {
            $rows =  $this->fetchAll(null,'id desc');
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setItemCountPerPage($this->paginatorNumberPerPage);
        $cur_page = isset($data['page']) ? $data['page'] : 0;
        $paginator->setCurrentPageNumber($cur_page);
        return $paginator;
    }

}

