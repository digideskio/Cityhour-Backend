<?php

class Application_Model_Linkedin
{

    public function getUser($token) {
        $params = array('oauth2_access_token' => $token,
            'format' => 'json',
        );
        $url = 'https://api.linkedin.com/v1/people/~:(firstName,lastName,email-address)?' . http_build_query($params);
        $context = stream_context_create(
            array('http' =>
            array('method' => 'GET',
            )
            )
        );
        try {
            $user_profile = @file_get_contents($url, false, $context);
            $user_profile = (array)json_decode($user_profile);
        }
        catch (Exception $e) {
            $user_profile = false;
        }
        return $user_profile;
    }

    public function storeInfo($token,$id) {
        return true;
    }

}

