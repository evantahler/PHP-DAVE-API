<?php


function GetGeoFromIP($IP)
{
	include("MaxMind/geoipcity.inc");
	include("MaxMind/geoipregionvars.php");
	
	$gi = geoip_open("MaxMind/GeoLiteCity.dat",GEOIP_STANDARD);
	
	$record = geoip_record_by_addr($gi,$IP);
	
	// Here are all the options that could be done...
	
	//record->country_code
	//$record->country_code3
	//$record->country_name
	//print $record->region
	//$GEOIP_REGION_NAME[$record->country_code][$record->region]
	//print $record->city
	//print $record->postal_code
	//print $record->latitude
	//print $record->longitude
	//print $record->dma_code
	//print $record->area_code
	
	$LAT = $record->latitude;
	$LONG = $record->longitude;
	
	geoip_close($gi);
	
	return array($LAT,$LONG);

}

?>