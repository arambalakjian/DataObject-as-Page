<?php
/*
 * Base class for DataObjects that behave like pages
 * 
 */
class DataObjectAsPage extends DataObject {
	
	static $listing_page_class = 'DataObjectAsPageHolder';
	
	static $db = array (
		"Status" => "Varchar",
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

	static $extensions = array(
		"Versioned('Stage', 'Live')"
	);

	//Better Search (Requires Better Search Module)
	static $indexes = array( 
		"SearchFields" => "fulltext (Title, MetaDescription, Content)", 
		"TitleSearchFields" => "fulltext (Title)",
        "URLSegment" => true	
	);

	static $frontend_searchable_fields = array(
		'Title',
		'MetaDescription',
		'Content'
	);
	
	static $search_heading = "Title"; 
	
	static $search_content = "Content";

	public static $default_sort = 'Created DESC';

	//Return the Title for use in Menu2
	public function MenuTitle()
	{
		return $this->Title;
	}

	//Chek if current user can view
	public function canView($member = null)
	{
		//If this is draft check for permissions to view draft content
		//getSearchResultItem is needed to ensure unpublished items don't show up in search results		
		if($this->Status == 'Draft')
		{
			return (Permission::check('VIEW_DRAFT_CONTENT') && Versioned::current_stage() == 'Stage');
		}		
		elseif(Controller::curr()->hasMethod("canView"))
		{
			//Otherwise return the parent listing pages view permission
			return Controller::curr()->canView();
		}
	}

	//Chek if current user can view
	public function canPublish($member = null)
	{
		return true;
	}

	//Chek if current user can view
	public function canUnPublish($member = null)
	{
		return true;
	}
	
    //Create duplicate button
	public function getCMSActions()
	{
		$Actions = parent::getCMSActions();
		
		
		//Create the Save & Publish action
		$PublishAction = FormAction::create('doPublish', 'Save & Publish');
		$PublishAction->describe("Publish this item");	      
		$Actions->insertFirst($PublishAction);
		  
		 if($this->Status != 'Draft')
		 {
		    //Create the Unpublish action
		    $unPublishAction = FormAction::create('doUnpublish', 'Unpublish');
		    $unPublishAction->describe("Unpublish this item");
			$Actions->insertFirst($unPublishAction);		 	
		 }
		 
		//Create the Duplicate action
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
			$color = '#E88F31';
			$links = '<a target="_blank" href="' . $this->Link('?stage=Stage') . '">View Draft</a>';
			$status = $this->Status;
			
			if($this->Status == 'Published')
			{
				$color = '#000';
				$links .= ' | <a target="_blank" href="' . $this->Link('?stage=Live') . '">View Published</a>';
				
				if($this->hasChangesOnStage())
				{
					$status .= ' (changed)';
					$color = '#428620';
				}
			}

			$fields->insertFirst(new LiteralField('', 
				'<h3 style=" Padding: 5px; border: 1px solid #ccc;background:#fff;margin-bottom: 10px;">
				<strong style="font-size: 16px;color: '.$color.';">'. $status . '</strong> - ' . $links .'</h3>'
			));
		}

		$fields->addFieldToTab('Root.Main', new TextField('Title'));	

		$fields->addFieldToTab('Root.Main', new HTMLEditorField('Content'));	
	
		//Remove Scafolded fields
		$fields->removeFieldFromTab('Root.Main', 'URLSegment');
		$fields->removeFieldFromTab('Root.Main', 'Status');
		$fields->removeFieldFromTab('Root.Main', 'Version');
		$fields->removeByName('Versions');
		

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

	/**
	 * Create a duplicate of this node. Doesn't affect joined data - create a
	 * custom overloading of this if you need such behaviour.
	 *
	 * @return SiteTree The duplicated object.
	 */
	 public function duplicate($doWrite = true) 
	 {
		$item = parent::duplicate(false);
		$this->extend('onBeforeDuplicate', $item);
 
        //Change the title so we know we are looking at the copy
        $item->Title = 'Copy of ' . $this->Title;
        $item->Status = 'Draft';
        		
		if($doWrite) {
			$item->write();
		}
		
		$this->extend('onAfterDuplicate', $page);
		
		return $item;
	}
	
	/**
	 * Publish this page.
	 * 
	 * @uses SiteTreeDecorator->onBeforePublish()
	 * @uses SiteTreeDecorator->onAfterPublish()
	 */
	function doPublish() {
		if (!$this->canPublish()) return false;
		
		$original = Versioned::get_one_by_stage("DataObjectAsPage", "Live", "\"DataObjectAsPage\".\"ID\" = $this->ID");
		if(!$original) $original = new DataObjectAsPage();

		// Handle activities undertaken by decorators
		$this->invokeWithExtensions('onBeforePublish', $original);
		$this->Status = "Published";
		//$this->PublishedByID = Member::currentUser()->ID;
		$this->write();
		$this->publish("Stage", "Live");

		// Handle activities undertaken by decorators
		$this->invokeWithExtensions('onAfterPublish', $original);
		
		return true;
	}

	/**
	 * Unpublish this DataObject - remove it from the live site
	 * 
	 */
	function doUnpublish() 
	{
		if(!$this->ID) return false;
		if (!$this->canUnPublish()) return false;
		
		$this->extend('onBeforeUnpublish');
		
		$origStage = Versioned::current_stage();
		Versioned::reading_stage('Live');

		// This way our ID won't be unset
		$clone = clone $this;
		$clone->delete();

		Versioned::reading_stage($origStage);

		// If we're on the draft site, then we can update the status.
		// Otherwise, these lines will resurrect an inappropriate record
		if(DB::query("SELECT \"ID\" FROM \"DataObjectAsPage\" WHERE \"ID\" = $this->ID")->value()
			&& Versioned::current_stage() != 'Live') {
			$this->Status = "Draft";
			$this->write();
		}

		$this->extend('onAfterUnpublish');

		return true;
	}

	function doDelete() {
		
		$this->doUnpublish();
		
		$oldMode = Versioned::get_reading_mode();
		Versioned::reading_stage('Draft');

		$result = $this->delete();
				
		Versioned::set_reading_mode($oldMode);

		return $result;
	}

	/**
	 * Check whether this DO has changes which are not published
	 */
	public function hasChangesOnStage()
	{
		$latestPublishedVersion = $this->get_versionnumber_by_stage('DataObjectAsPage', 'Live', $this->ID);
		$latestVersion = $this->get_versionnumber_by_stage('DataObjectAsPage', 'Stage', $this->ID);
		
		return ($latestPublishedVersion < $latestVersion);
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

}
