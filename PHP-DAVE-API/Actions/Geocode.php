<?php
/***********************************************
DAVE PHP API
https://github.com/evantahler/PHP-DAVE-API
Evan Tahler | 2011

I will use the MaxMind database to return geographic information for users based on IP address.
***********************************************/

// Geocoding Steps
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
if ($ERROR == 100){ require("CheckSafetyString.php"); }
if ($ERROR == 100)
{
	include("MaxMind/geoipcity.inc");
	include("MaxMind/geoipregionvars.php");
	$gi = geoip_open("MaxMind/GeoLiteCity.dat",GEOIP_STANDARD);
	$record = geoip_record_by_addr($gi,$IP);
	
	$country_code = $record->country_code;
	$country_code3 = $record->country_code3;
	$country_name = $record->country_name;
	$region = $record->region;
	$region_name = $GEOIP_REGION_NAME[$record->country_code][$record->region];
	$city = $record->city;
	$postal_code = $record->postal_code;
	$dma_code = $record->dma_code;
	$area_code = $record->area_code;
	
	require_once('MaxMind/timezone.php');
	$localTimeZone = get_time_zone($country_code,$region);
	date_default_timezone_set($localTimeZone);
	setlocale(LC_TIME, $country_code3);
	$LocalTime = strftime("%T %F");
	date_default_timezone_set($systemTimeZone);
	setlocale(LC_TIME, "C");
	$SystemTime = strftime("%T %F");
	
	geoip_close($gi);
	
	if ($country_code3 != "")
	{
		$OUTPUT['IP'] = $IP;
		$OUTPUT['country_code3'] = $country_code3;
		$OUTPUT['region_name'] = $region_name;
		$OUTPUT['city'] = $city;
		$OUTPUT['localTimeZone'] = $localTimeZone;
		$OUTPUT['LocalTime'] = $LocalTime;
		$OUTPUT['systemTimeZone'] = $systemTimeZone;
		$OUTPUT['SystemTime'] = $SystemTime;
	}
	else
	{
		$ERROR = "We cannot locate the IP Address ".$IP;
	}
}

?>