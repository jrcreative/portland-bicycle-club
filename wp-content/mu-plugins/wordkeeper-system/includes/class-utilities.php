<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use array_slice;
use count;
use curl_exec;
use curl_getinfo;
use curl_init;
use curl_setopt;
use dirname;
use explode;
use fclose;
use file_exists;
use file_get_contents;
use filter_var;
use fopen;
use fputs;
use function_exists;
use implode;
use ini_get;
use parse_url;
use str_replace;
use stream_context_create;
use stream_socket_client;
use strpos;
use substr;
use trim;

/**
 * The WordKeeper Utilities class
 *
 */
class Utilities {


	/**
	 * Tracks whether we have already submitted a clear cache call to the internal pool for this request
	 *
	 * @var bool
	 * @access public
	 * @static
	 */
	static $posted = false;


	/**
	 * HTTP GET request convenience method
	 *
	 * @access public
	 * @param mixed $url
	 * @return mixed $response
	 */
	public static function http_get($url, $opts = array()){

		if(Utilities::$posted == true){
			return;
		}

		$disabled = ini_get('disable_functions');
		if(function_exists('curl_init') && strpos($disabled, 'curl_exec') === false){
			$curl_available = true;
		}
		else{
			$curl_available = false;
		}

		$ssl = false;
		$response = null;
		if(strpos($url, 'https://') !== false){
			$ssl = true;
		}

		if(defined('ABSPATH')){
			$user = explode('/', trim(ABSPATH, '/'));
			$user = $user[1];
			$useragent = 'wordkeeper-' . $user;
			$ca = ABSPATH . 'wp-includes/certificates/ca-bundle.crt';
		}
		else{
			$fullpath = trim(dirname(__FILE__), '/');
			$fullpath = explode('/', $fullpath);

			$user = $fullpath[1];
			$useragent = 'wordkeeper-' . $user;

			$homepath = array_slice($fullpath, 0, 3);
			$homepath = implode('/', $homepath);

			if(file_exists($homepath . '/wp-includes/certificates/ca-bundle.crt')){
				$ca = $homepath . '/wp-includes/certificates/ca-bundle.crt';
			}
		}

		if($curl_available){
			try{
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				if($ssl){
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

					if(file_exists($ca)){
						curl_setopt($ch, CURLOPT_CAINFO, $ca);
					}
				}

				curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

				$response = curl_exec($ch);
				Utilities::$posted = true;

				$info = curl_getinfo($ch);
				$redirect = isset($info['redirect_url']) ? trim($info['redirect_url'], '/') : '';

				$response = array(
					'response' => $response,
					'status_code' => $info['http_code'],
					'redirect' => $redirect
				);

				if(false === $response['response']){
					$error = curl_errno($ch);
				}

				curl_close($ch);
			}
			catch(\Exception $e){
				//echo $e->getCode() . ' | ' . $e->getMessage();
			}
		}
		else{
			$parsed_url = parse_url($url);
			$host = $parsed_url['host'];

			if(isset($parsed_url['path'])){
				$path = $parsed_url['path'];
			}
			else{
				$path = '/';
			}

			if(isset($parsed_url['query'])){
				$path .= '?' . $parsed_url['query'];
			}

			$timeout = 10;
			$response = '';

			if(ini_get('allow_url_fopen')){
				$options = array(
					'http' => array(
						'method' => 'GET',
						'user_agent' => $useragent,
						'timeout' => $timeout,
						'header' => "Accept: */*\r\n" .
						"Accept-Language: en-us,en;q=0.5\r\n" .
						"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
						"Keep-Alive: 300\r\n" .
						"Connection: keep-alive\r\n" .
						"Referer: http://$host\r\n\r\n"
					)
				);

				if($ssl){
					$options['ssl'] = array(
						'verify_peer' => true,
						'cafile' => $ca,
						'verify_depth' => 5
					);
				}

				try{
					$context = stream_context_create($options);
					$response = @file_get_contents($url, false, $context);
					Utilities::$posted = true;

					$info = $http_response_header;
					$status = explode(' ', $info[0]);
					$i = 0;

					while($i < count($info)){
						if(strpos($info[$i], 'Location: ') !== false){
							break;
						}
						$i++;
					}

					$redirect = (isset($info[$i]) && strpos($info[$i], 'Location: ') !== false) ? trim(str_replace('Location: ', '', $info[$i]), '/') : '';

					$response = array(
						'response' => $response,
						'status_code' => $status[1],
						'redirect' => $redirect
					);
				}
				catch(\Exception $e){
					//echo $e->getMessage();
				}
			}
			else{
				$options = array(
					'http' => array(
						'method' => 'GET',
						'user_agent' => $useragent,
						'timeout' => $timeout,
						'header' => "Accept: */*\r\n" .
						"Accept-Language: en-us,en;q=0.5\r\n" .
						"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
						"Keep-Alive: 300\r\n" .
						"Connection: keep-alive\r\n" .
						"Referer: http://$host\r\n\r\n"
					)
				);

				if($ssl){
					$options['ssl'] = array(
						'verify_peer' => true,
						'cafile' => $ca,
						'verify_depth' => 5
					);

					$protocol = 'ssl://';
					$port = 443;
				}
				else{
					$protocol = 'tcp://';
					$port = 80;
				}

				$context = stream_context_create($options);
				$socket = stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

				if(!$socket){
					exit();
				}
				else{
					//send the necessary headers to get the file
					fputs($socket, "GET $path HTTP/1.0\r\n" .
						"Host: $host\r\n" .
						"Accept: */*\r\n\r\n"
					);

					$response = @stream_get_contents($socket);
					Utilities::$posted = true;

					fclose($socket);

					//strip the headers
					$pos = strpos($response, "\r\n\r\n");
					$headers = substr($response, 0, $pos + 3);
					$headers = explode("\r\n", $headers);
					$status = explode(' ', $headers[0]);
					$response = substr($response, $pos + 4);
					$i = 0;

					while($i < count($headers)){
						if(strpos($headers[$i], 'Location: ') !== false){
							break;
						}
						$i++;
					}

					if($response === false){
						$response = '';
					}

					$redirect = (isset($headers[$i]) && strpos($headers[$i], 'Location: ') !== false) ? trim(str_replace('Location: ', '', $headers[$i]), '/') : '';

					$response = array(
						'response' => $response,
						'status_code' => $status[1],
						'redirect' => $redirect
					);
				}
			}
		}

		if(isset($opts['save'])){
			file_put_contents($opts['save'], $response['response']);
			$response['response'] = $opts['save'];
		}

		$response['status_code'] = filter_var($response['status_code'], FILTER_SANITIZE_NUMBER_INT);
		$response['redirect'] = filter_var($response['redirect'], FILTER_SANITIZE_URL);

		return $response;
	}


