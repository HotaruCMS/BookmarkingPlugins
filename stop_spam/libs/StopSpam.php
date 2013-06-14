<?php
/**
 * The StopSpam class is based on this: http://www.stopforumspam.com/forum/t598-Basic-file-read
 *
 * PHP version 5
 *
 */
    
class StopSpamFunctions
{    
    /**
     * Check if the user is a spammer
     */
    public function checkSpammers($username ='', $email = '', $ip = '')
    {        
        if ($username) $args[] = 'username=' . urlencode(trim($username));
        if ($email) $args[] = 'email=' . urlencode(trim($email));
        if ($ip) $args[] = 'ip=' . urlencode(trim($ip));
        
        $sendArgs = implode('&', $args);
        
        if (!$sendArgs) return false;

        //Load the data from remote server using file into memory.
        $json = @file("http://www.stopforumspam.com/api?" . $sendArgs . '&f=json');                

        if (is_array($json)) return $json[0]; else return $json;       
    }
       
    
    /**
     * Use results from remote server and return flags for Hotaru
     * @param type $h
     * @param type $json
     * @return boolean|array
     */
    public function flagSpam($h, $json)
    {
            $result = json_decode($json);
            
            if (!$result || !isset($result->success) || !$result->success) { $h->messages[$h->lang('stop_spam_failed_test')] = 'red'; return false; }
            
            $appears_email = isset($result->email->appears) ? $result->email->appears : false;
            $appears_ip = isset($result->ip->appears) ? $result->ip->appears : false;
            
            $confidence_email = isset($result->email->confidence) ? $result->email->confidence : 0;
            $confidence_ip = isset($result->ip->confidence) ? $result->ip->confidence : 0;                       
            
            $confidence_settings = 10;
            
            $flags = array();
            if ($appears_email && $confidence_email >= $confidence_settings)  array_push($flags, 'email address');    
            //if (isset($result->username->appears) && $result->username->appears) array_push($flags, 'username');
            if ($appears_ip && $confidence_ip >= $confidence_settings) array_push($flags, 'IP address');            

            return $flags;
    }
    
    
    /**
     * Add spammer to the StopForumSpam.com database
     */
    public function addSpammer($ip = '', $username = '', $email = '', $apikey = '')
    {
        if (!$ip || !$username || !$email || !$apikey || $ip == '127.0.0.1') { return false; }
        
        $url = "http://www.stopforumspam.com/add.php?";
        $url .= "username=" . urlencode($username);
        $url .= "&ip_addr=" . $ip;
        $url .= "&email=" . urlencode($email);
        $url .= "&api_key=" . $apikey;
        
        require_once(EXTENSIONS . 'SWCMS/HotaruHttpRequest.php');
        $r = new HotaruHttpRequest($url);
        $error = $r->DownloadToString();
        //if (!$error) { echo "Success"; } else { echo $error; }
    }
}

?>