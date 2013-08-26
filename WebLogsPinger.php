<?php
/**
 * Pings rpc.weblogs.com to inform the site that your blog has been updated.
 *
 * @author Philip Brown <philip@supplyhog.com>
 * @license MIT
 * @version 1.0
 * Example
 * $p = new WebLogsPinger();
 * if(!$p->ping('My Blog', 'http://www.myblog.com', 'rss.myblog.com'))
 * 		echo $p->error;
 * else
 * 		echo $p->response;
 */
class WebLogsPinger {
	protected $_host = 'http://rpc.weblogs.com/pingSiteForm?';
	protected $_err = null;
	protected $_errno = null;
	protected $_response = null;
	
	/**
	 * @param string $name | The name of the blog. (1024 characters)
	 * @param string $url | The url of the blog. (255 characters)
	 * @param string $rss | The url of the rss feed. (255 characters)
	 * @return boolean
	 */
	public function ping($name, $url, $rss){
		$endpoint = $this->_host.'name='.urlencode($name).'&url='.urlencode($url).'&changesURL='.$rss;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$data = curl_exec($ch);
		$data = json_decode(json_encode((array)simplexml_load_string($data)),1);
		if(!curl_errno($ch)){
			if($data['flerror'] == 0){
				$this->_response = $data['message'];
			}
			else{
				$this->_errno = $data['flerror'];
				$this->_err = $data['message'];
			}
		}
		else{	// CURL Error
			$this->_errno = curl_errno($ch);
			$this->_err = curl_error($ch);
		}
		curl_close($ch);
		if($this->_err !== null)
			return false;
		return true;
	}
	
	/**
	 * Returns the values of error, error_no, and response.
	 * @return string | null
	 */
	public function __get($name){
		switch($name) {
			case 'error':
				return $this->_err;
				break;
			case 'error_no':
				return $this->_errno;
				break;
			case 'response':
				return $this->_response;
				break;
		}
		return null;
	}
}