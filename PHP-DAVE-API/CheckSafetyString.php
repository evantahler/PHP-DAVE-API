<?php

/***********************************************
Evan Tahler
evantahler@gmail.com
2010

This page will ensure that the the SafetyString is present and matches the right value.  If it doesn't, that means that this page (or the page that included it) wasn't launched from index.php (or another allowed page).  This will then set an error so that bad things don't happen

***********************************************/

if ($ERROR == 100)
{
	$OriginalSafeteyString = $SafetyString;
	include ("SafetyString.php"); // will override SafetyString with the correct value
	if ($OriginalSafeteyString != $SafetyString)
	{
		$ERROR = "This page was not launched in the appropriate manner.";	
	}
	// reset SafetyString vales to what they were for later checks
	$SafetyString = $OriginalSafeteyString;
}
	
?>