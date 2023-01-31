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

// if (php_sapi_name() != 'cli') {
//     throw new Exception('This application must be run on the command line.');
// }

use Google\Client;
use Google\Service\Gmail;

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


// Get the API client and construct the service object.
$client = getClient();
$service = new Gmail($client);

try{

    // Print the labels in the user's account.
    /*$user = 'me';
    $results = $service->users_labels->listUsersLabels($user);

    if (count($results->getLabels()) == 0) {
        print "No labels found.\n";
    } else {
        print "Labels:\n";
        foreach ($results->getLabels() as $label) {
            printf("- %s\n", $label->getName());
        }
    }*/

    function encodeRecipients($recipient){
        $recipientsCharset = 'utf-8';
        if (preg_match("/(.*)<(.*)>/", $recipient, $regs)) {
            $recipient = '=?' . $recipientsCharset . '?B?'.base64_encode($regs[1]).'?= <'.$regs[2].'>';
        }
        return $recipient;
    }

    function createMessage($sender, $to, $subject, $messageText) {
        $message = new Google_Service_Gmail_Message();
        
        /*$rawMessageString = "From: <{$sender}>\r\n";
        $rawMessageString .= "To: <{$to}>\r\n";
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $rawMessageString .= "{$messageText}\r\n";*/

        //$strMailContent = 'This is a test mail which is sent via using Gmail API client library.<br/><br/><br/>Thanks,<br/>GMail API Team.';
        $strMailContent = htmlspecialchars_decode($messageText);
        // $strMailTextVersion = strip_tags($strMailContent, '');
        $strMailTextVersion = strip_tags($messageText, '');

        $strRawMessage = "";
        $boundary = uniqid(rand(), true);
        $subjectCharset = $charset = 'utf-8';
        $strToMailName = 'Rajib';
        $strToMail = $to;
        $strSesFromName = 'Raffles Agent';
        $strSesFromEmail = $sender;
        $strSubject = 'Test mail using GMail API - with attachment - ' . date('M d, Y h:i:s A');

        $strRawMessage .= 'To: ' . encodeRecipients($strToMailName . " <" . $strToMail . ">") . "\r\n";
        $strRawMessage .= 'From: '. encodeRecipients($strSesFromName . " <" . $strSesFromEmail . ">") . "\r\n";

        $strRawMessage .= 'Subject: =?' . $subjectCharset . '?B?' . base64_encode($strSubject) . "?=\r\n";
        $strRawMessage .= 'MIME-Version: 1.0' . "\r\n";
        $strRawMessage .= 'Content-type: Multipart/Alternative; boundary="' . $boundary . '"' . "\r\n";
        $strRawMessage .= 'Content-type: Multipart/Mixed; boundary="' . $boundary . '"' . "\r\n";

        
        $filePath = __DIR__."/Turka Link IP 21-8-22.png";
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $mimeType = finfo_file($finfo, $filePath);
        $fileName = 'Turka Link IP 21-8-22.png';
        $fileData = base64_encode(file_get_contents($filePath));

        $strRawMessage .= "\r\n--{$boundary}\r\n";
        $strRawMessage .= 'Content-Type: '. $mimeType .'; name="'. $fileName .'";' . "\r\n";            
        $strRawMessage .= 'Content-ID: <' . $strSesFromEmail . '>' . "\r\n";            
        $strRawMessage .= 'Content-Description: ' . $fileName . ';' . "\r\n";
        $strRawMessage .= 'Content-Disposition: attachment; filename="' . $fileName . '"; size=' . filesize($filePath). ';' . "\r\n";
        $strRawMessage .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
        $strRawMessage .= chunk_split(base64_encode(file_get_contents($filePath)), 76, "\n") . "\r\n";
        $strRawMessage .= '--' . $boundary . "\r\n";

        

        $strRawMessage .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
        $strRawMessage .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $strRawMessage .= $strMailContent . "\r\n";
        
        
        $rawMessage = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
        //$rawMessage = strtr(base64_encode($strRawMessage), array('+' => '-', '/' => '_'));
        $message->setRaw($rawMessage);
        return $message;
    }

    function sendMessage($service, $userId, $message) {
        try {
          $message = $service->users_messages->send($userId, $message);
          print 'Message with ID: ' . $message->getId() . ' sent.';
          return $message;
        } catch (Exception $e) {
          print 'An error occurred: ' . $e->getMessage();
        }
       
        return null;
    }

    $htmlMessage = <<<BB
    <table role="presentation" width="100%">
    <tr>
 
      <td bgcolor="#00A4BD" align="center" style="color: white;">
    
     <img alt="Flower" src="https://hs-8886753.f.hubspotemail.net/hs/hsstatic/TemplateAssets/static-1.60/img/hs_default_template_images/email_dnd_template_images/ThankYou-Flower.png" width="400px" align="middle">
        
        <h1> Welcome! </h1>
        
      </td>
</table>
BB;

    $createMail = createMessage('agent@rafflestag.sg', 'agent@rafflestag.sg', "Test Mail From Gmail API", $htmlMessage);
    sendMessage($service, 'me', $createMail);


}
catch(Exception $e) {
    // TODO(developer) - handle error appropriately
    echo 'Message: ' .$e->getMessage();
}