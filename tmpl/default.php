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

foreach($items as $key => $item) 
{ ?>
	<article class="sf-li" data-permlink="<?php echo $item->permlink; ?>">
		
		<?php // Image
		if ($feed_show_images && isset($item->image) && $item->image)
		{ ?>
			<a href="https://steemit.com<?php echo $item->url.''.$referral_code; ?>" class="sf-image" target="_blank">
				<img src="<?php echo $item->image; ?>" alt="<?php echo $item->title; ?>" />
			</a>
		<?php } ?>
		
		<div class="sf-li-content">
		
			<?php // Title
			if ($detailBoxTitle)
			{ ?>
				<a class="sf-li-title" href="https://steemit.com<?php echo $item->url.''.$referral_code; ?>" target="_blank"><?php echo $item->short_title; ?></a>
			<?php }
			
			// Body
			if ($detailBoxIntrotext)
			{ ?>
				<div class="sf-li-body"><?php echo $item->short_body; ?></div>
			<?php }
			
			// Tags
			if ($detailBoxTags)
			{ ?>
				<div class="sf-li-tags">
					<?php foreach ($item->tags as $tag)
					{ ?>
						<a href="https://steemit.com/trending/<?php echo $tag.''.$referral_code; ?>" class="sf-li-tag" target="_blank">&#35;<?php echo $tag; ?></a>
					<?php } ?>
				</div>
			<?php }
				
			// Post footer
			if ($detailBoxDate
			|| $detailBoxCategory
			|| $detailBoxAuthor
			|| $detailBoxAuthorRep
			|| $detailBoxReward
			|| $detailBoxVotes
			|| $detailBoxComments)
			{ ?>
				
				<div class="sf-li-footer">
					
					<?php // Reward
					if ($detailBoxReward)
					{ ?>
						<span class="sf-li-reward">
							<span class="sf-li-dollar-sign">&#36;</span><?php echo $item->total_reward; ?>
						</span>
					<?php }
					
					// Votes
					if ($detailBoxVotes)
					{ ?>
						<span class="sf-li-votes">
							<i class="fa fa-chevron-up"></i>&nbsp;
							<?php echo $item->net_votes; ?>
						</span>
					<?php }
					
					// Replies
					if ($detailBoxComments)
					{ ?>
						<span class="sf-li-replies">
							<a href="https://steemit.com<?php echo $item->url.''.$referral_code; ?>#comments" target="_blank">
								<i class="fa fa-comments"></i>&nbsp;
								<span><?php echo $item->replies_count; ?></span>
							</a>
						</span>
					<?php }
					
					// Date - author - category
					if ($detailBoxDate
					|| $detailBoxAuthor
					|| $detailBoxCategory)
					{ ?>
						<span class="sf-li-vcard">
						
							<?php // Date
							if ($detailBoxDate)
							{ ?>
								<span class="sf-li-date"><?php echo $item->formatted_date; ?></span>
							<?php }
							
							// Author
							if ($detailBoxAuthor)
							{ ?>
								<span class="sf-li-author">
									<?php echo JText::_('MOD_STEEMITFEED_BY'); ?> <a href="https://steemit.com/@<?php echo $item->author.''.$referral_code; ?>" target="_blank"><?php echo $item->author; ?></a>
									<?php if ($detailBoxAuthorRep)
									{ ?>
										<span class="sf-li-rep"><?php echo $item->author_reputation; ?></span>
									<?php } ?>
								</span>
							<?php }
							
							// Category
							if ($detailBoxCategory)
							{ ?>
								<span class="sf-li-category">
									<?php echo JText::_('MOD_STEEMITFEED_IN'); ?> <a href="https://steemit.com/trending/<?php echo $item->category.''.$referral_code; ?>" target="_blank"><?php echo $item->category; ?></a>
								</span>
							<?php } ?>
						
						</span>
					<?php } ?>
					
				</div>
			<?php } ?>
		
		</div>
	
	</article>
<?php }

