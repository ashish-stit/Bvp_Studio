<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Drive;

class DriveController extends Controller
{
   public function drive(Request $request)
   {
	   	$file=$request->link;
	   	$client = new Google_Client();
       putenv('GOOGLE_APPLICATION_CREDENTIALS=bvpstudio-7d6b94a696ac.json');
       $client->useApplicationDefaultCredentials();
       $client->addScope(Google_Service_Drive::DRIVE);
       $driveService = new Google_Service_Drive($client);
       $fileID =$file;
       $response = $driveService->files->export($fileID, 'text/csv', array(
      'alt' => 'media'));
       $content = $response->getBody()->getContents();
       print_r($content);
       die;
        
  } 
}
