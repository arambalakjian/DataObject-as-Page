<?php

//So that we don't need a certain root folder name - Thanks Dan Hensby: https://github.com/dhensby/
define('MOD_DOAP_PATH',rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));
$folders = explode(DIRECTORY_SEPARATOR,MOD_DOAP_PATH);
define('MOD_DOAP_DIR',rtrim(array_pop($folders),DIRECTORY_SEPARATOR));
unset($folders);

/*
 * Add this line to your _config.php to enable versioning on your DataObjectAsPage classes.
 * 
 * Unfortunately this will to apply to all your DOAP classes as it needs to apply to the root DOAP class.
 * 
   DataObjectAsPage::enable_versioning();
 * 
 */


