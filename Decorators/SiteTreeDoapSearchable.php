<?php 

class SiteTreeDoapSearchable extends SiteTreeDecorator
{
	function extraStatics() 
	{
		return array(
			'indexes' => array(
				"SearchFields" => "fulltext (Title, MenuTitle, Content, MetaTitle, MetaDescription, MetaKeywords)",
				"TitleSearchFields" => "fulltext (Title)",
				"URLSegment" => true
			)
		);		
	}	
	
}
