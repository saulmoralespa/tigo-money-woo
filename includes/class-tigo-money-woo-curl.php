<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 8/10/17
 * Time: 05:40 PM
 */

class Tigo_Money_Woo_Curl
{
	public function execute($url, $params, $access)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		if (is_object(json_decode($params))){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $access ));
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}elseif ($params == 'GET'){
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $access ));
			curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $access);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec ($ch);
		curl_close ($ch);
		return $data;
	}
}