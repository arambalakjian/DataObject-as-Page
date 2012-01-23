<?php
/*
 * Base class for DataObjects that behave like pages
 * 
 */
class DataObjectAsPage extends DataObject {
	
	static $listing_page_class = 'DataObjectAsPageHolder';
	
	static $db = array (
		'Status'=>'Enum("Published,Draft")',
		'URLSegment' => 'Varchar(100)',
		'Title' => 'Varchar(255)',
		'MetaTitle' => 'Varchar(255)',
		'MetaDescription' => 'Varchar(255)',
		'Content' => 'HTMLText'
	);
	
	static $defaults = array(
		'Title'=>'New Item',
		'URLSegment' => 'new-item',
		'Status' => 'Draft'
	);
	
	static $summary_fields = array(
		'Title' => 'Title',
		'URLSegment' => 'URLSegment',
		'Status' => 'Status'
	);

	static $allowed_actions = array(
		'doPublish',
		'doUnpublish'
	);

	public static $default_sort = 'Created DESC';

	//Return the Title for use in Menu2
	public function MenuTitle()
	{
		return $this->Title;
	}
	
	//Better Search (Requires Better Search Module)
	static $indexes = array( 
        "URLSegment" => true	
	);

	//Chek if current user can view
	public function canView($member = null)
	{
		//If this is draft check for permissions to view draft content
		//getSearchResultItem is needed to ensure unpublished items don't show up in search results		
		if($this->Status == 'Draft' || ($this->getSearchResultItem() && $this->getSearchResultItem()->Status == 'Draft'))
		{
			return (Permission::check('VIEW_DRAFT_CONTENT') && Versioned::current_stage() == 'Stage');
		}		
		elseif(Controller::curr()->hasMethod("canView"))
		{
			//Otherwise return the parent listing pages view permission
			return Controller::curr()->canView();
		}
	}

    //Create duplicate button
    public function getCMSActions()
    {
        $Actions = parent::getCMSActions();
         
		 if($this->Status == 'Draft')
		 {
	        //Create the new action
	        $PublishAction = FormAction::create('doPublish', 'Save & Publish');
	        $PublishAction->describe("Publish this item");		 	
		 }
		 else
		 {
	        //Create the new action
	        $PublishAction = FormAction::create('doUnpublish', 'Unpublish');
	        $PublishAction->describe("Unpublish this item");			 	
		 }
         
        //add it to the existing actions
        $Actions->insertFirst($PublishAction);
 
 		//Create the new action
        $DuplicateAction = FormAction::create('duplicate', 'Duplicate Object');
        $DuplicateAction->describe("Duplicate this item");
         
        //add it to the existing actions
        $Actions->insertFirst($DuplicateAction); 		
         
        return $Actions;
    } 
		
	public function getCMSFields() 
	{
		$fields = parent::getCMSFields();

		//Add the status/view link
		if($this->ID)
		{
			if($this->Status == 'Published')
			{
				$SiteView = 'Live';
				$Color = '#000';
			}
			else
			{
				$SiteView = 'Stage';
				$Color = '#E88F31';
			}
				
			$fields->insertFirst(new LiteralField('', 
				'<h3 style=" Padding: 5px; border: 1px solid #ccc;background:#fff;margin-bottom: 10px;">Status: <strong style="font-size: 16px;color: '.$Color.';">'. $this->Status .'</strong> - <a target="_blank" href="' . $this->Link('?stage=' . $SiteView) . '">View</a></h3>'
			));		
		}

		$fields->addFieldToTab('Root.Main', new TextField('Title'));	

		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Content'));	
	
		//Remove Scafolded fields
		$fields->removeFieldFromTab('Root.Main', 'URLSegment');
		$fields->removeFieldFromTab('Root.Main', 'Status');
		

		//URLSegment
		$fields->addFieldToTab('Root.Metadata', 
			new FieldGroup("URL",
				new LabelField('BaseUrlLabel', Director::absoluteBaseURL() . 'listing-page/show/'),
				new UniqueRestrictedTextField("URLSegment",
					"URL Segment",
					"Event",
					"Another event is using that URL. URL must be unique for each product",
					"[^a-z0-9-]+",
					"-",
					"URLs can only be made up of letters, digits and hyphens.",
					"",
					"",
					"",
					50
				),
				new LabelField('TrailingSlashLabel',"/")
			)
		);

		//MetaData fields
		$fields->addFieldToTab('Root.Metadata', new TextField('MetaTitle', 'Meta Title'));
		$fields->addFieldToTab('Root.Metadata', new TextField('MetaDescription', 'Meta Description'));	
				
		return $fields;
	}

