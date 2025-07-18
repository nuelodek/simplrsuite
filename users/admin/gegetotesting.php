<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.infobip.com/whatsapp/1/message/template',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "messages":[
        {
            "from":"447860099299",
            "to":"2349168413719",
            "messageId":"'.uniqid().'",
            "content":{
                "templateName":"test_whatsapp_template_en",
                "templateData":{
                    "body":{
                        "placeholders":["Mayu"]
                    }
                },
                "language":"en"
            }
        }
    ]
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: App 0a9fcbc5a2b85257bff8899d4683cd23-c3479b5e-81a9-4882-858c-364b9ea5bb68',
    'Content-Type: application/json',
    'Accept: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;