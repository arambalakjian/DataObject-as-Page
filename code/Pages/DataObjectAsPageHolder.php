<?php

class DataObjectAsPageHolder extends Page 
{
	private static $hide_ancestor = 'DataObjectAsPageHolder';
	
	private static $db = array(
		'ItemsPerPage' => 'Int',
		'ItemsAsChildren' => 'Boolean',
		'Paginate' => 'Boolean'
	);
	
	private static $defaults = array(
		'ItemsPerPage' => 10,
		'Paginate' => true,
		'ItemsAsChildren' => false
	);
	
	public function getSettingsFields()
	{
		$fields = parent::getSettingsFields();
		
		$fields->addFieldToTab('Root.Settings', new HeaderField('DOAP', 'DataObject Item Display'));
		$fields->addFieldToTab('Root.Settings', new CheckboxField('Paginate', 'Paginate Items'));
		$fields->addFieldToTab('Root.Settings', new NumericField('ItemsPerPage', 'Items per page (if paginated)'));
		$fields->addFieldToTab('Root.Settings', new CheckboxField('ItemsAsChildren', 'Show DataObjects as Children of this page'));
		
		return $fields;
	}
	
	/*
	* Get Items which are to be displayed on this listing page
	*/
	public function FetchItems($itemClass, $filter = null, $sort = Null, $limit = Null)
	{
		$results = $itemClass::get();
		
		if($filter)
		{
			if(is_array($filter))
			{
				foreach($filter as $key => $value)
				{
					if($key == "filterany" || $key == "filter" || $key == "where")
					{
						$results = $results->$key($value);
					}
				}
			}
			else
			{
				$results = $results->filter($filter);	
			}
		}
		
		if($sort)
		{
			$results = $results->sort($sort);
		}

		if($limit)
		{
			$results = $results->limit($limit);
		}
						
		return $results;
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
	
	/*
	 * If ItemsAsChildren is enabled it returns the DataObjects as Children of this page
	 */
    public function Children()
    {
    	if($this->ItemsAsChildren && (Controller::curr() instanceof DataObjectAsPageHolder_Controller))
		{
			return Controller::curr()->Items();	
		}
		else
		{
			return parent::Children();
		}
    }	  
}

class DataObjectAsPageHolder_Controller extends Page_Controller 
{
	//Class Of Object Listied on this page
	private static $item_class = 'DataObjectAsPage';
	private static $item_sort = 'Created DESC';
	
	private static $allowed_actions = array(
		'show'
	);
	
	/*
	 * Returns the items to list on this page pagintated or Limited
	 */
	public function Items($limit = null)
	{
		//Set custom filter
		$where = ($this->hasMethod('getItemsWhere')) ? $this->getItemsWhere() : Null;
		
		//Set custom sort		
		$sort = ($this->hasMethod('getItemsSort')) ? $this->getItemsSort() : $this->stat('item_sort');
		
		//QUERY
		$items = $this->FetchItems($this->Stat('item_class'), $where, $sort, $limit);

		//Paginate the list
		if(!$limit && $this->Paginate)
		{
			$items = new PaginatedList($items, $this->request);
			$items->setPageLength($this->ItemsPerPage);
		}

		$this->extend('updateItems', $items);

		return $items;
	}
	
	/*
	 * Get the current DataObject Item from the URL if one exists
	 */
	public function getCurrentItem($itemID = null)
	{
		$params = $this->request->allParams();
		$class =  $this->stat('item_class');		
		
		if($itemID)
		{
			$item = $class::get()->byID($itemID);

		}
		elseif(isset($params['ID']))
		{
			//Sanitize
			$URL = Convert::raw2sql($params['ID']);
			
			$item = $class::get()->filter("URLSegment", $URL)->first();
		}		
		$this->extend('updateCurrentItem', $item);
		return $item;
	}
	
	/*
	 * Renders the detail page for the current item passed into the URLs ID 
	 * 
	 * Uses DataObjectAsPageViewer_show.ss by default
	 */
	public function show()
	{
		if($item = $this->getCurrentItem())
		{
			if ($item->canView())
			{
				$data = array(
					'Item' => $item,
					'Breadcrumbs' => $item->Breadcrumbs(),
					'MetaTags' => $item->MetaTags(),
					'BackLink' => base64_decode($this->request->getVar('backlink'))
				);

				return $this->customise(new ArrayData($data));
			}
			else
			{
				return Security::permissionFailure($this);
			}
		}
		else
		{
			return $this->httpError(404);
		}
	}
}