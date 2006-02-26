<?php

/***************************************************************************************
 * XPertMailer Version 1.4.2 Stable - 2/13/2006 3:48 PM                                *
 *                                                                                     *
 * This file is part of the XPertMailer package (http://xpertmailer.sourceforge.net/)  *
 *                                                                                     *
 * XPertMailer is free software; you can redistribute it and/or modify it under the    *
 * terms of the GNU General Public License as published by the Free Software           *
 * Foundation; either version 2 of the License, or (at your option) any later version. * 
 *                                                                                     *
 * XPertMailer is distributed in the hope that it will be useful, but WITHOUT ANY      *
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A     *
 * PARTICULAR PURPOSE.  See the GNU General Public License for more details.           *
 *                                                                                     *
 * You should have received a copy of the GNU General Public License along with        *
 * XPertMailer; if not, write to the Free Software Foundation, Inc., 51 Franklin St,   *
 * Fifth Floor, Boston, MA  02110-1301  USA                                            *
 *                                                                                     *
 * XPertMailer php functions class. Sends e-mail message in MIME type format.          *
 * Copyright (C) 2006  Tanase Laurentiu Iulian                                         *
 *                                                                                     *
 ***************************************************************************************/

if(!defined('PRINT_ERROR')) define('PRINT_ERROR', true);

if(isset($_CONSTS_NAME) && PRINT_ERROR) trigger_error("Variable '\$_CONSTS_NAME' it is already defined", 256);
if(isset($_NAME_CONSTS) && PRINT_ERROR) trigger_error("Variable '\$_NAME_CONSTS' it is already defined", 256);
if(isset($_VALUE_CONST) && PRINT_ERROR) trigger_error("Variable '\$_VALUE_CONST' it is already defined", 256);

