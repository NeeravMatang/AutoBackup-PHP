<?php 
/*
_____   __                                   ______  ___      _____                       
___  | / /______________________ ___   __    ___   |/  /_____ __  /______ ______________ _
__   |/ /_  _ \  _ \_  ___/  __ `/_ | / /    __  /|_/ /_  __ `/  __/  __ `/_  __ \_  __ `/
_  /|  / /  __/  __/  /   / /_/ /__ |/ /     _  /  / / / /_/ // /_ / /_/ /_  / / /  /_/ / 
/_/ |_/  \___/\___//_/    \__,_/ _____/      /_/  /_/  \__,_/ \__/ \__,_/ /_/ /_/_\__, /  
                                                                                 /____/   
Copyright 2015 Neerav Matang (http://neeravmatang.in)
Github:   https://github.com/NeeravMatang/
Facebook: https://www.facebook.com/MatangNeeravOfficial
Twitter:  http://twitter.com/MatangNeerav

*/


//Config:
$sendMailTo = 'example_to@exampleto.com'; // The email you are sending to.
$mailFrom = "example@example.com"; // The email you are sending from.
$mailSubject = "Example Subject"; // The Subject of the email.
$mailBodyText = "Text body of message"; // The mail text body.
$fileLocation = "/"; // The location of file to be backed-up. (With trailing slash '/')
//end Config;

/*********************************************************************************************************************************************************************
     _                              _                   _   _   _        _              _                         _     _       _            _   _                
  __| |   ___       _ __     ___   | |_       ___    __| | (_) | |_     | |__     ___  | |   ___   __      __    | |_  | |__   (_)  ___     | | (_)  _ __     ___ 
 / _` |  / _ \     | '_ \   / _ \  | __|     / _ \  / _` | | | | __|    | '_ \   / _ \ | |  / _ \  \ \ /\ / /    | __| | '_ \  | | / __|    | | | | | '_ \   / _ \
| (_| | | (_) |    | | | | | (_) | | |_     |  __/ | (_| | | | | |_     | |_) | |  __/ | | | (_) |  \ V  V /     | |_  | | | | | | \__ \    | | | | | | | | |  __/
 \__,_|  \___/     |_| |_|  \___/   \__|     \___|  \__,_| |_|  \__|    |_.__/   \___| |_|  \___/    \_/\_/       \__| |_| |_| |_| |___/    |_| |_| |_| |_|  \___|
                                                                                                                                                                  
**********************************************************************************************************************************************************************/

// SystemConfig:

$fileToZip = scandir($fileLocation);
$date = date('dmY');
$backupFileName = 'backup' . $date . '.zip';

// end SystemConfig;
// Function createZip:

function createZip($files = array() , $destination = '', $overwrite = true)
{

	// if the zip file already exists and overwrite is false, return false

	if (file_exists($destination) && !$overwrite)
	{
		return false;
	}

	// vars

	$valid_files = array();

	// if files were passed in...

	if (is_array($files))
	{

		// cycle through each file

		foreach($files as $file)
		{

			// make sure the file exists

			if (file_exists($file))
			{
				$valid_files[] = $file;
			}
		}
	}

	// if we have good files...

	if (count($valid_files))
	{

		// create the archive

		$zip = new ZipArchive();
		if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
		{
			return false;
		}

		// add the files

		foreach($valid_files as $file)
		{
			$zip->addFile($file, $file);
		}

		// debug
		// echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		// close the zip -- done!

		$zip->close();

		// check to make sure the file exists

		return file_exists($destination);
	}
	else
	{
		return false;
	}
}

// end createZip;
// startZipping:

$resultOfZipCreation = createZip($fileToZip, $backupFileName);

// end startZipping;
// sendMail:

if ($resultOfZipCreation)
{
	$fileatt = $backupFileName; // Path to the file.
	$fileatt_type = "application/zip"; // File Type
	$fileatt_name = $backupFileName; // Filename that will be used for the file as the attachment
	$file = fopen($fileatt, 'rb');
	$data = fread($file, filesize($fileatt));
	fclose($file);
	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	$headers = "From: $mailFrom"; // Who the email is from (example)
	$headers.= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";
	$email_message.= "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type:text/html; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $mailBodyText;
	$email_message.= "\n\n";
	$data = chunk_split(base64_encode($data));
	$email_message.= "--{$mime_boundary}\n" . "Content-Type: {$fileatt_type};\n" . " name=\"{$fileatt_name}\"\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n" . "--{$mime_boundary}--\n";
	mail($sendMailTo, $mailSubject, $email_message, $headers);
}
else
{
	die($resultOfZipCreation);
}

// end sendMail;
