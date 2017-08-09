<?php
/**
* @title			Steemit feed
* @copyright   		Copyright (C) 2011-2016 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url		https://www.minitek.gr/
* @developers   	Minitek.gr / Yannis Maragos
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the helper.
require_once __DIR__ . '/helper.php';

// Get params
$module_id = $module->id;

// Instantiate global document object
$document = JFactory::getDocument();

// Add Stylesheet
$document->addStyleSheet(JURI::base(true).'/modules/mod_steemitfeed/assets/css/style.css');

// Font Awesome
if ($params->get('load_fontawesome', 1)) 
{
	$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css');
}

// Add responsive css / scrollbar
$steemitfeed = '#steemitfeed-wrapper-'.$module_id;
$responsive_breakpoint = (int)$params->get('feed_breakpoint', 400);
$title_font_size = (int)$params->get('feed_title_font_size', 18);
$body_font_size = (int)$params->get('feed_body_font_size', 15);
$tags_font_size = (int)$params->get('feed_tags_font_size', 14);
$enable_scrollbar = $params->get('feed_scrollbar', false);
$max_height = (int)$params->get('feed-maxheight', 400);
$css = '';

if ($enable_scrollbar)
{
	$css .= '
	'.$steemitfeed.'.steemitfeed-wrapper {
		max-height: '.$max_height.'px;
		border-top: 1px solid #e5e5e5;
		border-bottom: 1px solid #e5e5e5;
	}
	'.$steemitfeed.' .sf-li {
		margin-right: 10px;	
		margin-left: 10px;	
	}
	'.$steemitfeed.' .sf-list1 .sf-li:last-of-type {
		border-bottom: 0;
	}
	'.$steemitfeed.' .sf-pagination {
		margin-right: 10px;	
		margin-left: 10px;	
		border-top: 1px solid #eee;
	}
	';
}

$css .= '
'.$steemitfeed.' .sf-li-title {
	font-size: '.$title_font_size.'px;
}
'.$steemitfeed.' .sf-li-tags {
	font-size: '.$tags_font_size.'px;
}
'.$steemitfeed.' .sf-li-body {
	font-size: '.$body_font_size.'px;
}
';

$css .= '@media only screen and (max-width:'.$responsive_breakpoint.'px) 
{
	'.$steemitfeed.' .sf-list1 .sf-image {
		float: none;
		display: block;
		width: 100%;
		margin: 0 0 12px;
		max-height: inherit;
	}
}';	
		
$document->addStyleDeclaration( $css );

// Add jQuery
if ($params->get('load_jquery', 1)) 
{
  	JHtml::_('jquery.framework');
}

// Add spin.js
$document->addCustomTag('<script src="'.JURI::base().'modules/mod_steemitfeed/assets/js/spin.min.js" type="text/javascript"></script>');

// Display feed
echo modSteemitFeedHelper::displayFeed($params, $module);

