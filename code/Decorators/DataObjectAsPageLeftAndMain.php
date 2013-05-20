<?php
/**
 * Fixes URL segment editor issues in model admin without breaking Pages
 * 
 * Note. This will stop translation of URL segment field buttons.
 * 
 */
class DataObjectAsPageLeftAndMain extends Extension{

	public function init() {
		Requirements::block(CMS_DIR . '/javascript/SiteTreeURLSegmentField.js');
	    Requirements::javascript(MOD_DOAP_DIR . '/javascript/SiteTreeURLSegmentField_modeladmin.js');
	}

}