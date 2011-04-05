<?php

function httpPost($Url, $PostRequest, $HTTP_headers)
{
   $ch=curl_init();
   curl_setopt($ch, CURLOPT_URL, $Url);
   // init
   curl_setopt($ch, CURLOPT_HEADER, 1);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array($HTTP_headers));
   // headers
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // I don't want headers back from the serrver...
   curl_setopt($ch, CURLOPT_POST, 1) ;
   curl_setopt($ch, CURLOPT_POSTFIELDS, $PostRequest);
   // post data
   //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);   // Don't need SSL!
   $result = curl_exec($ch);
   curl_close($ch);
   return $result;
} 

function httpsPost($Url, $PostRequest, $HTTP_headers)
{
   $ch=curl_init();
   curl_setopt($ch, CURLOPT_URL, $Url);
   // init
   curl_setopt($ch, CURLOPT_HEADER, 1);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array($HTTP_headers));
   // headers
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // I don't want headers back from the serrver...
   curl_setopt($ch, CURLOPT_POST, 1) ;
   curl_setopt($ch, CURLOPT_POSTFIELDS, $PostRequest);
   // post data
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   $result = curl_exec($ch);
   curl_close($ch);
   return $result;
} 

?>