	/*
	 * Get the listing page to view this Event on (used in Link functions below)
	 */
	function getListingPage(){
		
		if(Controller::curr()->ClassName == $this->stat('listing_class'))
		{
			$ListingPage = Controller::curr();
		}
		else
		{
			//Needed for search results to work ($this->EventTypeID returns nothing)
			$Item = DataObject::get_by_id($this->ClassName, $this->ID);
			
			$ListingPage = DataObject::get_one($this->stat('listing_class'));
		}
		
		return $ListingPage;		
	}
	
	/*
	 * Generate the link to this DataObject Item page
	 */
	function Link($ExtraURLVar = null)
	{
		//Hack for search results
		if($Item =  DataObject::get_by_id(get_class($this), $this->ID))
		{
			//Build link
			if($ListingPage = $Item->getListingPage())
			{
				return $ListingPage->Link('show/' . $Item->URLSegment . '/' . $ExtraURLVar);		
			}			
		}
	}
	
	function absoluteLink($appendVal = null)
	{
		return $this->getListingPage()->absoluteLink('show/' . $this->URLSegment . $appendVal);
	}

	/*
	 * Return the correct linking mode, for use in menus
	 */
	public function LinkingMode()
    {
        //Check that we have a controller to work with and that it is a listing page
        if(Controller::CurrentPage() && Controller::CurrentPage()->ClassName == $this->stat('listing_page_class'))
        {
            //check that the action is 'show' and that we have an item to work with
            if(Controller::CurrentPage()->getAction() == 'show' && $Item = Controller::CurrentPage()->getCurrentItem())
            {
                return ($Item->ID == $this->ID) ? 'current' : 'link';
            }
        }
    }

	/*
	 * Set URLSegment to be unique on write
	 */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
	
		//Set MetaData
		if(!$this->MetaTitle)
		{
			$this->MetaTitle = $this->Title;
		}
		
        // If there is no URLSegment set, generate one from Title
        if((!$this->URLSegment || $this->URLSegment == 'new-item') && $this->Title != 'New Item') 
        {
            $this->URLSegment = SiteTree::generateURLSegment($this->Title);
        } 
        else if($this->isChanged('URLSegment')) 
        {
            // Make sure the URLSegment is valid for use in a URL
            $segment = preg_replace('/[^A-Za-z0-9]+/','-',$this->URLSegment);
            $segment = preg_replace('/-+/','-',$segment);
              
            // If after sanitising there is no URLSegment, give it a reasonable default
            if(!$segment) {
                $segment = "item-$this->ID";
            }
            $this->URLSegment = $segment;
        }
  
        // Ensure that this object has a non-conflicting URLSegment value.
        $count = 2;

		$URLSegment = $this->URLSegment;
		$ID = $this->ID;

        while($this->LookForExistingURLSegment($URLSegment, $ID)) 
        {     	
            $URLSegment = preg_replace('/-[0-9]+$/', null, $URLSegment) . '-' . $count;
            $count++;
        }
		
		$this->URLSegment = $URLSegment;
    }
	
    //Test whether the URLSegment exists already on another Item
    public function LookForExistingURLSegment($URLSegment, $ID)
    {
		$Where = "`DataObjectAsPage`.`URLSegment` = '" . $URLSegment . "' AND `DataObjectAsPage`.`ID` != $ID";
       	$Item = (DataObject::get_one('DataObjectAsPage', $Where));
		
		return $Item;    	
    }

	public function getSingularName()
	{
		return $this->stat('singular_name');
	}	

	//Strange search needs this to have access to all fields in results
	public function getSearchResultItem()
	{
		return DataObject::get_by_id($this->ClassName, $this->ID);
	}
	
	public function getMetaTitle()
	{
		if($page = Controller::Curr())
		{
			return $this->Title . ' - ' . $page->Title;
		}
		else
		{
			return $this->MetaTitle;
		}
	}	
				
}
