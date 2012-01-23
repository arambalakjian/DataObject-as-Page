<?php

class DoapSearch_Controller extends Page_Controller 
{
	static $allowed_actions = array(
		'SearchForm'
	);

	function SearchForm() 
	{
		$searchText =  'Search';

        if($this->request) {
			$searchText = $this->request->getVar('Search');
        }
	
		$fields = new FieldSet(
			$TextField = new TextField('Search')
		);

		//Action
		$submit = new FormAction("results", 'Go');
		
		$actions = new FieldSet($submit);

        $form = new DoapSearchForm($this, 'SearchForm', $fields, $actions);
		$form->addSearchableClasses(array('DataObjectAsPage'));
        
		if($this->Link()) 
		{
        	$form->setFormAction($this->Link() . 'SearchForm');
        }

        return $form;
    }	
	
	public function Link()
	{
		return Director::baseURL() . 'search/';
	}

	
	public function getSearchQuery($data = null) {
		// legacy usage: $data was defaulting to $_REQUEST, parameter not passed in doc.silverstripe.com tutorials
		$data = $_REQUEST;

		return (isset($data['Search'])) ?  Convert::raw2xml($data['Search']) : '';
	}	
}