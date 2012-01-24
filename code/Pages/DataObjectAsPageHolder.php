<?php

class DataObjectAsPageHolder extends Page 
{
	static $hide_ancestor = 'DataObjectAsPageHolder';
	
	static $db = array(
		'ItemsPerPage' => 'Int',
		'ItemsAsChildren' => 'Boolean',
		'Paginate' => 'Boolean'
	);
	
	static $defaults = array(
		'ItemsPerPage' => 10,
		'Paginate' => true,
		'ItemsAsChildren' => false
	);
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Behaviour', new HeaderField('DOAP', 'DataObject Item Display'));
		$fields->addFieldToTab('Root.Behaviour', new CheckboxField('Paginate', 'Paginate Items'));
		$fields->addFieldToTab('Root.Behaviour', new NumericField('ItemsPerPage', 'Items per page (if paginated)'));
		$fields->addFieldToTab('Root.Behaviour', new CheckboxField('ItemsAsChildren', 'Show DataObjects as Children of this page'));
		
		return $fields;
	}
	
	/*
	 * Produce the correct breadcrumb trail for use on the DataObject Item Page
	 */ 
	public function ItemBreadcrumbs($Item, $Other = Null) 
	{
		$Breadcrumbs = parent::Breadcrumbs($maxDepth = 20, $unlinked = false, $stopAtPageType = false, $showHidden = false);

		$Parts = explode(self::$breadcrumbs_delimiter, $Breadcrumbs);

		$NumOfParts = count($Parts);
		
		$Parts[$NumOfParts-1] = ("<a href=\"" . $this->Link() . "\">" . Convert::raw2xml($this->Title) . "</a>");
		
		if($Other)
		{
			$Parts[$NumOfParts] = "<a href=\"" . $Item->Link() . "\">" . Convert::raw2xml($Item->Title) . "</a>"; 
			$Parts[] = Convert::raw2xml($Other);
		}
		else
		{
			$Parts[$NumOfParts] = Convert::raw2xml($Item->Title); 
		}
		
		return implode(self::$breadcrumbs_delimiter, $Parts);
	}	
	
	/*
	 * Generate custom metatags to display on the DataObject Item page
	 */ 
	 public function ItemMetaTags($item = null) 
	{
	    $tags = parent::MetaTags(false);
		
		//explode to find each meta tag
		$tagArray = explode('<meta', $tags);
		for($i=0; $i<count($tagArray); $i++)
		{
			//check if tag is a description: replace if it is
			if(strpos($tagArray[$i], 'name="description"'))
			{
				$tagArray[$i] = " name=\"description\" content=\"" . Convert::raw2att($item->MetaDescription) . "\" />\n";
			}
		}
		//rebuild string
	    $tags = implode('<meta', $tagArray);
		
		return $tags;
	}
		
	/*
	* Get Items which are to be displayed on this listing page
	*/
	public function FetchItems($ItemClass, $Filter = '', $Sort = Null, $Join = Null, $Limit = null)
	{
		//Set our status filter if in live mode
		if(Versioned::get_reading_mode() == 'Stage.Live')
		{
			if($Filter) $Filter .= ' AND ';
			
			$Filter .= "Status = 'Published'"; 
		}
		
		return DataObject::get($ItemClass, $Filter, $Sort, $Join, $Limit);
	}
	
	public function ItemCount($ItemClass = Null)
	{
		if($ItemClass && $Items = $this->FetchItems($ItemClass))
		{
			return $Items->TotalItems();
		}
		else
		{
			return 0;
		}
	}
	
	/*
	 * If ItemsAsChildren is enabled it returns the DataObjects as Children of this page
	 */
    public function Children()
    {
    	if($this->ItemsAsChildren)
		{
			return $this->FetchItems('DataObjectAsPage');	
		}
		else
		{
			return parent::Children();
		}
    }
	
	/*
	 * This is to prevent the DataObjects from being deleted when we unpublish the page if they are set as children
	 */
	public function onBeforeDelete()
    {
    	if($this->ItemsAsChildren)
		{
	        $CurrentVal = $this->get_enforce_strict_hierarchy();
	        $this->set_enforce_strict_hierarchy(false);
	
			parent::onBeforeDelete();
	
	        $this->set_enforce_strict_hierarchy($CurrentVal);
		}
		else
		{
			 parent::onBeforeDelete();
		}
    }   
}

class DataObjectAsPageHolder_Controller extends Page_Controller 
{
	//Class Of Object Listied on this page
	static $item_class = 'DataObjectAsPage';
	static $item_sort = 'Created DESC';
	
	public static $allowed_actions = array(
		'show'
	);

	/*
	 * Returns the items to list on this page pagintated or Limited
	 */
	function Items($Limit = null)
	{
		//Set Pagination if no limit set
		if(!$Limit && $this->Paginate)
		{
			//Pagination 
			if(!isset($_GET['start']) || !is_numeric($_GET['start']) || (int)$_GET['start'] < 1){
				$_GET['start'] = 0;
			}
			
			$Offset = (int)$_GET['start'];	
			
			$Limit = "{$Offset}, {$this->ItemsPerPage}" ;			
		}

		//Set custom filter
		$Where = ($this->hasMethod('getItemsWhere')) ? $this->getItemsWhere() : Null;
		
		//Set custom sort		
		$Sort = ($this->hasMethod('getItemsSort')) ? $this->getItemsSort() : $this->stat('item_sort');
		
		//Set custom join	
		$Join = ($this->hasMethod('getItemsJoin')) ? $this->getItemsJoin() : Null;
		
		//QUERY
		$items = $this->FetchItems($this->Stat('item_class'), $Where, $Sort, $Join, $Limit);

		return $items;
	}
	
	/*
	 * Get the current DataObject Item from the URL if one exists
	 */
	public function getCurrentItem($itemID = null)
	{
		if($itemID)
		{
			return DataObject::get_by_id($this->stat('item_class'), $itemID);
		}
		elseif($URL = $this->URLParams['ID'])
		{
			return DataObject::get_one($this->stat('item_class'), "URLSegment = '" . $URL . "'");
		}		
	}
	
	/*
	 * Renders the detail page for the current item passed into the URLs ID 
	 * 
	 * Uses DataObjectAsPageViewer_show.ss by default
	 */
	function show()
	{
		if(($item = $this->getCurrentItem()) && $this->getCurrentItem()->canView())
		{	
			$data = array(
				'Item' => $item,
				'Breadcrumbs' => $this->ItemBreadcrumbs($item),
				'MetaTitle' => $item->MetaTitle,
				'MetaTags' => $this->ItemMetaTags($item),
				'BackLink' => base64_decode($this->request->getVar('backlink'))
			);
			
			return $this->Customise(new ArrayData($data));					
		}
		else
		{
			return $this->httpError(404);
		}
	}
}