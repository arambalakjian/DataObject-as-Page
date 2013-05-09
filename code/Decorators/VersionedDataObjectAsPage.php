<?php

class VersionedDataObjectAsPage extends DataExtension{
	
	private static $db = array(
		"Status" => "Varchar"	
	);
	
	private static $summary_fields = array(
		'Status' => 'Status'
	);	
	
	private static $defaults = array(
		'Status' => 'Draft'
	);	

	private static $versioning = array(
		"Stage",  "Live"
	);
}