$_CONSTS_NAME = array(
	'SMTP_LOCAL'        => 1, 
	'SMTP_CLIENT'       => 2, 
	'SMTP_LOCAL_CLIENT' => 3, 
	'SMTP_CLIENT_LOCAL' => 4, 
	'SMTP_RELAY'        => 5, 
	'SMTP_RELAY_CLIENT' => 6, 
	'SMTP_CLIENT_RELAY' => 7, 
	'AUTH_DETECT'       => 1, 
	'AUTH_LOGIN'        => 2, 
	'AUTH_PLAIN'        => 3, 
	'MX_FALSE'          => false, 
	'MX_TRUE'           => true, 
	'ATTACH_HTML_IMG'   => 1, 
	'ATTACH_FILE'       => 2, 
	'P_LOW'             => 1, 
	'P_NORMAL'          => 2, 
	'P_HIGH'            => 3, 
	'SSL_FALSE'         => false, 
	'SSL_TRUE'          => true, 
	'CRLF'              => "\r\n", 
	'IS_WIN'            => (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
);
foreach($_CONSTS_NAME as $_NAME_CONSTS => $_VALUE_CONST){
	if(defined($_NAME_CONSTS)){
		if(PRINT_ERROR) trigger_error("The constant '".$_NAME_CONSTS."' it is already defined", 256);
	}else define($_NAME_CONSTS, $_VALUE_CONST);
}

class XPertMailer
{
	var $_setsmtp = 3;
	var $_pnumber = 25;
	var $_porelay = 25;
	var $_timeout = 30;

	var $_setarrh;
	var $_attc1st;
	var $_attc2nd;

	var $_subject;
	var $_fromadr;

	var $_sslconn = false;
	var $_fromail = false;
	var $_fromnam = false;

	var $_partmix;
	var $_part1st;
	var $_part2nd;

	var $_wheaders;
	var $_wmessage;
	var $_wattachs;
	var $_msgsplit;

	var $_authuser;
	var $_authpass;
	var $_relayset;
	var $_authsets = false;
	var $_priority = false;

	var $_charset = "ISO-8859-1";
	var $response = "unknow";

	function XPertMailer($smtp = 3, $relay = false){

		if(is_int($smtp) && $smtp >= 1 && $smtp <= 7){
			if($smtp >= 5){
				if(!$relay){
					if(PRINT_ERROR) trigger_error("The relay host name or ip address is not set on class XPertMailer->constructor", 512);
				}else{
					if(is_string($relay) && trim($relay) != ""){
						$this->_setsmtp  = $smtp;
						$this->_relayset = $relay;
					}elseif(PRINT_ERROR) trigger_error("Invalid relay host name or ip address on class XPertMailer->constructor", 512);
				}
			}else $this->_setsmtp = $smtp;
		}elseif(PRINT_ERROR) trigger_error("Invalid SMTP connection preference value on class XPertMailer->constructor", 512);
		$this->_setarrh = $this->_attc1st = $this->_attc2nd = $this->_msgsplit = array();

	}

	function timeout($tmout){
		if(is_int($tmout)) $this->_timeout = $tmout;
		elseif(PRINT_ERROR) trigger_error("Invalid parameter type on class XPertMailer->timeout()", 512);
	}

	function auth($user, $pass, $type = 1, $vssl = false, $prelay = 25){
		if(is_string($user) && is_string($pass) && !empty($user) && !empty($pass)){
			$this->_authuser = $user;
			$this->_authpass = $pass;
			if(is_int($type) && ($type == 1 || $type == 2 || $type == 3)) $this->_authsets = $type;
			else{
				$this->_authsets = 1;
				if(PRINT_ERROR) trigger_error("Invalid 3'rd auth parameter type on class XPertMailer->auth()", 512);
			}
			if(is_bool($vssl)){
				if($vssl){
					if(extension_loaded('openssl')){
						$this->_sslconn = true;
						if(is_int($prelay)) $this->_porelay = $prelay;
						elseif(PRINT_ERROR) trigger_error("Invalid TSL/SSL port number value on class XPertMailer->auth()", 512);
					}elseif(PRINT_ERROR) trigger_error("Your PHP don't have SSL support on class XPertMailer->auth()", 512);
				}
			}elseif(PRINT_ERROR) trigger_error("Invalid SSL value on class XPertMailer->auth()", 512);
		}elseif(PRINT_ERROR) trigger_error("Invalid parameter(s) on class XPertMailer->auth()", 512);
	}

	function headers($arrh){

		if(is_array($arrh) && count($arrh) > 0){
			$rebh = array();
			foreach($arrh as $numh => $valh){
				if(is_string($numh) && is_string($valh) && !empty($numh) && !empty($valh)){
					if(isset($rebh[$numh]) && PRINT_ERROR) trigger_error("Duplicate array key on class XPertMailer->headers()", 1024);
					$rebh[$numh] = $valh;
				}elseif(PRINT_ERROR) trigger_error("Invalid array parameter type on class XPertMailer->headers()", 1024);
			}
			$this->_setarrh = $rebh;
		}elseif(PRINT_ERROR) trigger_error("Invalid parameter type on class XPertMailer->headers()", 512);

	}

	function _attach_file($attc, $typeatt){

		if(is_array($attc) && count($attc) > 0){
			$reb1st = $reb2nd = array();
			$lst1st = $this->_attc1st;
			$lst2nd = $this->_attc2nd;
			foreach($attc as $numa => $vala){
				if(is_string($vala) && !empty($vala)){
					if(is_file($vala)){
						if(is_readable($vala)){
							$nmfile = (is_string($numa) && !empty($numa)) ? $numa : basename($vala);
							if(isset($reb1st[$nmfile]) || isset($reb2nd[$nmfile]) || isset($lst1st[$nmfile]) || isset($lst2nd[$nmfile])){
								if(PRINT_ERROR) trigger_error("Duplicate array key on class XPertMailer->_attach_file()", 1024);
							}
							if(strstr($nmfile, '/')){
								if(PRINT_ERROR) trigger_error("Do not include path value in the name of the file ".$nmfile." on class XPertMailer->_attach_file()", 1024);
								$nmfile = basename($nmfile);
							}
							if(!strstr($nmfile, '.')){
								if(PRINT_ERROR) trigger_error("File name: ".$nmfile." doesn't have an extension defined on class XPertMailer->_attach_file()", 1024);
							}else{
								$expext = explode(".", $nmfile);
								if($expext[count($expext)-1] != ""){
									if($typeatt == 1) $reb1st[$nmfile] = $vala;
									elseif($typeatt == 2) $reb2nd[$nmfile] = $vala;
								}else{
									if(PRINT_ERROR) trigger_error("File name: ".$nmfile." doesn't have an extension defined on class XPertMailer->_attach_file()", 1024);
								}
							}
						}elseif(PRINT_ERROR) trigger_error("Can't read file: ".$vala." on class XPertMailer->_attach_file()", 1024);
					}elseif(PRINT_ERROR) trigger_error("File: ".$vala." doesn't exists on class XPertMailer->_attach_file()", 1024);
				}elseif(PRINT_ERROR) trigger_error("Invalid array parameter type on class XPertMailer->_attach_file()", 1024);
			}
			$this->_attc1st = array_merge($lst1st, $reb1st);
			$this->_attc2nd = array_merge($lst2nd, $reb2nd);
		}elseif(PRINT_ERROR) trigger_error("Invalid first parameter type on class XPertMailer->_attach_file()", 512);

	}

	function attach($arratt, $attype = 2){
		if($attype == 1 || $attype == 2) $this->_attach_file($arratt, $attype);
		elseif(PRINT_ERROR) trigger_error("Invalid secound parameter type on class XPertMailer->attach()", 512);
	}

	function is_mail($addr, $vermx = false){

		$ism = false;
		if(is_string($addr)){
			$exp = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$";
			if(eregi($exp, $addr)){
				if($vermx){
					$ret = explode("@", $addr);
					$mxr = IS_WIN ? $this->_getmxrr_win($ret[1], $mxh) : getmxrr($ret[1], $mxh);
					if(count($mxh) > 0){
						foreach($mxh as $mxv){
							if($mxv != gethostbyname($mxv)){
								$ism = true;
								break;
							}
						}
					}
				}else $ism = true;
			}
		}elseif(PRINT_ERROR) trigger_error("Invalid first parameter type on class XPertMailer->is_mail()", 512);

		return $ism;

	}

	function _sendtoip($ip, $toarr, $relay = false){

		$set = true;
		$pnm = $this->_pnumber;
		$ssl = "";

		if($relay){
			$ssl = $this->_sslconn ? "tls://" : "";
			$pnm = $this->_porelay;
		}

		if($connect = fsockopen($ssl.$ip, $pnm, $err_num, $err_msg, $this->_timeout)){

			$rcv = fgets($connect, 1024);
			if(substr($rcv, 0, 3) != "220"){
				fclose($connect);
				$this->response = "Response 2 error: ".$rcv;
				$set = false;
			}

			if($set){

				$relayh = "";

				if($relay && $this->_authsets){
	
					fputs($connect, "EHLO ".$this->_fromadr.CRLF);

					$getinfo = "";
					while(!feof($connect)){
						$rcv = fgets($connect, 1024);
						$getinfo .= $rcv;
						if(substr($rcv, 0, 4) != "250-") break;
					}

					if(substr($rcv, 0, 3) != "250"){
						fclose($connect);
						$this->response = "Response 30 error: ".$rcv;
						$set = false;
					}

					$authreq = false;
					if($this->_authsets == 1){
						if(strstr($getinfo, 'LOGIN')) $authreq = "login";
						elseif(strstr($getinfo, 'PLAIN')) $authreq = "plain";
					}elseif($this->_authsets == 2) $authreq = "login";
					elseif($this->_authsets == 3) $authreq = "plain";

					if(!$authreq || $authreq == "login"){

					if($set){
						fputs($connect, "AUTH LOGIN".CRLF);
						$rcv = fgets($connect, 1024);
						if(substr($rcv, 0, 3) != "334"){
							fclose($connect);
							$this->response = "Response 32 error: ".$rcv;
							$set = false;
						}
					}
					if($set){
						fputs($connect, base64_encode($this->_authuser).CRLF);
						$rcv = fgets($connect, 1024);
						if(substr($rcv, 0, 3) != "334"){
							fclose($connect);
							$this->response = "Response 33 error: ".$rcv;
							$set = false;
						}
					}
					if($set){
						fputs($connect, base64_encode($this->_authpass).CRLF);
						$rcv = fgets($connect, 1024);
						if(substr($rcv, 0, 3) != "235"){
							fclose($connect);
							$this->response = "Response 34 error: ".$rcv;
							$set = false;
						}
					}

					}elseif($authreq == "plain"){

					if($set){
						fputs($connect, "AUTH PLAIN ".base64_encode($this->_authuser.chr(0).$this->_authuser.chr(0).$this->_authpass).CRLF);
						$rcv = fgets($connect, 1024);
						if(substr($rcv, 0, 3) != "235"){
							fclose($connect);
							$this->response = "Response 35 error: ".$rcv;
							$set = false;
						}
					}

					}

					$relayh = "@".$this->_relayset.":";

				}else{

					fputs($connect, "HELO ".$this->_fromadr.CRLF);
					$rcv = fgets($connect, 1024);
					if(substr($rcv, 0, 3) != "250"){
						fclose($connect);
						$this->response = "Response 31 error: ".$rcv;
						$set = false;
					}
				}
			}

			if($set){
				fputs($connect, "MAIL FROM:<".$this->_fromail.">".CRLF);
				$rcv = fgets($connect, 1024);
				if(substr($rcv, 0, 3) != "250"){
					fclose($connect);
					$this->response = "Response 4 error: ".$rcv;
					$set = false;
				}
			}

			if($set){
				foreach($toarr as $arrval){
					if($set){
						fputs($connect, "RCPT TO:<".$relayh.$arrval.">".CRLF);
						$rcv = fgets($connect, 1024);
						if(substr($rcv, 0, 3) != "250"){
							fclose($connect);
							$this->response = "Response 5 error: ".$rcv;
							$set = false;
						}
					}else break;
				}
			}

			if($set){
				fputs($connect, "DATA".CRLF);
				$rcv = fgets($connect, 1024);
				if(substr($rcv, 0, 3) != "354"){
					fclose($connect);
					$this->response = "Response 6 error: ".$rcv;
					$set = false;
				}
			}

			if($set){
				foreach($this->_msgsplit as $partmsg) fputs($connect, $partmsg);
				fputs($connect, ".".CRLF);
				$rcv = fgets($connect, 1024);
				if(substr($rcv, 0, 3) != "250"){
					$this->response = "Response 7 error: ".$rcv;
					$set = false;
				}
				fputs($connect, "QUIT".CRLF);
				if($rcvs = @fgets($connect, 1024)) $rcv = $rcvs;
				fclose($connect);
			}

			if($set) $this->response = "Response 8 success: ".$rcv;

		}else{
			$this->response = "Response 1 error: ".$err_msg;
			$set = false;
		}

		return $set;

	}

	function _is_ip($ipval){

		$retip = false;
		if(is_string($ipval) && !empty($ipval)){
			$expips = explode(".", $ipval);
			if(count($expips) == 4){
				$each = true;
				foreach($expips as $number){
					$partno = intval($number);
					if(!($number === strval($partno) && $partno >= 0 && $partno <= 255)){
						$each = false;
						break;
					}
				}
				$retip = $each;
			}
		}

		return $retip;

	}

	function _sendtohost($host, $detarr, $toaddh, $hccstr, $hbccstr, $dtmsg, $tpmime, $hrelay = false){

		$headstr2 = $this->_header($toaddh, $hccstr, $hbccstr, $this->_subject, $tpmime, true);
		$this->_msgsplit = $this->_splitmsg($headstr2.CRLF.CRLF.$dtmsg);

		if(strtolower($host) == "localhost"){
			$headstr1 = $this->_header($toaddh, $hccstr, $hbccstr, $this->_subject, $tpmime, false);
			return mail($toaddh, $this->_subject, $dtmsg, $headstr1) ? true : $this->_sendtoip("127.0.0.1", $detarr);
		}else{
			if($this->_is_ip($host)) return $this->_sendtoip($host, $detarr, $hrelay);
			else{
				$retrn = false;
				$resmx = IS_WIN ? $this->_getmxrr_win($host, $mxhost) : getmxrr($host, $mxhost);
				$iparr = array();
				if($resmx){
					foreach($mxhost as $hostname){
						$iphost = gethostbyname($hostname);
						if($hostname != $iphost && $hostname != $host && $this->_is_ip($iphost)) $iparr[] = $iphost;
					}
				}else{
					$iphost = gethostbyname($host);
					if($this->_is_ip($iphost)) $iparr[] = $iphost;
				}
				if(count($iparr) > 0){
					foreach($iparr as $ipaddr) if($retrn = $this->_sendtoip($ipaddr, $detarr, $hrelay)) break;
				}else $this->response = "Host name '".$host."' doesn't have IP address !";

				return $retrn;
			}
		}
	}

	function _encstring($len = 9){

		$encwrd = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$enclen = strlen($encwrd)-1;
		$encids = "";
		for($i=0;$i<$len;$i++) $encids .= $encwrd{rand(0, $enclen)};

		return $encids;

	}

	function _header($tom, $hcc, $hbcc, $subjct, $content, $cntx){

		$arr_headers = array();

		$fromaddr = $toaddr = false;
		if(count($this->_setarrh) > 0){
			foreach($this->_setarrh as $hnam => $hval){
				if(is_string($hnam) && is_string($hval) && trim($hnam) != "" && trim($hval) != ""){

				$hnam = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $hnam);
				$hval = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $hval);

				$hnam = trim($hnam);
				$hval = trim($hval);

				if(strtolower($hnam) == "from"){
					$expf = explode(" ", $hval);
					$mfrm = str_replace(array('<', '>', '"'), '', $expf[count($expf)-1]);
					if($this->is_mail($mfrm)){
						$fromaddr = $mfrm;
						$arr_headers['From'] = $hval;
					}
				}else{
					if(!(strtolower($hnam) == "to" || strtolower($hnam) == "subject" || strtolower($hnam) == "cc" || strtolower($hnam) == "bcc")) $arr_headers[$hnam] = $hval;
				}

				}elseif(PRINT_ERROR) trigger_error("Invalid header format on class XPertMailer->_setheaders()", 512);
			}
		}
		
		if(!($fromaddr && $this->_fromail)){
			$fromaddr = ini_get('sendmail_from');
			if(empty($fromaddr)){
				if(isset($_SERVER['SERVER_ADMIN']) && $this->is_mail($_SERVER['SERVER_ADMIN'])) $fromaddr = $_SERVER['SERVER_ADMIN'];
				elseif(isset($_SERVER['SERVER_NAME'])) $fromaddr = "postmaster@".$_SERVER['SERVER_NAME'];
				elseif(isset($_SERVER['HTTP_HOST']))   $fromaddr = "postmaster@".$_SERVER['HTTP_HOST'];
				elseif(isset($_SERVER['REMOTE_ADDR'])) $fromaddr = "postmaster@".$_SERVER['REMOTE_ADDR'];
				elseif(isset($_SERVER['SERVER_ADDR'])) $fromaddr = "postmaster@".$_SERVER['SERVER_ADDR'];
				else $fromaddr = "postmaster@localhost";
			}
		}

		if(!$this->_fromail) $this->_fromail = $fromaddr;
		else $fromaddr = $this->_fromail;
		$expfrom = explode("@", $fromaddr);
		$this->_fromadr = $expfrom[1];

		if(!isset($arr_headers['From'])){
			if(IS_WIN && !$cntx){
				$inifrom = ini_get('sendmail_from');
				if(empty($inifrom)) $arr_headers['From'] = ($this->_fromnam ? '"'.$this->_fromnam.'" ' : '').'<'.$fromaddr.'>';
				elseif(PRINT_ERROR) trigger_error("From e-mail address can not be set because it is already defined 'sendmail_from' value in php.ini on class XPertMailer->_setheaders()", 512);
			}else $arr_headers['From'] = ($this->_fromnam ? '"'.$this->_fromnam.'" ' : '').'<'.$fromaddr.'>';
		}

		if($cntx) $arr_headers['To'] = $tom;
		if($cntx) $arr_headers['Subject'] = $subjct;
		if(!empty($hcc)) $arr_headers['Cc'] = $hcc;
		if(!empty($hbcc) && !$cntx) $arr_headers['Bcc'] = $hbcc;

		$arr_headers['Date'] = date("r");
		$arr_headers['MIME-Version'] = '1.0';

		if($content == "text/plain"){
			$arr_headers['Content-Type'] = 'text/plain; charset="'.$this->_charset.'"';
			$arr_headers['Content-Transfer-Encoding'] = '7bit';
		}elseif($content == "multipart/alternative") $arr_headers['Content-Type'] = 'multipart/alternative; boundary="'.$this->_partmix.'"';
		elseif($content == "multipart/related")      $arr_headers['Content-Type'] = 'multipart/related; boundary="'.$this->_partmix.'"';
		elseif($content == "multipart/mixed")        $arr_headers['Content-Type'] = 'multipart/mixed; boundary="'.$this->_partmix.'"';

		$arr_headers['Message-ID'] = "<XPertMailer14.".uniqid(time())."@".$expfrom[1].">";

		if($this->_priority){
			$arr_headers['X-Priority']        = $this->_priority[1];
			$arr_headers['X-MSMail-Priority'] = $this->_priority[0];
		}else{
			if(!isset($arr_headers['X-Priority']))        $arr_headers['X-Priority']        = "3";
			if(!isset($arr_headers['X-MSMail-Priority'])) $arr_headers['X-MSMail-Priority'] = "Normal";
		}

		$arr_headers['X-Mailer']  = base64_decode("WFBlcnRNYWlsZXIgMS40IDxodHRwOi8vd3d3LnhwZXJ0bWFpbGVyLmNvbS8+");
		$arr_headers['X-MimeOLE'] = base64_decode("UHJvZHVjZWQgQnkgWFBlcnRNYWlsZXIgVjEuNA==");

		$retheaders = "";
		foreach($arr_headers as $hname => $hvalue) $retheaders .= $hname.": ".$hvalue.CRLF;

		return substr($retheaders, 0, -1*strlen(CRLF));

	}

	function _to_quoted($input, $arrids = array(), $linemax = 76){

		if(count($arrids) > 0){
			$find1 = $repl1 = array();
			foreach($arrids as $namefl => $codevl){
				$find1[] = "=\"".$namefl;
				$repl1[] = "=\"cid:".$codevl;
				$find2[] = "=".$namefl;
				$repl2[] = "=cid:".$codevl;
			}
			$input = str_replace($find1, $repl1, $input);
			$input = str_replace($find2, $repl2, $input);
		}
		
		$lines = preg_split("/\r?\n/", $input);
        $genre = "";
		while(list(, $line) = each($lines)){
			$linelen = strlen($line);
			$newline = "";
			for($i=0;$i<$linelen;$i++){
				$char = substr($line, $i, 1);
				$decd = ord($char);
				if($decd == 32 && $i == ($linelen - 1)) $char = "=20";
				elseif($decd == 61 || $decd < 32 || $decd > 126) $char = "=".strtoupper(sprintf('%02s', dechex($decd)));
                if((strlen($newline) + strlen($char)) >= $linemax){
					$genre  .= $newline."=".CRLF;
					$newline = "";
				}
				$newline .= $char;
			}
			$genre .= $newline.CRLF;
		}

		return substr($genre, 0, -1*strlen(CRLF));

    }

	function _splitmsg($longmsg, $approx = 900){

		$longmsg = str_replace(array(".\r\n", ".\n", ".\r"), ". ".CRLF, $longmsg);
		$msgarr  = explode(CRLF, $longmsg);
		$addmsg  = "";
		$arrmsg  = array();

		foreach($msgarr as $inline){
			$addmsg .= $inline.CRLF;
			if(strlen($addmsg) >= $approx){
				$arrmsg[] = $addmsg;
				$addmsg = "";
			}
		}

		if(count($arrmsg) == 0) $arrmsg[] = $longmsg;
		else $arrmsg[] = $addmsg;

		return $arrmsg;

	}

	function send($to, $subj, $textmsg, $htmlmsg = false, $charset = false){

		$retval = $iserrs = false;

		$to = str_replace(array("\r\n", "\r", "\n", "\t"), " ", trim($to));
		$toexpl = explode(", ", $to);
		$alldm = $grpto = $verad = $arrvl = array();
		foreach($toexpl as $valto){
			$valto = trim($valto);
			$expadd = explode(" ", $valto);
			$addsto = str_replace(array("<", ">"), "", $expadd[count($expadd)-1]);
			if($this->is_mail($addsto)){
				if(!isset($verad[$addsto])){
					$expat = explode("@", $addsto);
					$verad[$addsto] = true;
					$grpto[$expat[1]][] = $addsto;
					$alldm[] = $addsto;
					$arrvl[] = $valto;
				}elseif(PRINT_ERROR) trigger_error("Duplicate to e-mail address '".$addsto."' on class XPertMailer->send()", 512);
			}elseif(PRINT_ERROR) trigger_error("Invalid to e-mail address format '".$addsto."' on class XPertMailer->send()", 512);
		}

		if(!(count($alldm) > 0)){
			$iserrs = true;
			if(PRINT_ERROR) trigger_error("Can not find any valid to e-mail address on class XPertMailer->send()", 512);
		}
		if(!is_string($subj)){
			$iserrs = true;
			if(PRINT_ERROR) trigger_error("Invalid subject format on class XPertMailer->send()", 512);
		}
		if(!is_string($textmsg)){
			$iserrs = true;
			if(PRINT_ERROR) trigger_error("Invalid text/plain message format on class XPertMailer->send()", 512);
		}

		if(!$iserrs){ // 1

			$arrcc = $arrbcc = array();
			if(count($this->_setarrh) > 0){
				foreach($this->_setarrh as $numhd => $valhd){
					$numhd = trim($numhd);
					$valhd = trim($valhd);
					if(strtolower($numhd) == "cc"){
						$expcc = explode(", ", $valhd);
						foreach($expcc as $ccadd){
							$ccadd  = trim($ccadd);
							$expcvl = explode(" ", $ccadd);
							$addrcc = str_replace(array("<", ">"), "", $expcvl[count($expcvl)-1]);
							if($this->is_mail($addrcc)){
								if(!isset($vercc[$addrcc])){
									$vercc[$addrcc] = true;
									$arrcc[] = array($addrcc, $ccadd);
								}elseif(PRINT_ERROR) trigger_error("Duplicate Cc e-mail address '".$addrcc."' on class XPertMailer->send()", 512);
							}elseif(PRINT_ERROR) trigger_error("Invalid Cc e-mail address format '".$addrcc."' on class XPertMailer->send()", 512);
						}
					}elseif(strtolower($numhd) == "bcc"){
						$expbcc = explode(", ", $valhd);
						foreach($expbcc as $bccadd){
							$bccadd = trim($bccadd);
							$expbvl = explode(" ", $bccadd);
							$addbcc = str_replace(array("<", ">"), "", $expbvl[count($expbvl)-1]);
							if($this->is_mail($addbcc)){
								if(!isset($verbcc[$addbcc])){
									$verbcc[$addbcc] = true;
									$arrbcc[] = array($addbcc, $bccadd);
								}elseif(PRINT_ERROR) trigger_error("Duplicate Bcc e-mail address '".$addbcc."' on class XPertMailer->send()", 512);
							}elseif(PRINT_ERROR) trigger_error("Invalid Bcc e-mail address format '".$addbcc."' on class XPertMailer->send()", 512);
						}
					}
				}
			}

			$repcc = $repbcc = array();
			if(count($arrcc) > 0){
				$repcc = array();
				foreach($arrcc as $arrccdat){
					$samecc = false;
					foreach($alldm as $tovaladd){
						if($arrccdat[0] == $tovaladd){
							$samecc = true;
							if(PRINT_ERROR) trigger_error("The e-mail address '".$tovaladd."' appear in 'To' and 'Cc' on class XPertMailer->send()", 512);
							break;
						}
					}
					if(!$samecc) $repcc[] = $arrccdat;
				}
			}
			if(count($arrbcc) > 0){
				$repbcc = array();
				foreach($arrbcc as $arrbccdat){
					$samebcc = false;
					foreach($alldm as $tovaladd){
						if($arrbccdat[0] == $tovaladd){
							$samebcc = true;
							if(PRINT_ERROR) trigger_error("The e-mail address '".$tovaladd."' appear in 'To' and 'Bcc' on class XPertMailer->send()", 512);
							break;
						}
					}
					if(!$samebcc && count($repcc) > 0){
						foreach($repcc as $toarrcc){
							if($arrbccdat[0] == $toarrcc[0]){
								$samebcc = true;
								if(PRINT_ERROR) trigger_error("The e-mail address '".$toarrcc[0]."' appear in 'Cc' and 'Bcc' on class XPertMailer->send()", 512);
								break;
							}
						}
					}
					if(!$samebcc) $repbcc[] = $arrbccdat;
				}
			}

			$tostr = $ccstr = $bccstr = "";
			foreach($arrvl as $line) $tostr .= $line.", ";
			if(count($repcc) > 0){
				foreach($repcc as $ccline){
					$ccstr .= $ccline[1].", ";
					$expcc = explode("@", $ccline[0]);
					$grpto[$expcc[1]][] = $ccline[0];
					$alldm[] = $ccline[0];
				}
			}
			if(count($repbcc) > 0){
				foreach($repbcc as $bccline){
					$bccstr .= $bccline[1].", ";
					$expbcc = explode("@", $bccline[0]);
					$grpto[$expbcc[1]][] = $bccline[0];
					$alldm[] = $bccline[0];
				}
			}
			$tostr = substr($tostr, 0, -2);
			if(!empty($ccstr)) $ccstr = substr($ccstr, 0, -2);
			if(!empty($bccstr)) $bccstr = substr($bccstr, 0, -2);

			$reptxt = $rephtm = "";

			$arrtxt = explode(CRLF, $textmsg);
			if(count($arrtxt) > 0){
				foreach($arrtxt as $txtval) $reptxt .= str_replace(array("\r", "\n"), CRLF, $txtval).CRLF;
				$reptxt = substr($reptxt, 0, -2);
			}else $reptxt = str_replace(array("\r", "\n"), CRLF, $textmsg);

			if($htmlmsg){
				if(is_string($htmlmsg)){
					$arrhtm = explode(CRLF, $htmlmsg);
					if(count($arrhtm) > 0){
						foreach($arrhtm as $htmval) $rephtm .= str_replace(array("\r", "\n"), CRLF, $htmval).CRLF;
						$rephtm = substr($rephtm, 0, -2);
					}else $rephtm = str_replace(array("\r", "\n"), CRLF, $htmlmsg);
				}else{
					$htmlmsg = false;
					if(PRINT_ERROR) trigger_error("Invalid HTML message format on class XPertMailer->send()", 512);
				}
			}

			$this->_subject = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $subj);
			if($charset){
				if(is_string($charset) && !empty($charset)) $this->_charset = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $charset);
				else{
					if(PRINT_ERROR) trigger_error("Invalid charset format on class XPertMailer->send()", 512);
					$charset = $this->_charset;
				}
			}else $charset = $this->_charset;

			$cnt1st = count($this->_attc1st);
			$cnt2nd = count($this->_attc2nd);

			$this->_partmix = "=_0XPertMailer14".$this->_encstring();
			$this->_part1st = "=_1XPertMailer14".$this->_encstring();
			$this->_part2nd = "=_2XPertMailer14".$this->_encstring();

			$sendmsg = "";
			if(!$htmlmsg){
				if($cnt2nd > 0){
					$typmime = "multipart/mixed";
					$sendmsg = CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: text/plain; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: 7bit".CRLF.CRLF.$reptxt.CRLF;
					foreach($this->_attc2nd as $attname => $attfile){
						$content  = file_get_contents($attfile);
						$sendmsg .= "--".$this->_partmix.CRLF.
							"Content-Type: ".$this->mimetype($attname).CRLF.
							"Content-Transfer-Encoding: base64".CRLF.
							"Content-Disposition: attachment; filename=\"".$attname."\"".CRLF.CRLF.
							chunk_split(base64_encode($content));
					}
					$sendmsg .= "--".$this->_partmix."--".CRLF;
				}else{
					$typmime = "text/plain";
					$sendmsg = CRLF.$reptxt.CRLF;
				}
			}else{
				if($cnt1st > 0 && $cnt2nd > 0){
					$idsarrs = array();
					foreach($this->_attc1st as $attnamex => $attfilex) $idsarrs[$attnamex] = "CXPertMailer14".$this->_encstring();
					$typmime = "multipart/mixed";
					$sendmsg = CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: multipart/related; boundary=\"".$this->_part1st."\"".CRLF.CRLF.
						"--".$this->_part1st.CRLF.
						"Content-Type: multipart/alternative; boundary=\"".$this->_part2nd."\"".CRLF.CRLF.
						"--".$this->_part2nd.CRLF.
						"Content-Type: text/plain; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: 7bit".CRLF.CRLF.$reptxt.CRLF.
						"--".$this->_part2nd.CRLF.
						"Content-Type: text/html; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: quoted-printable".CRLF.CRLF.$this->_to_quoted($rephtm, $idsarrs).CRLF.
						"--".$this->_part2nd."--".CRLF;
					foreach($this->_attc1st as $attname1 => $attfile1){
						$content1 = file_get_contents($attfile1);
						$sendmsg .= "--".$this->_part1st.CRLF.
							"Content-Type: ".$this->mimetype($attname1).CRLF.
							"Content-Transfer-Encoding: base64".CRLF.
							"Content-Disposition: inline; filename=\"".$attname1."\"".CRLF.
							"Content-ID: <".$idsarrs[$attname1].">".CRLF.CRLF.
							chunk_split(base64_encode($content1));
					}
					$sendmsg .= "--".$this->_part1st."--".CRLF;
					foreach($this->_attc2nd as $attname2 => $attfile2){
						$content2 = file_get_contents($attfile2);
						$sendmsg .= "--".$this->_partmix.CRLF.
							"Content-Type: ".$this->mimetype($attname2).CRLF.
							"Content-Transfer-Encoding: base64".CRLF.
							"Content-Disposition: attachment; filename=\"".$attname2."\"".CRLF.CRLF.
							chunk_split(base64_encode($content2));
					}
					$sendmsg .= "--".$this->_partmix."--".CRLF;
				}elseif($cnt1st > 0){
					$idsarrs = array();
					foreach($this->_attc1st as $attnamex => $attfilex) $idsarrs[$attnamex] = "CXPertMailer14".$this->_encstring();
					$typmime = "multipart/related";
					$sendmsg = CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: multipart/alternative; boundary=\"".$this->_part1st."\"".CRLF.CRLF.
						"--".$this->_part1st.CRLF.
						"Content-Type: text/plain; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: 7bit".CRLF.CRLF.$reptxt.CRLF.
						"--".$this->_part1st.CRLF.
						"Content-Type: text/html; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: quoted-printable".CRLF.CRLF.$this->_to_quoted($rephtm, $idsarrs).CRLF.
						"--".$this->_part1st."--".CRLF;
					foreach($this->_attc1st as $attname => $attfile){
						$content  = file_get_contents($attfile);
						$sendmsg .= "--".$this->_partmix.CRLF.
							"Content-Type: ".$this->mimetype($attname).CRLF.
							"Content-Transfer-Encoding: base64".CRLF.
							"Content-Disposition: inline; filename=\"".$attname."\"".CRLF.
							"Content-ID: <".$idsarrs[$attname].">".CRLF.CRLF.
							chunk_split(base64_encode($content));
					}
					$sendmsg .= "--".$this->_partmix."--".CRLF;
				}elseif($cnt2nd > 0){
					$typmime = "multipart/mixed";
					$sendmsg = CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: multipart/alternative; boundary=\"".$this->_part1st."\"".CRLF.CRLF.
						"--".$this->_part1st.CRLF.
						"Content-Type: text/plain; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: 7bit".CRLF.CRLF.$reptxt.CRLF.
						"--".$this->_part1st.CRLF.
						"Content-Type: text/html; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: quoted-printable".CRLF.CRLF.$this->_to_quoted($rephtm).CRLF.
						"--".$this->_part1st."--".CRLF;
					foreach($this->_attc2nd as $attname => $attfile){
						$content  = file_get_contents($attfile);
						$sendmsg .= "--".$this->_partmix.CRLF.
							"Content-Type: ".$this->mimetype($attname).CRLF.
							"Content-Transfer-Encoding: base64".CRLF.
							"Content-Disposition: attachment; filename=\"".$attname."\"".CRLF.CRLF.
							chunk_split(base64_encode($content));
					}
					$sendmsg .= "--".$this->_partmix."--".CRLF;
				}else{
					$typmime = "multipart/alternative";
					$sendmsg = CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: text/plain; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: 7bit".CRLF.CRLF.$reptxt.CRLF.
						"--".$this->_partmix.CRLF.
						"Content-Type: text/html; charset=\"".$charset."\"".CRLF.
						"Content-Transfer-Encoding: quoted-printable".CRLF.CRLF.$this->_to_quoted($rephtm).CRLF.
						"--".$this->_partmix."--".CRLF;
				}
			}

			if(!empty($sendmsg)){
				if($this->_setsmtp == 1) $retval = $this->_sendtohost("localhost", $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
				elseif($this->_setsmtp == 5) $retval = $this->_sendtohost($this->_relayset, $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime, true);
				elseif($this->_setsmtp == 2){
					foreach($grpto as $namdom => $arrdom){
						$retval = $this->_sendtohost($namdom, $arrdom, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
					}
				}elseif($this->_setsmtp == 3){
					if(!$retval = $this->_sendtohost("localhost", $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime)){
						foreach($grpto as $namdom => $arrdom){
							$retval = $this->_sendtohost($namdom, $arrdom, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
						}
					}
				}elseif($this->_setsmtp == 4){
					foreach($grpto as $namdom => $arrdom){
						$retval = $this->_sendtohost($namdom, $arrdom, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
					}
					if(!$retval) $retval = $this->_sendtohost("localhost", $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
				}elseif($this->_setsmtp == 6){
					if(!$retval = $this->_sendtohost($this->_relayset, $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime, true)){
						foreach($grpto as $namdom => $arrdom){
							$retval = $this->_sendtohost($namdom, $arrdom, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
						}
					}
				}elseif($this->_setsmtp == 7){
					foreach($grpto as $namdom => $arrdom){
						$retval = $this->_sendtohost($namdom, $arrdom, $tostr, $ccstr, $bccstr, $sendmsg, $typmime);
					}
					if(!$retval) $retval = $this->_sendtohost($this->_relayset, $alldm, $tostr, $ccstr, $bccstr, $sendmsg, $typmime, true);
				}
			}elseif(PRINT_ERROR) trigger_error("Unknow message type on class XPertMailer->send()", 512);

		} // 1

		return $retval;

	}

	function priority($setpr){

		if(!(is_int($setpr) && ($setpr == 1 || $setpr == 2 || $setpr == 3))){
			if(PRINT_ERROR) trigger_error("Invalid priority value on class XPertMailer->priority()", 512);
		}else{
			if($setpr == 1) $this->_priority = array("Low", "5");
			elseif($setpr == 2) $this->_priority = array("Normal", "3");
			elseif($setpr == 3) $this->_priority = array("High", "1");
		}

	}

	function port($pno){
		if(is_int($pno)) $this->_pnumber = $pno;
		elseif(PRINT_ERROR) trigger_error("Invalid port number value on class XPertMailer->port()", 512);
	}

	function from($fromusr, $fromnme = false){

		if(is_string($fromusr) && $this->is_mail($fromusr)){
			$this->_fromail = $fromusr;
			if($fromnme){
				if(is_string($fromnme) && !empty($fromnme)) $this->_fromnam = $fromnme;
				elseif(PRINT_ERROR) trigger_error("Invalid from name format on class XPertMailer->from()", 512);
			}
		}elseif(PRINT_ERROR) trigger_error("Invalid from mail address format on class XPertMailer->from()", 512);

	}

	function response(){
		return $this->response;
	}

	function _getmxrr_win($hostname, &$mxhosts){

		$mxhosts = array();
		if(is_string($hostname)){
			$retstr = exec('nslookup -type=mx '.$hostname, $retarr);
			if($retstr && count($retarr) > 0){
				foreach($retarr as $line) if(preg_match("/.*mail exchanger = (.*)/", $line, $matches)) $mxhosts[] = $matches[1];
			}
		}elseif(PRINT_ERROR) trigger_error("Invalid parameter type on class XPertMailer->_getmxrr_win()", 512);

		return (count($mxhosts) > 0);

	}

	function mimetype($filename){

		$retm = "application/octet-stream";
		$mime = array(
			'z'    => "application/x-compress", 
			'xls'  => "application/x-excel", 
			'gtar' => "application/x-gtar", 
			'gz'   => "application/x-gzip", 
			'cgi'  => "application/x-httpd-cgi", 
			'php'  => "application/x-httpd-php", 
			'js'   => "application/x-javascript", 
			'swf'  => "application/x-shockwave-flash", 
			'tar'  => "application/x-tar", 
			'tgz'  => "application/x-tar", 
			'tcl'  => "application/x-tcl", 
			'src'  => "application/x-wais-source", 
			'zip'  => "application/zip", 
			'kar'  => "audio/midi", 
			'mid'  => "audio/midi", 
			'midi' => "audio/midi", 
			'mp2'  => "audio/mpeg", 
			'mp3'  => "audio/mpeg", 
			'mpga' => "audio/mpeg", 
			'ram'  => "audio/x-pn-realaudio", 
			'rm'   => "audio/x-pn-realaudio", 
			'rpm'  => "audio/x-pn-realaudio-plugin", 
			'wav'  => "audio/x-wav", 
			'bmp'  => "image/bmp", 
			'fif'  => "image/fif", 
			'gif'  => "image/gif", 
			'ief'  => "image/ief", 
			'jpe'  => "image/jpeg", 
			'jpeg' => "image/jpeg", 
			'jpg'  => "image/jpeg", 
			'png'  => "image/png", 
			'tif'  => "image/tiff", 
			'tiff' => "image/tiff", 
			'css'  => "text/css", 
			'htm'  => "text/html", 
			'html' => "text/html", 
			'txt'  => "text/plain", 
			'rtx'  => "text/richtext", 
			'vcf'  => "text/x-vcard", 
			'xml'  => "text/xml", 
			'xsl'  => "text/xsl", 
			'mpe'  => "video/mpeg", 
			'mpeg' => "video/mpeg", 
			'mpg'  => "video/mpeg", 
			'mov'  => "video/quicktime", 
			'qt'   => "video/quicktime", 
			'rv'   => "video/vnd.rn-realvideo", 
			'asf'  => "video/x-ms-asf", 
			'asx'  => "video/x-ms-asf", 
			'avi'  => "video/x-msvideo", 
			'vrml' => "x-world/x-vrml", 
			'wrl'  => "x-world/x-vrml"
		);
		$expext = explode(".", $filename);
		if(count($expext) >= 2){
			$extnam = strtolower($expext[count($expext)-1]);
			if(isset($mime[$extnam])) $retm = $mime[$extnam];
		}
		return $retm;

	}

}

?>