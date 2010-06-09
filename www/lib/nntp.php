<?php
require_once("config.php");
if(!include('Net/NNTP/Client.php')) {
	exit("Error: <b>You must install the pear package 'Net_NNTP'.</b>");	
}

class Nntp extends Net_NNTP_Client
{    
	function doConnect() 
	{
		$ret = $this->connect(NNTP_SERVER);
		if(PEAR::isError($ret))
		{
			echo "Cannot connect to server ".NNTP_SERVER." $ret";
			die();
		}
		if(!defined(NNTP_USERNAME) && NNTP_USERNAME!="" )
		{
			$ret2 = $this->authenticate(NNTP_USERNAME, NNTP_PASSWORD);
			if(PEAR::isError($ret) || PEAR::isError($ret2)) 
			{
				echo "Cannot authenticate to server ".NNTP_SERVER." - ".NNTP_USERNAME." ($ret $ret2)";
				die();
			}
		}
	}
	
	function doQuit() 
	{
		$this->quit();
	}
	
	function getBinary($binary)
	{
		require_once(WWW_DIR."/lib/yenc.php");
		$yenc = new yenc();
		$message = $dec = '';
		$summary = $this->selectGroup($binary['binary']['groupname']);
		if (PEAR::isError($summary)) 
		{
			echo $summary->getMessage();
			return false;
		}

		// Fetch body
		foreach($binary['parts'] as $part) 
		{
			$messageID = '<'.$part['messageID'].'>';
			$body = $this->getBody($messageID, true);
			if (PEAR::isError($body)) 
			{
			   echo 'Error fetching part number '.$part['messageID'].' in '.$binary['binary']['groupname'].' (Server response: '. $body->getMessage().')';
			   return false;
			}
			
			$dec = $yenc->decode($body);
			if ($yenc->error) 
			{
				echo $yenc->error;
				return false;
			}

			$message .= $dec;
		}
		return $message;
	}
	
}
?>
