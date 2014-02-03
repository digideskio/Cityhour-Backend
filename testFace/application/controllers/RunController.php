<?php

class RunController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $form = new Application_Form_Run();

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                (new Application_Model_DbTable_TestUsers())->insertTestData();

                $this->view->data = $this->runTest($formData);
            } else {
                $this->view->err = 'Не все поля заполнены корректно.';
            }
        }

        $this->view->form = $form;
    }

    private function runTest($formData) {
        $url = "http://meetrocket2.demo.alterplay.com/";
//        $url = "http://127.0.0.1:5555/";
        $url_post = $url.'findPeople.php';
        $url_update = $url.'updateAll.php';
        file_get_contents($url_update);

        $city = (new Application_Model_DbTable_TestCity())->getCity($formData['city']);

        $day = date("Y-m-d ",time()+172800);
        $data_from = $day.$formData['start_time'];
        $data_from = strtotime($data_from);

        $data_to = $day.$formData['end_time'];
        $data_to = strtotime($data_to);

        $data = array(
            "private_key" => '2',
            "debug" => false,
            "offset" => $formData['offset'],
            "data_from" => $data_from,
            "data_to" => $data_to,
            "time_from" => $data_from,
            "time_to" => $data_to,
            "city" => $city['city'],
        );

        if ($formData['goal']) {
            $data['goal'] = $formData['goal'];
        }

        if ($formData['industry']) {
            $data['industry'] = $formData['industry'];
        }

        $data = json_encode($data);
        $client = new Zend_Http_Client($url_post);
        $client->setMethod('POST');
        $res = $client->setRawData($data, 'application/json')->request('POST');
        $res = json_decode($res->getBody(),true);
        $final = '';
        $this->view->dd = $res;

        $db = new Application_Model_DbTable_TestResult();

        if (isset($res['body']['users']) &&  $res['body']['users']) {
            foreach ($res['body']['users'] as $row) {
                $status = ($db->check($row['id'],$row['fp_start_time'],$row['fp_end_time'])) ? '<span style="color: #008000">ok</span><br>':'<span style="color: red">error</span><br>';
                $final .= print_r($row['name'].' => '.$status,true);
            }
        }
        else {
            $final = "No one found";
        }
        return $final;
    }


}

