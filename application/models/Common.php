<?php

class Application_Model_Common
{

    public static function updateContacts($id,$add) {
        if ($add) {
            Zend_Db_Table::getDefaultAdapter()->query("update users set contacts = (contacts + 1) where id in ($id)");
        }
        else {
            Zend_Db_Table::getDefaultAdapter()->query("update users set contacts = (contacts - 1) where id in ($id) and contacts > 0");
        }
        return true;
    }

    public static function getFullname($name,$lastname,$id,$fid) {
        $che = Zend_Db_Table::getDefaultAdapter()->fetchOne("
            select id
            from user_friends
            where user_id=$id and friend_id=$fid
            and status = 1
            limit 1
        ");
        if (!$che) {
            $lastname = trim($lastname)[0].'.';
        }
        return $name.' '.$lastname;
    }

    public static function makeCoolDate($date,$offset) {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        if (!is_numeric($offset)) {
            $offset = 0;
        }
        $time = (int)$date+(int)$offset;
        // format date to August 12, 11:00 AM
        return gmdate('F d, h:s A',$time);
    }


    public static function updateUserFreeSlots($id) {
        exec("curl http://helpers.truebear.com/updateOne.php?id=$id > /dev/null 2>&1 &");
        return true;
    }

    public static function UpdateCompleteness($id) {
//        $first_name = 5;
//        $last_name = 5;
//        $job = 15;
//        $city = 5;
//        $industry = 10;

        $answer = 40;

        $res = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select u.photo, u.phone, u.business_email, u.skype, u.summary,
            ( select s.id from user_skills s where s.user_id = $id limit 1 ) as skills,
            ( select l.languages_id from user_languages l where l.user_id = $id limit 1 ) as languages,
            ( select j.id from user_jobs j where j.type = 1 and j.user_id = $id limit 1 ) as education
            from users u
            where u.id = $id
        ");

        $answer = $answer + ($res['photo'] != null ? 15 : 0);
        $answer = $answer + ($res['phone'] != null ? 5 : 0);
        $answer = $answer + ($res['business_email'] != null ? 5 : 0);
        $answer = $answer + ($res['skype'] != null ? 5 : 0);
        $answer = $answer + ($res['summary'] != null ? 10 : 0);
        $answer = $answer + ($res['skills'] != null ? 10 : 0);
        $answer = $answer + ($res['languages'] != null ? 5 : 0);
        $answer = $answer + ($res['languages'] != null ? 5 : 0);

        return $answer;
    }

    public static function UpdateExperience($id) {
        $all = false;
        $res = Zend_Db_Table::getDefaultAdapter()->fetchAll("
            select j.id, j.user_id, j.name, j.company, j.current, j.start_time, j.end_time, j.active
            from user_jobs j
            where j.user_id = $id
            and j.type = 0
            order by j.start_time asc
        ");

        if ($res) {
            $min = new DateTime($res[0]['start_time']);
            $max = 0;

            foreach ($res as $row) {
                if ($row['active'] == 1) {
                    $max = time();
                    break;
                }
                elseif ($row['end_time'] === null) {
                    $max = time();
                    break;
                }
                elseif (strtotime($row['end_time']) > $max) {
                    $max = strtotime($row['end_time']);
                }
            }

            $max = new DateTime(gmdate('Y-m-d H:i:s',$max));
            $end = $res[0]['end_time'];
            if (!$end || !strtotime($end) || strtotime($end) < 1) {
                $end = gmdate('Y-m-d H:i:s',time());
            }

            $all = $max->diff($min);
            $month = 0;
            foreach ($res as $row) {
                if (strtotime($row['start_time']) > strtotime($end)) {
                    $min = new DateTime($row['start_time']);
                    $max = new DateTime($end);
                    $diff = $max->diff($min);
                    $month = $month + (int)$diff->format('%y')*12 + (int)$diff->format('%m');
                }
                if (strtotime($row['end_time']) > strtotime($end)) {
                    $end = $row['end_time'];
                } elseif (!$row['end_time']) {
                    $end = date('Y-m-d');
                }
            }
        }
        if ($all) {
            $all = (int)$all->format('%y')*12 + (int)$all->format('%m') - $month;
            return round($all/12);
        }
        else {
            return 0;
        }
    }

    public static function getCity($city) {
        $data = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select city, city_name, lat, lng
            from city
            where city = '$city'
        ");
        if ($data) {
            return $data;
        }
        else {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->google->url.(string)$city;

            $client = new Zend_Http_Client($url);
            $req = json_decode($client->request()->getBody(), true);
            if ($req['status'] == 'OK') {
                $data['city'] = $city;
                $data['lat'] = $req['result']['geometry']['location']['lat'];
                $data['lng'] = $req['result']['geometry']['location']['lng'];

                $country = '';
                $region = '';
                foreach ($req['result']['address_components'] as $row) {
                    if ($row['types']) {
                        foreach ($row['types'] as $row2) {
                            if ($row2 == 'country') {
                                $country = $row['short_name'];
                            }
                            elseif ($row2 == 'administrative_area_level_1') {
                                $region = $row['short_name'];
                            }
                        }
                    }
                }

                if ($region && $country) {
                    $data['city_name'] = $req['result']['name'].', '.$region.', '.$country;
                }
                elseif($country) {
                    $data['city_name'] = $req['result']['name'].', '.$country;
                }
                else {
                    $data['city_name'] = $req['result']['formatted_address'];
                }

                if (isset($req['result']['geometry']['viewport'])) {
                    $data['n_lng'] = $req['result']['geometry']['viewport']['northeast']['lng'];
                    $data['s_lng'] = $req['result']['geometry']['viewport']['southwest']['lng'];
                    $data['n_lat'] = $req['result']['geometry']['viewport']['northeast']['lat'];
                    $data['s_lat'] = $req['result']['geometry']['viewport']['southwest']['lat'];
                }
                else {
                    $data['n_lng'] = (float)$data['lng']+0.1;
                    $data['s_lng'] = (float)$data['lng']-0.1;
                    $data['n_lat'] = (float)$data['lat']+0.1;
                    $data['s_lat'] = (float)$data['lat']-0.1;
                }

                Zend_Db_Table::getDefaultAdapter()->insert('city',array(
                    'city' => $city,
                    'city_name' => $data['city_name'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    's_lng' => $data['s_lng'],
                    'n_lng' => $data['n_lng'],
                    'n_lat' => $data['n_lat'],
                    's_lat' => $data['s_lat'],
                ));

                unset($data['s_lng']);
                unset($data['n_lng']);
                unset($data['n_lat']);
                unset($data['s_lat']);
                return $data;
            }
            else {
                return array();
            }
        }
    }

    public static function validTime($date_from,$date_to) {
        $time_m = $date_to-$date_from;
        if (time() < $date_from && $time_m >= 3600) {
            return false;
        }
        return true;
    }

    public static function getPlace($place) {
        $data = Zend_Db_Table::getDefaultAdapter()->fetchRow("
            select foursquare_id, place, lat, lng
            from place
            where foursquare_id = '$place'
        ");
        if ($data) {
            return $data;
        }
        else {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $url = $config->foursquare->url;
            $token = $config->foursquare->token;

            $client = new Zend_Http_Client($url.$place.$token);
            $req = json_decode($client->request()->getBody(), true);
            if ($req['meta']['code'] == '200') {
                $data['place'] = $req['response']['venue']['name'];
                $data['lat'] = $req['response']['venue']['location']['lat'];
                $data['lng'] = $req['response']['venue']['location']['lng'];
                $data['foursquare_id'] = $place;
                Zend_Db_Table::getDefaultAdapter()->insert('place',array(
                    'foursquare_id' => $place,
                    'place' => $data['place'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                ));
                return $data;
            }
            else {
                return array();
            }
        }
    }

    // send email
    public static function sendEmail($to, $subject, $bodyText, $cc=null, $bcc=null,
                                     $viewFileName=null, $options=null, $tags=null, $filename=null, $filedata=null, $script_path=null)
    {
        try {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $from = $config->resources->mail->defaultFrom->email;
            $fromName = $config->resources->mail->defaultFrom->name;

            $emails = array();
            $validator = new Zend_Validate_EmailAddress();
            // from
            if (!$validator->isValid($from))
                throw new Exception("wrong originator address");
            // to
            foreach ((array)$to as $email) {
                if ($validator->isValid($email))
                    $emails[] = $email;
            }
            if (!count($emails))
                throw new Exception("no correct destination address");
            // subject
            if (!$subject)
                throw new Exception("no subject");
            // init
            $mail = new Zend_Mail('utf-8');
            $mail->setFrom($from, $fromName)
                ->addTo($emails)
                ->setSubject($subject);
            // body
            if ($bodyText) {
                $mail->setBodyText($bodyText);
            } elseif($viewFileName) {
                $html = new Zend_View();
                if (!$script_path) {
                    $html->setScriptPath(APPLICATION_PATH . '/email_templates/');
                } else {
                    $html->setScriptPath(APPLICATION_PATH . $script_path);
                }
                if ($options)
                    $html->assign('options', $options);
                $bodyText = $html->render($viewFileName);
                $mail->setBodyHtml($bodyText);
            } else
                throw new Exception("no body text or html");
            // cc and bcc
            if ($validator->isValid($cc))
                $mail->addCc($cc);
            if ($validator->isValid($bcc))
                $mail->addBcc($bcc);
            // attachments
            if ($filename && $filedata) {
                $at = $mail->createAttachment($filedata);
                $at->type        = 'application/octet-stream';
                $at->disposition = Zend_Mime::DISPOSITION_INLINE;
                $at->encoding    = Zend_Mime::ENCODING_BASE64;
                $at->filename    = $filename;
            }

            if ($tags != null) {
                $mail->addHeader('X-MC-Tags', $tags);
            }

            //send e-mail
            $mail->send();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}

