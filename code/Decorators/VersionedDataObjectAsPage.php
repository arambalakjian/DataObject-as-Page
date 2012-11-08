<?php

class VersionedDataObjectAsPage extends DataExtension{
	
	static $db = array(
		"Status" => "Varchar"	
	);
	
	static $summary_fields = array(
		'Status' => 'Status'
	);	
	
	static $defaults = array(
		'Status' => 'Draft'
	);	

	static $versioning = array(
		"Stage",  "Live"
	);
}