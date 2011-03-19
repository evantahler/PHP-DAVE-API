<?php

function validstatezip($state, $zip5)
{
   $allstates = array (
     "AK" => array ("9950099929"),
     "AL" => array ("3500036999"),
     "AR" => array ("7160072999", "7550275505"),
     "AZ" => array ("8500086599"),
     "CA" => array ("9000096199"),
     "CO" => array ("8000081699"),
     "CT" => array ("0600006999"),
     "DC" => array ("2000020099", "2020020599"),
     "DE" => array ("1970019999"),
     "FL" => array ("3200033999", "3410034999"),
     "GA" => array ("3000031999"),
     "HI" => array ("9670096798", "9680096899"),
     "IA" => array ("5000052999"),
     "ID" => array ("8320083899"),
     "IL" => array ("6000062999"),
     "IN" => array ("4600047999"),
     "KS" => array ("6600067999"),
     "KY" => array ("4000042799", "4527545275"),
     "LA" => array ("7000071499", "7174971749"),
     "MA" => array ("0100002799"),
     "MD" => array ("2033120331", "2060021999"),
     "ME" => array ("0380103801", "0380403804", "0390004999"),
     "MI" => array ("4800049999"),
     "MN" => array ("5500056799"),
     "MO" => array ("6300065899"),
     "MS" => array ("3860039799"),
     "MT" => array ("5900059999"),
     "NC" => array ("2700028999"),
     "ND" => array ("5800058899"),
     "NE" => array ("6800069399"),
     "NH" => array ("0300003803", "0380903899"),
     "NJ" => array ("0700008999"),
     "NM" => array ("8700088499"),
     "NV" => array ("8900089899"),
     "NY" => array ("0040000599", "0639006390", "0900014999"),
     "OH" => array ("4300045999"),
     "OK" => array ("7300073199", "7340074999"),
     "OR" => array ("9700097999"),
     "PA" => array ("1500019699"),
     "RI" => array ("0280002999", "0637906379"),
     "SC" => array ("2900029999"),
     "SD" => array ("5700057799"),
     "TN" => array ("3700038599", "7239572395"),
     "TX" => array ("7330073399", "7394973949", "7500079999", "8850188599"),
     "UT" => array ("8400084799"),
     "VA" => array ("2010520199", "2030120301", "2037020370", "2200024699"),
     "VT" => array ("0500005999"),
     "WA" => array ("9800099499"),
     "WI" => array ("4993649936", "5300054999"),
     "WV" => array ("2470026899"),
     "WY" => array ("8200083199"));

// if you use a drop down list for state selection, ensuring valid data,
// isset is not needed.  (Warnings can not be turned off with: @foreach...)

   if (isset($allstates[$state]))
      {
      foreach($allstates[$state] as $ziprange)
        {
        if (($zip5 >= substr($ziprange, 0, 5)) && ($zip5 <= substr($ziprange,5)))
           {
           $valid = "TRUE";
           return ($valid);  // on match, jump out of foreach early :)
           }
        }
      }
   $valid = "FALSE"; 
   return ($valid);
}

?>