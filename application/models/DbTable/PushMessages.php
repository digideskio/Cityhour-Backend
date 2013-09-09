<?php

class Application_Model_DbTable_PushMessages extends Zend_Db_Table_Abstract
{

    protected $_name = 'push_messages';

    public function sendAll($ids) {
        $ids = explode(',',$ids);
        $good_ids = array();
        foreach ($ids as $row) {
            if (is_numeric($row)) {
                array_push($good_ids,$row);
            }
        }
        $ids = implode(',',$good_ids);
        $tokens = $this->_db->fetchAll("
            select m.alert, m.data, p.debug, p.deviceToken, p.id, m.id as mid
            from push_messages m
            inner join push p on m.user_id = p.user_id
            where m.id in ($ids) and m.status = 0
        ");
        $dcrt = APPLICATION_PATH.'/configs/development.pem';
        $pcrt = APPLICATION_PATH.'/configs/production.pem';

        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/apns.log');
        $logger = new Zend_Log($writer);
        try {
            if (isset($tokens[0])) {

                //Connect
                $apns = new Zend_Mobile_Push_Apns();
                $apns->setCertificate($pcrt);
                $apns->setCertificatePassphrase('gfhjkm');
                try {
                    $apns->connect(Zend_Mobile_Push_Apns::SERVER_PRODUCTION_URI);
                } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
                    $logger->info(Zend_Debug::dump($e->getMessage()));
                    exit(1);
                } catch (Zend_Mobile_Push_Exception $e) {
                    $logger->info(Zend_Debug::dump($e->getMessage()));
                    exit(1);
                }

                $dapns = new Zend_Mobile_Push_Apns();
                $dapns->setCertificate($dcrt);
                $dapns->setCertificatePassphrase('gfhjkm');
                try {
                    $dapns->connect(Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI);
                } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
                    $logger->info(Zend_Debug::dump($e->getMessage()));
                    exit(1);
                } catch (Zend_Mobile_Push_Exception $e) {
                    $logger->info(Zend_Debug::dump($e->getMessage()));
                    exit(1);
                }
                //Send
                foreach ($tokens as $num=>$row) {
                    $mid = $row['mid'];
                    $this->update(array(
                        'status' => 1
                    ),"id = $mid");
                    $id = $row['id'];
                    try {
                        $message = new Zend_Mobile_Push_Message_Apns();
                        $message->setAlert($row['alert']);
                        $message->setBadge(0);
                        $message->setSound('default');
                        $message->setId(time());
                        $message->setToken($row['deviceToken']);
                        $message->addCustomData('info', $row['data']);
                        if ($row['debug'] == 0) {
                            $apns->send($message);
                        }
                        else {
                            $dapns->send($message);
                        }
                    } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
                        $this->delete("id = $id");
                        $logger->info(Zend_Debug::dump($e->getMessage()));
                    } catch (Zend_Mobile_Push_Exception $e) {
                        $logger->info(Zend_Debug::dump($e->getMessage()));
                    }
                }

                //Close connection
                $apns->close();
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}

