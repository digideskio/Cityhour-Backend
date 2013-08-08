<?php

class Application_Model_DbTable_Complaints extends Zend_Db_Table_Abstract
{

    protected $_name = 'complaints';
    private $paginatorNumberPerPage = 50;

    public function getTo($id,$type) {
        $res = $this->fetchAll("`to` = $id and `type` = $type");
        if ($res != null) {
            $res = $res->toArray();
            return $res;
        }
        else {
            return array();
        }
    }

    public function getList($data = false) {
        if ($data) {
            $email = $data['search'];
            $rows =  $this->_db->fetchAll("
                select c.id, c.type, c.`from`, c.to, c.dscr, c.create_at, c.status, u.name as name1, u.lastname as lastname1, u.email as email1, u2.name as name2, u2.lastname as lastname2, u2.email as email2
                from complaints c
                left join users u on c.`from` = u.id
                left join users u2 on c.`to` = u2.id
                where
                (u2.email like \"%$email%\" or u.email like \"%$email%\" or c.id like \"%$email%\" )
                order by u.id desc
            ");
        }
        else {
            $rows =  $this->_db->fetchAll("
                select c.id, c.type, c.`from`, c.to, c.dscr, c.create_at, c.status, u.name as name1, u.lastname as lastname1, u.email as email1, u2.name as name2, u2.lastname as lastname2, u2.email as email2
                from complaints c
                left join users u on c.`from` = u.id
                left join users u2 on c.`to` = u2.id
                order by u.id desc
            ");
        }
        $paginator = Zend_Paginator::factory($rows);
        $paginator->setItemCountPerPage($this->paginatorNumberPerPage);
        $cur_page = isset($data['page']) ? $data['page'] : 0;
        $paginator->setCurrentPageNumber($cur_page);
        return $paginator;
    }

    public function addComplaint($data) {
        $this->insert($data);
        return true;
    }

    public function updateComplaint($id,$dscr,$user_id) {
        $this->update(array(
            'dscr' => $dscr
        ),"id = $id and `from` = $user_id");
        return true;
    }

    public function deleteComplaint($user_id,$id) {
        $this->delete("id = $id and `from` = $user_id");
        return true;
    }

}

