<?php

class Application_Model_DbTable_Complaints extends Zend_Db_Table_Abstract
{

    protected $_name = 'complaints';
    private $paginatorNumberPerPage = 50;

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

    public function addComplaint($data,$from,$to) {
        $che = $this->fetchRow(" `from` = $from and `to` = $to ");
        if ($che) {
            return 403;
        }
        $this->_db->beginTransaction();
        try {
            $this->insert($data);
            $this->_db->commit();
            return true;
        } catch (Exception $e) {
            $this->_db->rollBack();
            return $e->getMessage();
        }
    }

    public function addFeedback($user,$dscr) {
        try {
            $options = array(
                'name' => $user['name'].' '.$user['lastname'],
                'email' => $user['email'],
                'dscr' => $dscr
            );
            Application_Model_Common::sendEmail('bukashk0zzz@me.com', "ФидаБЕк", null, null, null, "feedback.phtml", $options, 'feedback');
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

}

