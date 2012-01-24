# DataObjectAsPage Module #

## Overview ##

The module provides functionality for displaying DataObjects managed via ModelAdmin to appear as though they were 
full Pages on the front end of the site. It includes versioning, allowing Draft and Published versions of the DataObject
as well as a modified search engine to include the DataObjects in the site search.

For full instructions on use see: http://www.ssbits.com/tutorials/2012/dataobject-as-pages-the-module/

## Maintainer Contact

 * Aram Balakjian 
   <aram (at) ssbits (dot) com>

## Requirements

 * SilverStripe 2.4.1 or newer

## Installation

Unpack and copy the mobile folder into your SilverStripe project.

Create 3 new Classes; The Item class, the Admin Class and the Listing Page class.

*The Item class must extend DataObjectAsPage

*The Admin class must extend DataObjectAsPageAdmin

*The Listing Page class must extend DataObjectAsPageHolder and DataObjectAsPage_Controller

Inside the Item Class you must define: static $listing_page_class = '[YourListingPageClass]';

Inside the Admin Class, you need to define the standard ModelAdmin attributes

Inside the ListingPage Controller Class you must define: static $item_class = '[YourItemClass]';

Run "dev/build" in your browser, for example: "http://localhost/silverstripe/dev/build?flush=all"

For full installation and extention options visit http://www.ssbits.com/tutorials/2012/dataobject-as-pages-the-module/