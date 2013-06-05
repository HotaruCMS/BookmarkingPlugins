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
        $json = file("http://www.stopforumspam.com/api?" . $sendArgs . '&f=json');                
               
        if (is_array($json)) return $json[0]; else return $json;       
    }
    

    /**
     * Use the above function
     */
    public function isSpammer($username, $email, $ip)
    {
        // Get the Json results from remote site
        $result = $this->checkSpammers($username, $email, $ip);
        //Is he reported?
        if ($result['success'] == true) {
            //He is a spammer
            return true;
        } else {
            //He is not reported as a spammer
            return false;
        }
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