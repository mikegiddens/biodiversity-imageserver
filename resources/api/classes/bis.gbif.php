<?php

function gbifNameFinder($ocrValue)
{
	if($ocrValue=='')
		return false;
	$gbif = array();
	$ocrValue = urlencode($ocrValue);
	$gbifURL = "http://ecat-dev.gbif.org/tf?type=text&format=json&input=".$ocrValue;
	$data = file_get_contents($gbifURL);
	$array = json_decode($data,true);
	foreach($array['names'] as $names)
	{
		$gbif[] = $names['scientificName'];
	}
	return $gbif;
}

function gbifChecklistBank($scientificName)
{
	if($scientificName=='')
		return false;
	$gbifURL = "http://ecat-dev.gbif.org/ws/usage/?rkey=1012&rank=fgs&q=".$scientificName;
	$data = file_get_contents($gbifURL);
	$array = json_decode($data,true);
	if($array['success'])
	{
		$gbif['rank'] = $array['data'][0]['rank'];
		$gbif['taxonID'] = $array['data'][0]['taxonID'];
		return $gbif;
	}
	else
	{
		return false;
	}
}

function gbifFullRecord($taxonID)
{
	if($taxonID=='')
		return false;
	$gbifURL = "http://ecat-dev.gbif.org/ws/usage/".$taxonID;
	$data = file_get_contents($gbifURL);
	$array = json_decode($data,true);
	$gbif['canonicalName']=$array['data']['canonicalName'];
	$gbif['taxonomicStatus']=$array['data']['taxonomicStatus'];
	$gbif['higherTaxon']=$array['data']['higherTaxon'];
	return $gbif;
}

?>