<?php 

class FileDoapSearchable extends DataObjectDecorator
{
	function extraStatics() 
	{
		return 	array(
			'indexes' => array(
				"SearchFields" => "fulltext (Filename,Title,Content)"
			)
		);	
	}	
}