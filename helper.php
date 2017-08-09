<?php
/**
* @title			Steemit feed
* @copyright   		Copyright (C) 2011-2017 Minitek, All rights reserved.
* @license   		GNU General Public License version 3 or later.
* @author url		https://www.minitek.gr/
* @developers   	Minitek.gr / Yannis Maragos
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

class modSteemitFeedHelper 
{
	public static function displayFeed($params, $module) 
	{
		$app = JFactory::getApplication();
		
		// Check if we have an author
		if (!$params->get('feed_author', ''))
		{
			$html = '<div class="mn_steem_feed_error"><p>'.JText::_('MOD_STEEMITFEED_AUTHOR_NOT_FOUND').'</p></div>';
			
			return $html;
		}
		
		$module_id = $module->id;
		$steemitfeed_id = 'steemitfeed-'.$module_id;
		$steemitfeed_wrapper_id = 'steemitfeed-wrapper-'.$module_id;
		
		// Start html
		$html = '<div id="'.$steemitfeed_wrapper_id.'" class="steemitfeed-wrapper '.$params->get('moduleclass_sfx', '').'">';
				
			// Loader
			if ($params->get('feed_load_asynch', false))
			{
				$html .= '<div id="steemitfeed-loader-'.$module_id.'" class="steemit-feed-loader"></div>';
			}
								
			// Feed display
			$html .= modSteemitFeedHelper::feedCreateItemsWrapper($params, $module);
			
			// Pagination display
			if ($params->get('feed_additional_pages', 10) 
			&& ((int)$params->get('feed_additional_pages', 10) > 0
			|| $params->get('feed_additional_pages') === '-1'))
			{
				$style = '';
				if ($params->get('feed_load_asynch', false))
				{
					$style = 'style="display: none;"';	
				}
				$html .= '<div id="sf-pagination-'.$module_id.'" class="sf-pagination" '.$style.'>';
				$html .= '<a href="#" class="sf-btn" data-page="1">';
				$html .= '<span class="sf-page-text">'. JText::_('MOD_STEEMITFEED_LOAD_MORE') .'</span>';
				$html .= '<span class="sf-page-end">'. JText::_('MOD_STEEMITFEED_NO_MORE_POSTS')  .'</span>';
				$html .= '<span id="steemitfeed-page-loader-'.$module_id.'" class="steemit-feed-page-loader"></span>';
				$html .= '</a>';
				$html .= '</div>';
				
				$itemid = $app->getMenu()->getActive()->id;
				$sf_ajaxurl = JURI::root().'index.php?option=com_ajax&module=steemitfeed&format=raw&Itemid='.$itemid;

				$pagination_script = "
					<script type='text/javascript'>
					(function ($) {
						$(document).ready(function() 
						{
							// Options
							var feedId = ".$module_id.";
							var sf_ajaxurl = '".$sf_ajaxurl."';
							var modtitle = '".$module->title."';
							
							// Initialize spinner
							var createPaginationSpinner = function()
							{ 
								var spinner_options = {
								  lines: 8,
								  length: 3,
								  width: 3,
								  radius: 3,
								  corners: 1,
								  rotate: 0,
								  direction: 1,
								  color: '#333',
								  speed: 1,
								  trail: 52,
								  shadow: false,
								  hwaccel: false,
								  className: 'spinner',
								  zIndex: 2e9,
								  top: '50%',
								  left: '50%'
								};
								$('#steemitfeed-page-loader-'+feedId+'').append(new Spinner(spinner_options).spin().el);
								
								return;
							}
							
							// Pagination button click
							$('#sf-pagination-'+feedId+'').on('click', 'a.sf-btn', function(e) {
								e.preventDefault();
								
								if (!$(this).hasClass('sf-loading') && !$(this).hasClass('sf-disabled'))
								{
									$(this).addClass('sf-loading');
									
									// Get page number
									var page = $('#sf-pagination-'+feedId+' .sf-btn').attr('data-page');
									page = parseInt(page, 10);
									
									// Get last permlink
									var permlink = $('#".$steemitfeed_id." .sf-li:last').data('permlink');
								
									// Check if spinner has initialized
									if (!$('#steemitfeed-page-loader-'+feedId+' .spinner').length)
										createPaginationSpinner();
									
									// Show loader
									$('#sf-pagination-'+feedId+' .sf-page-text').hide();
									$('#steemitfeed-page-loader-'+feedId+'').css('display', 'inline-block');
								
									// Load feed										
									$.ajax({
										type : 'post',
										url : sf_ajaxurl,
										data : {
											method			: 'feedCreateItems', 
											feedid 			: ''+feedId+'',
											'ajax'			: '1',
											'permlink'		: ''+permlink+'',
											'page'			: ''+page+'',
											'modtitle'		: ''+modtitle+''
										}
									}).done(function (response) 
									{
										$('#sf-pagination-'+feedId+' .sf-page-text').css('display', 'inline-block');
										$('#steemitfeed-page-loader-'+feedId+'').hide();
										var next_page = page + 1;
										next_page = parseInt(next_page, 10);
										$('#sf-pagination-'+feedId+' .sf-btn').attr('data-page', next_page);
										$('#sf-pagination-'+feedId+' .sf-btn').removeClass('sf-loading');
										if (!$.trim(response))
										{
											$('#sf-pagination-'+feedId+' .sf-btn').addClass('sf-disabled');
											$('#sf-pagination-'+feedId+' .sf-page-text').hide();
											$('#sf-pagination-'+feedId+' .sf-page-end').css('display', 'inline-block');
										} 
										else
										{
											$('#".$steemitfeed_id."').append(response);
										}
										
									}).fail(function (jqXHR, exception) 
									{
										var msg = '';
										if (jqXHR.status === 0) {
											msg = 'No connection. Verify Network.';
										} else if (jqXHR.status == 404) {
											msg = 'Requested page not found. [404]';
										} else if (jqXHR.status == 500) {
											msg = 'Internal Server Error [500].';
										} else if (exception === 'parsererror') {
											msg = 'Requested JSON parse failed.';
										} else if (exception === 'timeout') {
											msg = 'Time out error.';
										} else if (exception === 'abort') {
											msg = 'Ajax request aborted.';
										} else {
											msg = 'Uncaught Error.' + jqXHR.responseText;
										}
										$('#sf-pagination-'+feedId+' .sf-page-text').css('display', 'inline-block');
										$('#steemitfeed-page-loader-'+feedId+'').hide();
										$('#sf-pagination-'+feedId+' .sf-btn').removeClass('sf-loading');
										$('#".$steemitfeed_id."').append(response);
									});
								}
							});
						});
						})(jQuery)
					</script>
				";	
				
				$html .= $pagination_script;
			}
		
		$html .= '</div>';
		
		return $html;
	}
	
	public static function feedCreateItemsWrapper($params, $module) 
	{
		$app = JFactory::getApplication();
		
		// Get feed data
		$module_id = $module->id;
		$feed_id = $module_id;
		$steemitfeed_id = 'steemitfeed-'.$feed_id;

		// Create items wrapper
		$html = '<div id="'.$steemitfeed_id.'" class="sf-list sf-list1">';
		
			// Load items
			if (!$params->get('feed_load_asynch', false))
			{
				// Synchronous display
				$html .= modSteemitFeedHelper::feedCreateItemsAjax($params, $module_id);
			}
			else
			{
				// Asynchronous display
				$itemid = $app->getMenu()->getActive()->id;
				$sf_ajaxurl = JURI::root().'index.php?option=com_ajax&module=steemitfeed&format=raw&Itemid='.$itemid;
	
				$asynch_script = "
				<script type='text/javascript'>
					(function ($) {
						$(document).ready(function() 
						{
							var sf_ajaxurl = '".$sf_ajaxurl."';
							var feedId = ".$feed_id.";
							var modtitle = '".$module->title."';
							
							$('#sf-pagination-'+feedId+' .sf-btn').addClass('sf-loading');

							// Initialize spinner
							var createSpinner = function()
							{ 
								var spinner_options = {
								  lines: 9,
								  length: 4,
								  width: 3,
								  radius: 3,
								  corners: 1,
								  rotate: 0,
								  direction: 1,
								  color: '#333',
								  speed: 1,
								  trail: 52,
								  shadow: false,
								  hwaccel: false,
								  className: 'spinner',
								  zIndex: 2e9,
								  top: '50%',
								  left: '50%'
								};
								$('#steemitfeed-loader-'+feedId+'').append(new Spinner(spinner_options).spin().el);
								
								return;
							}
							
							// Check if spinner has initialized
							if (!$('#steemitfeed-loader-'+feedId+' .spinner').length)
								createSpinner();
								
							// Show loader
							$('#steemitfeed-loader-'+feedId+'').show();
							
							// Load feed										
							$.ajax({
								type : 'post',
								url : sf_ajaxurl,
								data : {
									method			: 'feedCreateItems', 
									feedid 			: ''+feedId+'',
									'ajax'			: '1',
									'modtitle'		: ''+modtitle+''
								}
							}).done(function (response) 
							{
								$('#steemitfeed-loader-'+feedId+'').hide();
								$('#sf-pagination-'+feedId+'').show();
								$('#sf-pagination-'+feedId+' .sf-btn').removeClass('sf-loading');
								$('#".$steemitfeed_id."').html(response);
								
							}).fail(function (jqXHR, exception) 
							{
								var msg = '';
								if (jqXHR.status === 0) {
									msg = 'No connection. Verify Network.';
								} else if (jqXHR.status == 404) {
									msg = 'Requested page not found. [404]';
								} else if (jqXHR.status == 500) {
									msg = 'Internal Server Error [500].';
								} else if (exception === 'parsererror') {
									msg = 'Requested JSON parse failed.';
								} else if (exception === 'timeout') {
									msg = 'Time out error.';
								} else if (exception === 'abort') {
									msg = 'Ajax request aborted.';
								} else {
									msg = 'Uncaught Error.' + jqXHR.responseText;
								}
								$('#steemitfeed-loader-'+feedId+'').hide();
								$('#sf-pagination-'+feedId+' .sf-btn').removeClass('sf-loading');
								$('#".$steemitfeed_id."').html(response);
							});
							
						});
					})(jQuery)
				</script>
				";
				
				$html .= $asynch_script;
			}
		
		$html .= '</div>';
		
		return $html;
	}
	
	public static function feedCreateItemsAjax($params = false, $module_id = false) 
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;  
		
		// Get ajax variables
		$safe_ajax = $jinput->get('ajax', false, 'INT');
		if ($safe_ajax == '1')
		{
			$ajax = true;	
		}
		else
		{
			$ajax = false;	
		}

		if ($ajax)
		{
			$feed_id = $jinput->get('feedid', false, 'INT');
			$permlink = $jinput->getString('permlink', false);
			$page = $jinput->get('page', false, 'CMD');
			$module = JModuleHelper::getModule('mod_steemitfeed' , $jinput->getString('modtitle'));
			$params = new JRegistry($module->params);      
		}
		else
		{
			$feed_id = $module_id;
			$permlink = false;
			$page = false;
		}
	
		// Referral code
		$referral_code = $params->get('feed_referral', '') ? '?r='.$params->get('feed_referral') : '';
			
		// Images
		$feed_show_images = $params->get('feed_show_images', true);
		$feed_image_size = $params->get('feed_image_size', '0x0');
		$feed_fallback_image = $params->get('feed_fallback_image', '');	
		
		// Detail box options
		$detailBoxTitle = $params->get('feed_item_title', true);
		$feed_title_limit = $params->get('feed_title_limit', 20);
		$detailBoxIntrotext = $params->get('feed_item_introtext', true);
		$feed_introtext_limit = $params->get('feed_introtext_limit', 40);
		$detailBoxDate = $params->get('feed_item_date', true);
		$detailBoxCategory = $params->get('feed_item_category', true);
		$detailBoxTags = $params->get('feed_item_tags', true);
		$detailBoxAuthor = $params->get('feed_item_author', true);
		$detailBoxAuthorRep = $params->get('feed_item_author_rep', true);
		$detailBoxReward = $params->get('feed_item_reward', true);
		$detailBoxVotes = $params->get('feed_item_votes', true);
		$detailBoxComments = $params->get('feed_item_comments_count', true);

		// Query items
		$queryItems = modSteemitFeedHelper::feedQueryItems($params, $ajax, $permlink, $page);

		foreach ($queryItems as $key => $item) 
		{
			// Strip slashes		
			$item->title = trim(stripslashes( $item->title ));
			$item->body  = trim(stripslashes( $item->body ));
			
			// Trim title
			$item->short_title = modSteemitFeedHelper::trimWords($item->title, (int)$feed_title_limit);
			
			// Trim body
			$item->short_body = modSteemitFeedHelper::trimWords($item->body, (int)$feed_introtext_limit);
			
			// Format date
			$item->formatted_date = modSteemitFeedHelper::timeSince($item->created);
			
			// Author reputation
			$item->author_reputation = modSteemitFeedHelper::formatReputation($item->author_reputation);
			
			// Reward
			$total_payout_value = round((float)$item->total_payout_value, 2);
			$curator_payout_value = round((float)$item->curator_payout_value, 2);
			$pending_payout_value = round((float)$item->pending_payout_value, 2);
			$total_pending_payout_value = round((float)$item->total_pending_payout_value, 2);
			$item->total_reward = number_format(round(($total_payout_value + $curator_payout_value + $pending_payout_value + $total_pending_payout_value), 2), 2);
			
			// Votes
			$item->votes = $item->net_votes;
			
			// Replies count
			$item->replies_count = modSteemitFeedHelper::getRepliesCount($item->author, $item->permlink);
			
			// Metadata
			$metadata = json_decode($item->json_metadata, false);
		
			// Tags
			$item->tags = $metadata->tags;
			array_shift($item->tags);
			
			// Image
			if (isset($metadata->image))
			{
				$raw_image = $metadata->image;
				if (array_key_exists('0', $raw_image))
				{
					$item->image = 'https://steemitimages.com/'.$feed_image_size.'/'.$raw_image[0];
				}
			}
			else
			{
				if ($feed_fallback_image)
				{
					$item->image = $feed_fallback_image;
				}
			}
												
			$items[] = $item;
		}

		// Get feed from file
		ob_start();
			require JModuleHelper::getLayoutPath('mod_steemitfeed', $params->get('layout', 'default'));		
			$items = ob_get_clean();		
		
		if ($ajax) {
			if (count($queryItems))
			{
				echo $items;
			}
			jexit();
		} else {
			return $items;	
		}		
	}
	
	public static function feedQueryItems($params, $ajax, $permlink, $page) 
	{	
		// Get date 
		$datenow = JFactory::getDate()->toISO8601();
		$datenow = substr($datenow, 0, strpos($datenow, '+'));

		// Options - Data source
		$mn_sf_author = trim($params->get('feed_author', ''));
		$mn_sf_datenow = $datenow;
		$last_post_permlink = '';
										
		// Options - Pagination
		$feed_initial_items = (int)$params->get('feed_initial_items', 5);
		$feed_items_per_page = (int)$params->get('feed_items_per_page', 5);
		$feed_additional_pages = (int)$params->get('feed_additional_pages', 10);
			
		// Page load
		if (!$ajax)
		{ 
			$posts_per_page = $feed_initial_items;
		}
		// Ajax
		else
		{
			$posts_per_page = $feed_initial_items;	
			
			if ($permlink)
			{
				$posts_per_page = $feed_items_per_page;	
				$last_post_permlink = $permlink;
				
				// Check for allowed additional pages
				if ($page && $page > $feed_additional_pages)
				{
					return false;	
				}
			}
		}

		// Included tags
		$included_tags = array();
		if ($params->get('feed_tags_include', '') && $params->get('feed_tags_include', ''))
		{
			$included_tags = array_map( 'trim', explode( ',', $params->get('feed_tags_include', '') ) );
		}

		// Excluded tags
		$excluded_tags = array();
		if ($params->get('feed_tags_exclude', '') && $params->get('feed_tags_exclude', ''))
		{
			$excluded_tags = array_map( 'trim', explode( ',', $params->get('feed_tags_exclude', '') ) );
		}
		
		// Excluded posts
		$excluded_posts = array();
		if ($params->get('feed_posts_exclude', '') && $params->get('feed_posts_exclude', ''))
		{
			$excluded_posts = array_map( 'trim', explode( PHP_EOL, $params->get('feed_posts_exclude', '') ) );
		}

		// Define posts array and permlinks
		$posts = array();
		$previous_batch_permlink = '';
			
		// Starting posts count = 0
		$c = 0;
		while ($c < $posts_per_page)
		{
			// API call
			$raw_url = 'https://api.steemjs.com/get_discussions_by_author_before_date?author='.$mn_sf_author.'&startPermlink='.$last_post_permlink.'&beforeDate='.$mn_sf_datenow.'&limit='.$posts_per_page;
			$temp = file_get_contents($raw_url);
			$isjson = modSteemitFeedHelper::isJson($temp);

			if ($isjson)
			{
				$batch = json_decode($temp, false);

				// Track last permlink of this batch
				$batch_count = count($batch);
				$last_post = $batch[$batch_count - 1];
				$last_post_permlink = $last_post->permlink;
				
				// Break if last permlink of this batch is the same as last permlink of previous batch
				if ($last_post_permlink === $previous_batch_permlink) break;
				// Update previous batch
				$previous_batch_permlink = $last_post->permlink;
				
				foreach ($batch as $item)
				{
					// Exclude last permlink of previous page
					if ($permlink && $permlink === $item->permlink)
					{
						continue;
					}
					
					// Exclude posts
					if (in_array($item->permlink, $excluded_posts))
					{
						continue;
					}
					
					if (count($posts) >= $posts_per_page)
					{
						break;	
					}
				
					// Metadata
					$metadata = json_decode($item->json_metadata, false);
				
					// Add item to posts
					if (!in_array($item, $posts))
					{
						if (empty($included_tags) && empty($excluded_tags))
						{
							$posts[] = $item;
						}
						else
						{
							if (empty($included_tags) && !empty($excluded_tags))
							{
								if (empty(array_intersect($metadata->tags, $excluded_tags)))
								{
									$posts[] = $item;
								}
							}
							else if (!empty($included_tags) && empty($excluded_tags))
							{
								if (!empty(array_intersect($metadata->tags, $included_tags)))
								{
									$posts[] = $item;
								}
							}
							else if (!empty($included_tags) && !empty($excluded_tags))
							{
								if (!empty(array_intersect($metadata->tags, $included_tags)) && empty(array_intersect($metadata->tags, $excluded_tags)))
								{
									$posts[] = $item;
								}
							}
						}
					}
				}
			}
			
			// Update posts count
			$c = count($posts);
			
			// Change limit if limit == 1 and posts is empty
			if ($c === 0 && $posts_per_page === 1)
			{
				$posts_per_page = 2;
			}
		}

		return $posts;
	}
	
	public static function getRepliesCount($author, $permlink)
	{
		$replies = file_get_contents('https://api.steemjs.com/getContentReplies?parent='.$author.'&parentPermlink='.$permlink);
		$isjson = modSteemitFeedHelper::isJson($replies);
		
		if ($isjson)
		{
			$replies = json_decode($replies, false);
			$childrenCount = 0;
			
			// Get children replies
			foreach ($replies as $reply)
			{
				$childrenCount += $reply->children;
			}
			
			$repliesCount = count($replies) + $childrenCount;
			
			return $repliesCount;	
		}
		else
		{
			return false;
		}
	}
		
	public static function trimWords( $text, $num_words = 55, $more = null ) 
	{
		if ( null === $more ) 
		{
			$more = '&hellip;';
		}
	
		$original_text = $text;
		$text = strip_tags( $text );
	
		$words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
		// strip urls
		//$str = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $str);  
		$sep = ' ';
	
		if ( count( $words_array ) > $num_words ) 
		{
			array_pop( $words_array );
			$text = implode( $sep, $words_array );
			$text = $text . $more;
		} 
		else 
		{
			$text = implode( $sep, $words_array );
		}
		
		return $text;
	}
	
	public static function timeSince($date) 
	{
		$date = strtotime($date);
		$now = JFactory::getDate()->format("Y-m-d H:i:s");
		$now = strtotime($now);
		$since = $now - $date;
		
		$chunks = array(
			array(60 * 60 * 24 * 365 , JText::_('MOD_STEEMITFEED_YEAR'), JText::_('MOD_STEEMITFEED_YEARS')),
			array(60 * 60 * 24 * 30 , JText::_('MOD_STEEMITFEED_MONTH'), JText::_('MOD_STEEMITFEED_MONTHS')),
			array(60 * 60 * 24 * 7, JText::_('MOD_STEEMITFEED_WEEK'), JText::_('MOD_STEEMITFEED_WEEKS')),
			array(60 * 60 * 24 , JText::_('MOD_STEEMITFEED_DAY'), JText::_('MOD_STEEMITFEED_DAYS')),
			array(60 * 60 , JText::_('MOD_STEEMITFEED_HOUR'), JText::_('MOD_STEEMITFEED_HOURS')),
			array(60 , JText::_('MOD_STEEMITFEED_MINUTE'), JText::_('MOD_STEEMITFEED_MINUTES')),
			array(1 , JText::_('MOD_STEEMITFEED_SECOND'), JText::_('MOD_STEEMITFEED_SECONDS'))
		);
	
		for ($i = 0, $j = count($chunks); $i < $j; $i++) {
			$seconds = $chunks[$i][0];
			$name_1 = $chunks[$i][1];
			$name_n = $chunks[$i][2];
			if (($count = floor($since / $seconds)) != 0) {
				break;
			}
		}
	
		$print = ($count == 1) ? '1 '.$name_1 : "$count {$name_n}";
		return $print;
	}
	
	public static function formatReputation($reputation)
	{
		if ($reputation == null) return $reputation;
	
		$is_neg = $reputation < 0 ? true : false;
		$rep = $is_neg ? abs($reputation) : $reputation;
		$str = $rep;
		$leadingDigits = (int)substr($str, 0, 4);
		$log = log($leadingDigits) / log(10);
		$n = strlen((string)$str) - 1;
		$out = $n + ($log - (int)$log);
		if (!($out)) $out = 0;
		$out = max($out - 9, 0);
		$out = ($is_neg ? -1 : 1) * $out;
		$out = $out * 9 + 25;
		$out = (int)$out;
		
		return $out;
	}
	
	public static function isJson($string) 
	{
 		json_decode($string);
 		
		return (json_last_error() == JSON_ERROR_NONE);
	}
}
