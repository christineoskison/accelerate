<?php
/**
 * Sharecount functions
 * Get the share count from the service sharedcount.com
 *
 * @package     MASHSB
 * @subpackage  Functions/sharedcount
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.9
 */

class mashsbSharedcount {
    private $url,$timeout;

    function __construct($url,$timeout=10) {
        $this->url=rawurlencode($url);
        $this->timeout=$timeout;
        }

function getFBTWCounts(){
        global $mashsb_options;
        
        isset($mashsb_options['facebook_count_mode']) ? $fb_mode = $mashsb_options['facebook_count_mode'] : $fb_mode = '';
        
        $sharecounts = $this->get_sharedcount();

        if(!$sharecounts){
	    return; 
        }
        
            $counts = array('shares'=>array(),'total'=>0);
        if ($fb_mode === 'total'){
            $counts['shares']['fb'] = $sharecounts['Facebook']['total_count'];
        } else {
            $counts['shares']['fb'] = $sharecounts['Facebook']['share_count'];    
        }
            $counts['shares']['tw'] = $sharecounts['Twitter'];

	foreach ($counts['shares'] as $mashsbcounts => $sharecount) $counts['total'] += (int)$sharecount;
        mashdebug()->error("sharedcount.com getFBTWCounts: " . $counts['total']);
        // Log the counts if debug is enabled
        //MASHSB()->logger->info("sharedcount.com FB total_count: " . $sharecounts['Facebook']['total_count'] . " FB share_count:" . $sharecounts['Facebook']['share_count'] . " TW: " . $sharecounts['Twitter']);
	return $counts;

}
/* Only used when mashshare-networks is enabled */
function getAllCounts(){
        global $mashsb_options;
        
        isset($mashsb_options['facebook_count_mode']) ? $fb_mode = $mashsb_options['facebook_count_mode'] : $fb_mode = '';
        $sharecounts = $this->get_sharedcount();
        if(!$sharecounts){
	       return; 
        }
        
	$counts = array('shares'=>array(),'total'=>0);
	if ($fb_mode === 'total'){
            isset($sharecounts['Facebook']['total_count']) ? $counts['shares']['fb'] = $sharecounts['Facebook']['total_count'] : $sharecounts['Facebook']['total_count'] = 0;
        } else {
            isset($sharecounts['Facebook']['share_count']) ? $counts['shares']['fb'] = $sharecounts['Facebook']['share_count'] : $counts['shares']['fb'] = 0;  
        }
            isset($sharecounts['Twitter']) ? $counts['shares']['tw'] = $sharecounts['Twitter'] : $sharecounts['Twitter'] = 0;
            isset($sharecounts['GooglePlusOne']) ? $counts['shares']['gp'] = $sharecounts['GooglePlusOne'] : $counts['shares']['gp'] = 0 ;
            isset($sharecounts['LinkedIn']) ? $counts['shares']['li'] = $sharecounts['LinkedIn'] : $counts['shares']['li'] = 0;
            isset($sharecounts['StumbleUpon']) ? $counts['shares']['st'] = $sharecounts['StumbleUpon'] : $counts['shares']['st'] = 0 ;
            isset($sharecounts['Pinterest']) ? $counts['shares']['pin'] = $sharecounts['Pinterest'] : $counts['shares']['pin'] = 0;

	foreach ($counts['shares'] as $sbserv => $sbsharecount) $counts['total'] += (int)$sbsharecount;
        mashdebug()->info("sharedcount.com getAllCounts: " . $counts['total']);
        //MASHSB()->logger->info("sharedcount.com FB total_count: " . $sharecounts['Facebook']['total_count'] . " FB share_count:" . $sharecounts['Facebook']['share_count'] . " TW: " . $sharecounts['Twitter'] . " G+:" . $counts['shares']['gb'] . " Linkedin:" . $counts['shares']['li'] . " Stumble: " . $counts['shares']['st'] . " Pinterest: " . $counts['shares']['pin']);
	return $counts;
}

function update_sharedcount_domain($apikey, $domain = false){ 
    global $mashsb_options;
	if(!$domain){
		try{
			$domain_obj =  $this->_curl('http://'. $mashsb_options["mashsharer_sharecount_domain"] . "/account?apikey=" . $apikey);
			$domain = $domain_obj["domain"];
		}
		catch (Exception $e){
	        mashdebug()->error("error: " . $domain_obj);
			return 0;
		}
	}
	$mashsb_options["mashsharer_sharecount_domain"] = $domain;
	update_option( 'mashsb_settings', $mashsb_options);
	return 1;
}

private function _curl($url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$curl_results = curl_exec ($curl);
	curl_close ($curl);
	return json_decode($curl_results, true);
}

function get_sharedcount()  {
    mashdebug()->info("Share URL: " . $this->url);
    global $mashsb_options;
    if(empty($mashsb_options['mashsharer_apikey'])){
        return 0; //quit early if there's no API key.
    }
    $apikey = trim($mashsb_options['mashsharer_apikey']);
    $domain = trim($mashsb_options['mashsharer_sharecount_domain']);
	
	if(!isset($domain) || empty($domain)){
		$domain = "free.sharedcount.com";
		$this->update_sharedcount_domain($apikey, $domain);
	}

	try {
        $counts = $this->_curl('http://'.$domain . "/?url=" . $this->url . "&apikey=" . $apikey);
        if(isset($counts["Error"]) && isset($counts['Domain']) && $counts["Type"] === "domain_apikey_mismatch"){
	         $this->update_sharedcount_domain($apikey, $counts['Domain']);
             return 0;
        }
        else if(isset($counts["Error"]) && isset($counts['Type']) && $counts['Type'] === 'invalid_api_key'  ){
             $this->update_sharedcount_domain($apikey);
             return 0;
        }
        mashdebug()->info("Twitter count: " . isset($counts['Twitter']));
        MASHSB()->logger->info("URL: " . urldecode($this->url) . " API Key:" . $apikey . " sharedcount.com FB total_count: " . $counts['Facebook']['total_count'] . " FB share_count:" . $counts['Facebook']['share_count'] . " TW: " . $counts['Twitter'] . " G+:" . $counts['GooglePlusOne'] . " Linkedin:" . $counts['LinkedIn'] . " Stumble: " . $counts['StumbleUpon'] . " Pinterest: " . $counts['Pinterest']);
        
        return $counts;
	} catch (Exception $e){
                mashdebug()->error("error: " . $counts);
                MASHSB()->logger->info('ERROR: Curl()' . $counts);
		return 0;
	}
        mashdebug()->error("error2: " . $counts);
        MASHSB()->logger->info('ERROR 2: Curl()' . $counts);
	return 0;
}

}
?>