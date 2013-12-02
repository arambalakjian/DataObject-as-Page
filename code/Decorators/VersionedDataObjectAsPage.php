<?php

class VersionedDataObjectAsPage extends DataExtension{
	
	private static $summary_fields = array(
		'Status' => 'Status'
	);	

	private static $versioning = array(
		"Stage",  "Live"
	);
}