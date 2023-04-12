<?php
session_start();

if ( !$_SESSION["logged"] )
{
	header("Location: /index.php");
	exit();
}

function send($id)
{
	$path = "../../private/downloads.txt";
	if ( !($fd = fopen($path, "r")) )
	{
		$_SESSION["communicates"]["serverError"] = "<span style='color:red'>Server error! Mail admin at admin@{$_SERVER['HTTP_HOST']}</span>";
		header("Location: ../fileManager.php");
		return;
	}
	$found = false;
	while (!feof($fd))
	{
		$line = trim(fgets($fd));
		$arr = explode(";", $line);
		if ( count($arr) == 4 )
		{
			if ( $id === $arr[0] )
			{
				$found = true;
				$path = $arr[2];
				$name = $arr[1];
				break;
			}
		}
	}

	//var_dump($found);
	//var_dump($path . $id);
	//var_dump(!file_exists($path . $id));
	if ( !$found || !file_exists($path . $id) )
	{
		$_SESSION["communicates"]["fileError"] = "<span style='color:red'>No file with given id!</span>";
		return;
	}
	$fd = fopen($path . $id, "r");
	$size = filesize($path . $id);
	//$contents = fread($fd, $size);
	fclose($fd);

	header("Content-Type: application/octet-stream");
	header("Content-Length: $size;");
	header('Content-Disposition: attachment; filename="'.$name.'"');
	echo file_get_contents($path . $id);
	//echo $contents;
}

if ( !empty($_GET['fileid']) )
	send($_GET['fileid']);
else
{
	$_SESSION["communicates"]["fileError"] = "<span style='color:red'>No file id given!</span>";
	header("Location: ../fileManager.php");
}
?>
