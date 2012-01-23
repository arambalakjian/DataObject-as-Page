<?php

//So that we don't need a certain root folder name - Thanks Dan Hensby: https://github.com/dhensby/
define('MOD_DOAP_PATH',rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));
$folders = explode(DIRECTORY_SEPARATOR,MOD_DOAP_PATH);
define('MOD_DOAP_DIR',rtrim(array_pop($folders),DIRECTORY_SEPARATOR));
unset($folders);


Object::add_extension('SiteTree', 'SiteTreeDoapSearchable');
Object::add_extension('DataObjectAsPage', 'DoapSearchable');
Object::add_extension('Page_Controller', 'DoapPage');

//DataObject::add_extension('File', 'FileDoapSearchable');

//Sitemap
Director::addRules(10, array(
	'search' => 'DoapSearch_Controller'
));