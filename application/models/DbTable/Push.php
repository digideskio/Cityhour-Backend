<?php

class Application_Model_DbTable_Push extends Zend_Db_Table_Abstract
{

    protected $_name = 'push';

    public function addToken($id, $token, $device, $debug) {

        $puID = $this->fetchRow("user_id = $id and device = '$device'")['id'];
        if (!is_numeric($puID)) {
            $this->insert(array(
                'user_id' => $id,
                'deviceToken' => $token,
                'device' => $device,
                'debug' => $debug
            ));
        }
        else {
            $this->update(array(
                'deviceToken' => $token,
                'debug' => $debug
            ),"id = $puID");
        }
        return true;
    }

    public function deletePush($id, $device) {
        $this->delete("user_id = $id and device = $device");
        return true;
    }

    public function sendAll() {
//        $dcrt = APPLICATION_PATH.'/configs/development.pem';
//        $pcrt = APPLICATION_PATH.'/configs/production.pem';
//
//        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/apns.log');
//        $logger = new Zend_Log($writer);
//
//        $tokens = $this->_db->fetchAll("
//            select id, deviceToken, debug
//            from push
//            where user_id in ($users)
//        ");
//
//        if (isset($tokens[0])) {
//            $apns = new Zend_Mobile_Push_Apns();
//            $apns->setCertificate($pcrt);
//            $apns->setCertificatePassphrase('gfhjkm');
//            try {
//                $apns->connect(Zend_Mobile_Push_Apns::SERVER_PRODUCTION_URI);
//            } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//                exit(1);
//            } catch (Zend_Mobile_Push_Exception $e) {
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//                exit(1);
//            }
//
//            $dapns = new Zend_Mobile_Push_Apns();
//            $dapns->setCertificate($dcrt);
//            $dapns->setCertificatePassphrase('gfhjkm');
//            try {
//                $dapns->connect(Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI);
//            } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//                exit(1);
//            } catch (Zend_Mobile_Push_Exception $e) {
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//                exit(1);
//            }
//        }
//
//        foreach ($tokens as $num=>$row) {
//            $id = $row['id'];
//            try {
//                $message = new Zend_Mobile_Push_Message_Apns();
//                $message->setAlert("One of your projects was updated.");
//                $message->setBadge(0);
//                $message->setSound('default');
//                $message->setId(time());
//                $message->setToken($row['deviceToken']);
//                $message->addCustomData('info', $info);
//                if ($row['debug'] == 0) {
//                    $apns->send($message);
//                }
//                else {
//                    $dapns->send($message);
//                }
//            } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
//                $this->delete("id = $id");
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//            } catch (Zend_Mobile_Push_Exception $e) {
//                $logger->info(Zend_Debug::dump($e->getMessage()));
//            }
//        }
//        if (isset($tokens[0])) {
//            $apns->close();
//        }
        return true;
    }

    public function sendPush($user_id,$data,$type,$alert) {
        $data = json_encode($data);
        $this->_db->insert('push_messages',array(
            'user_id' => $user_id,
            'type' => $type,
            'data' => $data,
            'alert' => $alert
        ));
        return true;
    }


}

