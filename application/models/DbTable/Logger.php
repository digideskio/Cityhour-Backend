<?php

class Application_Model_DbTable_Logger extends Zend_Db_Table_Abstract
{

    protected $_name = 'logger';
    private $paginatorNumberPerPage = 50;

    public function saveData($data) {
        $data['data_in'] = @json_encode($data['data_in'], JSON_PRETTY_PRINT);
        $this->insert($data);
    }

    public function clearData() {
        $this->_db->query("truncate table logger");
        return true;
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

