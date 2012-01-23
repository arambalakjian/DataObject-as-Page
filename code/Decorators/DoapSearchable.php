<?php

class DoapSearchable extends Extension
{
	function extraStatics() 
	{
		return 	array(
			'indexes' => array(
				"SearchFields" => "fulltext (Title, MetaDescription, Content)", 
				"TitleSearchFields" => "fulltext (Title)"
			)
		);	
	}

	//Strange search needs this to have access to all fields in results
	public function getSearchResultItem()
	{
		return DataObject::get_by_id($this->owner->ClassName, $this->owner->ID);
	}
	
}
