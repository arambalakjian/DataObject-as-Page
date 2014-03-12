# DataObjectAsPage Module #

## Maintainers

 * Aram Balakjian
  <aram at carboncrayon dot com>

## Branch Requirements

 * 3.1 -> SilverStripe 3.1.x
 * 3.0 -> SilverStripe 3.0.x
 * 2.4 -> SilverStripe 2.4.x

## Overview ##

The module provides functionality for displaying DataObjects managed via ModelAdmin to appear as though they were 
full Pages on the front end of the site. It includes the option to enable versioning, allowing Draft and Published versions of the DataObject.

Searching has been removed for now but will hopefully be added back in future as a standalone module. We would recommend either Lucene or Solr modules for DataObject searchability.

For full instructions on use see: http://www.ssbits.com/tutorials/2012/dataobject-as-pages-the-module/

##Versioning

Versioning is now optional, you can enable it by adding the following line to you _config.php

DataObjectAsPage::enable_versioning();

## Installation

Unpack and copy the module folder into your SilverStripe project.

Create 3 new Classes; The Item class, the Admin Class and the Listing Page class.

* The Item class must extend DataObjectAsPage
- Inside the Item Class you must define: static $listing_page_class = '[YourListingPageClass]';

* The Admin class must extend DataObjectAsPageAdmin
- Inside the Admin Class, you need to define the standard ModelAdmin attributes

* The Listing Page class must extend DataObjectAsPageHolder and DataObjectAsPageHolder_Controller
- Inside the ListingPage Controller Class you must define: static $item_class = '[YourItemClass]';


Run "dev/build" in your browser, for example: "http://localhost/silverstripe/dev/build?flush=all"

For full installation and extention options visit http://www.ssbits.com/tutorials/2012/dataobject-as-pages-the-module/