	/**
	 * HTTP POST convience method
	 *
	 * @access public
	 * @param mixed $url
	 * @return mixed $response
	 */
	public static function http_post($url, $data, $opts = array()){

		if(Utilities::$posted == true){
			return;
		}

		$disabled = ini_get('disable_functions');
		if(function_exists('curl_init') && strpos($disabled, 'curl_exec') === false){
			$curl_available = true;
		}
		else{
			$curl_available = false;
		}

		$ssl = false;
		$response = null;
		if(strpos($url, 'https://') !== false){
			$ssl = true;
		}

		if(defined('ABSPATH')){
			$user = explode('/', trim(ABSPATH, '/'));
			$user = $user[1];
			$useragent = 'wordkeeper-' . $user;
			$ca = ABSPATH . 'wp-includes/certificates/ca-bundle.crt';
		}
		else{
			$fullpath = trim(dirname(__FILE__), '/');
			$fullpath = explode('/', $fullpath);

			$user = $fullpath[1];
			$useragent = 'wordkeeper-' . $user;

			$homepath = array_slice($fullpath, 0, 3);
			$homepath = implode('/', $homepath);

			if(file_exists($homepath . '/wp-includes/certificates/ca-bundle.crt')){
				$ca = $homepath . '/wp-includes/certificates/ca-bundle.crt';
			}
		}

		if($curl_available){
			try{
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				if($ssl){
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

					if(file_exists($ca)){
						curl_setopt($ch, CURLOPT_CAINFO, $ca);
					}
				}

				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

				$response = curl_exec($ch);
				Utilities::$posted = true;

				$info = curl_getinfo($ch);
				$redirect = isset($info['redirect_url']) ? trim($info['redirect_url'], '/') : '';

				$response = array(
					'response' => $response,
					'status_code' => $info['http_code'],
					'redirect' => $redirect
				);

				if(false === $response){
					$error = curl_errno($ch);
				}

				curl_close($ch);
			}
			catch(\Exception $e){
				//echo $e->getCode() . ' | ' . $e->getMessage();
			}
		}
		else{
			$parsed_url = parse_url($url);
			$host = $parsed_url['host'];

			if(isset($parsed_url['path'])){
				$path = $parsed_url['path'];
			}
			else{
				$path = '/';
			}

			if(isset($parsed_url['query'])){
				$path .= '?' . $parsed_url['query'];
			}

			$timeout = 10;
			$response = '';

			if(ini_get('allow_url_fopen')){
				$options = array(
					'http' => array(
						'method' => 'POST',
						'user_agent' => $useragent,
						'timeout' => $timeout,
						'content' => $data,
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
						"Content-Length: " . strlen($data) . "\r\n",
						"Keep-Alive: 300\r\n" .
						"Connection: keep-alive\r\n" .
						"Referer: http://$host\r\n\r\n"
					)
				);

				if($ssl){
					$options['ssl'] = array(
						'verify_peer' => true,
						'cafile' => $ca,
						'verify_depth' => 5
					);
				}

				try{
					$context = stream_context_create($options);
					$response = @file_get_contents($url, false, $context);
					Utilities::$posted = true;

					$info = $http_response_header;
					$status = explode(' ', $info[0]);
					$i = 0;

					while($i < count($info)){
						if(strpos($info[$i], 'Location: ') !== false){
							break;
						}
						$i++;
					}

					$redirect = (isset($info[$i]) && strpos($info[$i], 'Location: ') !== false) ? trim(str_replace('Location: ', '', $info[$i]), '/') : '';

					$response = array(
						'response' => $response,
						'status_code' => $status[1],
						'redirect' => $redirect
					);
				}
				catch(\Exception $e){
					//echo $e->getMessage();
				}
			}
			else{
				$options = array(
					'http' => array(
						'method' => 'POST',
						'user_agent' => $useragent,
						'timeout' => $timeout,
						'content' => $data,
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
						"Content-Length: " . strlen($data) . "\r\n",
						"Keep-Alive: 300\r\n" .
						"Connection: keep-alive\r\n" .
						"Referer: http://$host\r\n\r\n"
					)
				);

				if($ssl){
					$options['https'] = array(
						'verify_peer' => true,
						'cafile' => $ca,
						'verify_depth' => 5
					);
					$protocol = 'ssl://';
					$port = 443;
				}
				else{
					$protocol = 'tcp://';
					$port = 80;
				}

				$context = stream_context_create($options);
				$handle = fopen($url, 'rb', false, $context);
				$response = @stream_get_contents($handle);
				Utilities::$posted = true;

				$info = $http_response_header;
				$status = explode(' ', $info[0]);
				$i = 0;

				while($i < count($info)){
					if(strpos($info[$i], 'Location: ') !== false){
						break;
					}
					$i++;
				}

				fclose($handle);

				$redirect = (isset($info[$i]) && strpos($info[$i], 'Location: ') !== false) ? trim(str_replace('Location: ', '', $info[$i]), '/') : '';

				$response = array(
					'response' => $response,
					'status_code' => $status[1],
					'redirect' => $redirect
				);
			}
		}

		$response['status_code'] = filter_var($response['status_code'], FILTER_SANITIZE_NUMBER_INT);
		$response['redirect'] = filter_var($response['redirect'], FILTER_SANITIZE_URL);

		return $response;
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
