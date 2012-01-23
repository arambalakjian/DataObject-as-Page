<?php 

class DoapPage extends Extension
{
	function extraStatics() 
	{
		return array(
		);		
	}		
	/**
	 * Custom Site search form
	 */
	function SearchForm() 
	{
		$form = Singleton("DoapSearch_Controller")->SearchForm();
		
		return $form;
	}
}