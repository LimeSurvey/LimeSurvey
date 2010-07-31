<?php
include_once('globalsettings.php');
include_once('database.php');
if($subaction=='bounceprocessing')
{
	if(empty($cron))
	{
	$surveyidoriginal = $_GET['sid'];
}
	$settings=getSurveyInfo($surveyidoriginal);
		                     if ($settings['bounceprocessing']=='N')
				{	
				}
		else
		{
		$bouncetotal=0;
		$checktotal=0;
		if($settings['bounceprocessing']=='G')
			{
				$accounttype=getGlobalSetting('bounceaccounttype');
				$hostname=getGlobalSetting('bounceaccounthost');
				$username=getGlobalSetting('bounceaccountuser');
				$pass=getGlobalSetting('bounceaccountpass');
				$hostencryption=getGlobalSetting('bounceencryption');
			}
		else
			{
				
				$accounttype=$settings['bounceaccounttype'];
				$hostname=$settings['bounceaccounthost'];
				$username=$settings['bounceaccountuser'];
				$pass=$settings['bounceaccountpass'];
				$hostencryption=$settings['bounceaccountencryption'];
			}
			
		if($accounttype=='IMAP')
		{
			if($hostencryption=='SSL')
			   {
				$finalhostname='{'.$hostname.'/imap/ssl}INBOX';
			   }
			elseif($hostencryption=='TLS')
			   {
				$finalhostname='{'.$hostname.'/imap/tls}INBOX';
			   }
			else                 
			   {	
				$finalhostname='{'.$hostname.'/imap}INBOX';
			   }
			if(@$mbox=imap_open($finalhostname,$username,$pass))
			{
			@$count=imap_num_msg($mbox);
			$lasthinfo=imap_headerinfo($mbox,$count);
			$datelc=$lasthinfo->date;
			$datelcu = strtotime($datelc);
            $gettimestamp = "select bouncetime from ".db_table_name("surveys")." where sid='$surveyidoriginal';";
		    $datelcufiles = $connect->Execute($gettimestamp);
			$datelcufile = substr($datelcufiles,11);
			while($datelcu > $datelcufile)
			{	
				$lasthinfo=imap_headerinfo($mbox,$count);
				$datelc=$lasthinfo->date;
				$datelcu = strtotime($datelc);
				$header = explode("\r\n", imap_body($mbox,$count));
				foreach ($header as $item)
				 {
				 	if (preg_match('/^X-surveyid/',$item))
				 	{
						$surveyid=explode(": ",$item);
					}		
					if (preg_match('/^X-tokenid/',$item))
					{
						$token=explode(": ",$item);
						if($surveyidoriginal == $surveyid[1])
						{ 
							$bouncequery = "UPDATE ".db_table_name("tokens_$surveyidoriginal")." set emailstatus='bounced' where token='$token[1]';";
							$anish=$connect->Execute($bouncequery);
							$bouncetotal++;
						}
					}
				   }
				$count--;
				$lasthinfo=imap_headerinfo($mbox,$count);
				$datelc=$lasthinfo->date;
				$datelcu = strtotime($datelc);
				$checktotal++;
			}
			@$count=imap_num_msg($mbox);
		@$lastcheckedinfo=imap_headerinfo($mbox,$count);
		$datelcfinal=$lastcheckedinfo->date;
		$datelcfinalu = strtotime($datelcfinal);
		$entertimestamp = "update ".db_table_name("surveys")." set bouncetime='$datelcfinalu' where sid='$surveyidoriginal';";
			$executetimestamp = $connect->Execute($entertimestamp);
		}
	else
		{
			echo "Please check your settings";
		}
	}
	elseif($accounttype='POP')
	{
		if($hostencryption=='SSL')
		   {
			$finalhostname='{'.$hostname.'/pop3/ssl/novalidate-cert}INBOX';
		   }
		elseif($hostencryption=='TLS')
		   {
			$finalhostname='{'.$hostname.'/pop3/tls/novalidate-cert}INBOX';
		   }
		else
		   {
			$finalhostname='{'.$hostname.'/pop3/novalidate-cert}INBOX';
		   }
		if(@$mbox=imap_open($finalhostname,$username,$pass))
		{
		@$count=imap_num_msg($mbox);
		while($count>0)
		{		@$header = explode("\r\n", imap_body($mbox,$count));
				foreach ($header as $item)
				 {
				 	if (preg_match('/^X-surveyid/',$item))
	      				   {
						$surveyid=explode(": ",$item);
					   }	
					if (preg_match('/^X-tokenid/',$item))
					  {	
							$token=explode(": ",$item);
							if($surveyidoriginal == $surveyid[1])
							{ 
								$bouncequery = "UPDATE ".db_table_name("tokens_$surveyidoriginal")." set emailstatus='bounced' where 							  token='$token[1]';";
								$anish=$connect->Execute($bouncequery) or safe_die ("Couldn't update sent field<br />$query<br />".$connect->ErrorMsg());
								$bouncetotal++;
							}
						}
					}
			$count--;
			$checktotal++;
		}
		imap_errors();
		imap_close($mbox);
	}
	else
	{
		echo "Please check your settings";
	}
 }
if($bouncetotal>0)
	{	
echo "<div id='dialog-modal'>$checktotal messages were scanned out of which $bouncetotal were marked as bounce by the system. <br><br><br>Please reload the browse table to view results</div>";
	}	
else 
	{

echo "<div id ='dialog-modal'>$checktotal messages were scanned out of which $bouncetotal were marked as bounce by the system.</div>";

	}
}

}

?>

