<?php
/**
 * Copyright 2018 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// [START gmail_quickstart]
require __DIR__ . './../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// if (php_sapi_name() != 'cli') {
//     throw new Exception('This application must be run on the command line.');
// }

use Google\Client;
use Google\Service\Gmail;
use PHPUnit\TextUI\XmlConfiguration\Constant;

/**
 * Returns an authorized API client.
 * @return Client the authorized client object
 */
function getClient()
{
    $client = new Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes('https://mail.google.com/');
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

function index(){
    $cliente = getClient();
    if ($cliente->getAccessToken()) {
        $service = new Google_Service_Gmail($cliente);
        try {
            $mail = new PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->From = 'shohan.office1@gmail.com';
            $mail->FromName = "shohan";
            $mail->AddAddress('shohan@coder71.com');
            $mail->AddReplyTo("coder71");
            $mail->Subject = "test";
            $mail->Body = base64_encode("<h3>Coder 71 Web team</h3><img src='https://www.emartwayskincare.com.bd/public/uploads/all/Iw1HjW9TkCvkwl3xJUY3qeuIPmBNmiDKfgb28i7x.jpg'/>");

            // attachment
            // foreach ($data['attachments'] as $attachment) {
            //     $location = public_path() . '/email_attachments/sent/';
            //     $filename = str_random(20) . '-' . $attachment->getClientOriginalName() . "." . $attachment->guessClientExtension();
            //     $file_location = $attachment->move($location, $filename);
            //     $mimetype = $attachment->getClientMimeType();
            //     $raw .= $line . "--" . $boundary . $line;
            //     $raw .= 'Content-Type: ' . $mimetype . '; name="' . $attachment->getClientOriginalName() . '";' . $line;
            //     $raw .= 'Content-ID: <' . $this->email . '>' . $line;
            //     $raw .= 'Content-Description: ' . $attachment->getClientOriginalName() . ';' . $line;
            //     $raw .= 'Content-Disposition: attachment; filename="' . $attachment->getClientOriginalName() . '"; size=' . $attachment->getClientSize() . ';' . $line;
            //     $raw .= 'Content-Transfer-Encoding: base64' . $line . $line;
            //     $raw .= chunk_split(base64_encode(file_get_contents($location . $filename)), 76, "\n") . $line;
            //     $raw .= '--' . $boundary . $line;
            // }

            // attachment

            $mail->preSend();
            $mail->isHTML();
            $mime = $mail->getSentMIMEMessage();
            $mime = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
            $mensaje = new Google_Service_Gmail_Message();
            $mensaje->setRaw($mime);
            $service->users_messages->send('me', $mensaje);
            $r = 1;
        } catch (Exception $e) {
            print $e->getMessage();
            $r = 0;
        }
    } else {
        $r = -1;
    }
    return $r;
}

$imagePath = __DIR__."/Turka Link IP 21-8-22.png";
$base64Image = base64_encode($imagePath);
$htmlMessage = <<<BB
<table role="presentation" width="100%">
<tr>

  <td bgcolor="#00A4BD" align="center" style="color: white;">

 <img alt="Flower" src="$base64Image" width="400px" align="middle">
 <img alt="Flower" src="cid:Turka Link IP 21-8-22" width="400px" align="middle">
    
    <h1> Welcome! </h1>
    
  </td>
</table>
BB;

$data = [
    "subject"=>"Test Message",
    "to"=>"shohan@coder71.com",
    "message"=>$htmlMessage,
    "cc"=>"shohan.office1@gmail.com",
];


function sendEmail($data)
 {
    $cliente = getClient();
    $service = new Google_Service_Gmail($cliente);
    //  $service = Mailbox::getGmailService();
     $message = new \Google_Service_Gmail_Message();
     $boundary = uniqid(rand(), true);
     $subject = $data['subject'];
     $to = $data['to'];
     $message_text = htmlspecialchars_decode($data['message']);
     $message_text = strip_tags($message_text);
     $data['cc'] = str_replace(" ", "", $data['cc']);
     $cc_tmp = explode(';', $data['cc']);
     $cc = implode(", ", $cc_tmp);
    //  $data['bcc'] = str_replace(" ", "", $data['bcc']);
    //  $bcc_tmp = explode(';', $data['bcc']);
    //  $bcc = implode(", ", $bcc_tmp);
     $line = "\n";
     $raw = "to: {$to}" . $line;
     if ($data['cc']) {
         $raw .= "cc: " . $cc . $line;
     }
    //  if ($data['bcc']) {
    //      $raw .= "bcc: " . $bcc . $line;
    //  }
     $raw .= "subject: {$subject}" . $line;


      /*
     if (!is_null($data['attachments'][0])) {
         $raw .= 'Content-type: multipart/mixed; boundary="' . $boundary . '"' . $line;
         $raw .= $line . "--" . $boundary . $line;
         $raw .= 'Content-Type: text/plain; charset=utf-8' . $line;
         $raw .= 'Content-Transfer-Encoding: 7bit' . $line . $line;
         $raw .= $message_text . $line;
         // $raw .= $line."--".$boundary.$line;
         // $raw .= 'Content-Type: text/html; charset=utf-8'.$line;
         // $raw .= $message_text.$line;
        
         foreach ($data['attachments'] as $attachment) {
             $location = public_path() . '/email_attachments/sent/';
             $filename = str_random(20) . '-' . $attachment->getClientOriginalName() . "." . $attachment->guessClientExtension();
             $file_location = $attachment->move($location, $filename);
             $mimetype = $attachment->getClientMimeType();
             $raw .= $line . "--" . $boundary . $line;
             $raw .= 'Content-Type: ' . $mimetype . '; name="' . $attachment->getClientOriginalName() . '";' . $line;
             $raw .= 'Content-ID: <' . $this->email . '>' . $line;
             $raw .= 'Content-Description: ' . $attachment->getClientOriginalName() . ';' . $line;
             $raw .= 'Content-Disposition: attachment; filename="' . $attachment->getClientOriginalName() . '"; size=' . $attachment->getClientSize() . ';' . $line;
             $raw .= 'Content-Transfer-Encoding: base64' . $line . $line;
             $raw .= chunk_split(base64_encode(file_get_contents($location . $filename)), 76, "\n") . $line;
             $raw .= '--' . $boundary . $line;
         }
        
         // echo '<pre>';
         // die(print_r($raw));
     } else {
         */
         $raw .= "MIME-Version: 1.0" . $line . $line;
         $raw .= $message_text;
        //  $raw->body .= $message_text;

    //  }
    //  dd($raw);
     $message->setRaw(rtrim(strtr(base64_encode($raw), '+/', '-_'), '='));
     try {
         $service->users_messages->send("me", $message);
     } catch (Exception $e) {
         print 'An error occurred: ' . $e->getMessage();
     }
 }


 function enviar($htmlMessage)
 {
    // return print_r(__DIR__."/Turka Link IP 21-8-22.png");die;

    $cliente =  getClient();
     if ($cliente->getAccessToken()) {
         $service = new Google_Service_Gmail($cliente);
         try {
            $mail = new PHPMailer();
            $mail->CharSet = "UTF-8";
            $mail->From = "shohan.office1@gmail.com" ;
            $mail->FromName = "Coder71" ;
            $mail->AddAddress("shohan@coder71.com");
            $mail->AddReplyTo("shohan@coder71.com");
            $mail->Subject = "Test Body";
            $mail->Body = strip_tags(htmlspecialchars_decode($htmlMessage));
            $mail->addEmbeddedImage(__DIR__."/Turka Link IP 21-8-22.png","Turka Link IP 21-8-22");
            $mail->preSend();
            $mail->isHTML();
            // $mail->strip_tags()
            $mime = $mail->getSentMIMEMessage();
            $mime = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
            $mensaje = new Google_Service_Gmail_Message();
            $mensaje->setRaw($mime);
            $service->users_messages->send('me', $mensaje);
            $r = 1;
        } catch (Exception $e) {
            print $e->getMessage();
            $r = 0;
        }
     } else {
         $r = -1;
     }
     return $r;
 }

 enviar($htmlMessage);