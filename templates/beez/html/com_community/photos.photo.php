<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

if( $photos )
{
	
	$hooked_albums = array(54,108,96);
	
	if (in_array($album->id,$hooked_albums))
		$allowTag = 0;

	require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

	$pids = array();
	foreach($photos as $photo) {
		$pids[] = $photo->id;
	}
	
	$db =& JFactory::getDBO();
	$sql = "SELECT * FROM #__relate_photos rp ".
	       "LEFT JOIN #__content c ON c.id = rp.listing_id ".
	       "WHERE rp.photo_id IN (".implode(',', $pids).") ".
	       "AND rp.listing_id != 0 AND c.state > 0 AND c.catid IN (13,14,15) ";
	$db->setQuery($sql);
	$results = $db->loadObjectList();
	
	$related_reports = array();
	foreach ($results as $listing) {
		if (!isset($related_reports[$listing->photo_id])) {
			$related_reports[$listing->photo_id] = array();
		}
		
		$link = ContentHelperRoute::getArticleRoute($listing->listing_id, $listing->catid, $listing->sectionid);
		$related_reports[$listing->photo_id][] = JText::sprintf('CC PHOTO RELATED REPORT', $link, $listing->title); 
	}
?>
<div class="page-actions clrfix"></div>
<div id="cGallery">
	<script type="text/javascript">
		joms.gallery.bindKeys();
		var jsPlaylist = {
			album: <?php echo $album->id;?>,
			photos:	[
					<?php
					if($photos)
					{
						CFactory::load('libraries', 'storage');
						CFactory::load('helpers', 'image');

						
						for($i=0; $i < count($photos); $i++ ) 
						{
							$photo	=& $photos[$i];
							$storage = CStorage::getStorage( $photo->storage );
							$imgpath = str_replace('/', DS, $photo->original);

					?>
						{id: <?php echo $photo->id; ?>,
						 loaded: false,
						 caption: '<?php echo addslashes( $photo->caption );?>',
						 thumbnail: '<?php echo $photo->getThumbURI(); ?>',
						 hits: '<?php echo $photo->hits; ?>',
						 url: '<?php  echo $photo->getImageURI(); ?>',
						 originalUrl: '<?php  echo $photo->getOriginalURI(); ?>',
						 tags: [
						 	<?php foreach($photo->tagged as $tagItem){ ?>
						 	{
							 	id:     <?php echo $tagItem->id;?>,
							 	photoId: <?php echo $photo->id; ?>,
							 	userId: <?php echo $tagItem->userid;?>,
							 	displayName: '<?php echo $tagItem->user->getDisplayName(); ?>',
							 	profileUrl: '<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$tagItem->userid, false);?>',
							 	top: <?php echo $tagItem->posx;?>,
							 	left: <?php echo $tagItem->posy;?>,
							 	width: <?php echo $tagItem->width;?>,
							 	height: <?php echo $tagItem->height;?>,
							 	displayTop: null,
							 	displayLeft: null,
							 	displayWidth: null,
							 	displayHeight: null,
							 	canRemove: <?php echo $tagItem->canRemoveTag;?>
							}
							<?php $end = end($photo->tagged); if($end->id != $tagItem->id) echo ',';?>
						 	<?php } ?>
						 ],
						 related: '<?php if ($related_reports[$photo->id]) echo implode("<br/>", $related_reports[$photo->id]); ?>'
						}<?php  ?>
					<?php
							$end	= end( $photos );
							if ($end->id!=$photo->id)
								echo ',';
						}
					}
					?>
					],
			currentPlaylistIndex: null,
			language: {
				CC_REMOVE: '<?php echo addslashes(JText::_('CC REMOVE'));?>',
				CC_NO_PHOTO_CAPTION_YET: '<?php echo addslashes(JText::_('CC NO PHOTO CAPTION YET'));?>',
				CC_SET_PHOTO_AS_DEFAULT_DIALOG: '<?php echo addslashes(JText::_('CC SET PHOTO AS DEFAULT DIALOG'));?>',
				CC_REMOVE_PHOTO_DIALOG: '<?php echo addslashes(JText::_('CC REMOVE PHOTO DIALOG'));?>',
				CC_SELECT_FRIEND: '<?php echo addslashes(JText::_('CC SELECT PERSON')); ?>',
				CC_PHOTO_TAG_NO_FRIEND: '<?php echo addslashes(JText::_('CC PHOTO TAG NO FRIEND')); ?>',
				CC_PHOTO_TAG_ALL_TAGGED: '<?php echo addslashes(JText::_('CC PHOTO TAG ALL TAGGED')); ?>',
				CC_CONFIRM: '<?php echo addslashes(JText::_('CC CONFIRM')); ?>',
				CC_PLEASE_SELECT_A_FRIEND: '<?php echo addslashes(JText::_('CC PLEASE SELECT A FRIEND')); ?>'
			},
			config: {
				defaultTagWidth: <?php echo $config->get('tagboxwidth');?>,
				defaultTagHeight: <?php echo $config->get('tagboxheight');?>
			}
		};			
	</script>

	<?php if ($default) { ?>
	<div class="photoCaption">
		<h3 class="photoCaptionText">
			<?php echo $default->caption;?>    
	    </h3>
    <?php if( $isOwner || $isAdmin ) { ?>
    	<div class="photoCaptionAction _edit">
    		<a title="<?php echo JText::_('CC EDIT');?>" href="javascript:void(0);" class="edit-this" onclick="joms.gallery.editPhotoCaption()"><?php echo JText::_('CC EDIT');?></a>
    	</div>
    <?php } ?>
	
	<?php if( $isOwner || $isAdmin ) { ?>
		<input class="photoCaptionInput" type="text" value="" size="48" enabled="enabled"/>
	<?php } ?>

		<span class="photoCaptionActions">
	<?php if( $isOwner || $isAdmin ) { ?>
			<button class="photoCaptionAction _save" href="javascript:void(0);" onclick="joms.gallery.savePhotoCaption()"><?php echo JText::_('CC SAVE');?></button>
			<button class="photoCaptionAction _cancel" href="javascript:void(0);" onclick="joms.gallery.cancelPhotoCaption()"><?php echo JText::_('CC CANCEL');?></button>
	<?php } ?>
		</span>
	</div>
	
	<div class="clr"></div>
	
  	<div class="photoViewport">
		<div class="photoDisplay">
			<img class="photoImage"/>
		</div>
	
		<div class="photoActions">
			<div class="photoAction _next" onclick="joms.gallery.displayPhoto(joms.gallery.nextPhoto());joms.jQuery('#relatedReport').html(jsPlaylist.photos[jsPlaylist.currentPlaylistIndex].related);"><img src="" height="50" alt="" /></div>
			<div class="photoAction _prev" onclick="joms.gallery.displayPhoto(joms.gallery.prevPhoto());joms.jQuery('#relatedReport').html(jsPlaylist.photos[jsPlaylist.currentPlaylistIndex].related);"><img src="" height="50" alt="" /></div>
		</div>
	
		<div class="photoTags">
			<div class="photoTagActions">
				<button class="photoTagAction _select" onclick="joms.gallery.selectNewPhotoTagFriend();"><?php echo JText::_('CC SELECT PERSON');?></button>
				<button class="photoTagAction _cancel" onclick="joms.gallery.cancelNewPhotoTag(); cWindowHide();"><?php echo JText::_('CC CANCEL');?></button>
			</div>
		</div>

		<div class="photoLoad"></div> 
		
    	<div class="vidSubmenu clrfix">
	    
    		<ul class="submenu jsApSbMn">

			<?php if( ($isOwner || $isAdmin) && ($photo->storage == 'file') ) { ?>
				<li><a title="<?php echo JText::_('CC PHOTO ROTATE LEFT'); ?>" href="javascript:void(0);"  class="jsApIcn jsPhRotL photoRotaterActions" onclick="joms.gallery.rotatePhoto('left')">-<?php echo JText::_('CC PHOTO ROTATE LEFT'); ?></a></li>
				<li><a title="<?php echo JText::_('CC PHOTO ROTATE RIGHT'); ?>" href="javascript:void(0);" class="jsApIcn jsPhRotR photoRotaterActions" onclick="joms.gallery.rotatePhoto('right')">-<?php echo JText::_('CC PHOTO ROTATE RIGHT'); ?></a></li>
			<?php } ?>
			</ul>
			<div id="like-container"></div>
    	</div>
    	  
	</div>
	
	<?php }	?>	
	
	<div class="uploadedBy" id="uploadedBy">
		<?php echo JText::sprintf('CC UPLOADED BY', CRoute::_('index.php?option=com_community&view=profile&userid='.$photoCreator->id), $photoCreator->getDisplayName()); ?>
	</div>
	
	<div class="clr"></div>
	
	<div class="uploadedBy" id="relatedReport">
		<?php if ($related_reports[$photos[0]->id]) echo implode("<br/>\n", $related_reports[$photos[0]->id]); ?>
	</div>

	<div class="clr"></div>
	
	<div class="photoDescription">
		<div class="photoSummary"></div>
		<?php if ($allowTag) : ?>
        <div class="photoTextTags"><?php echo JText::_('CC IN THIS PHOTO'); ?> </div>
        <?php endif; ?>
	</div>
	
	<?php if( isset($allowTag) && ($allowTag)) { ?>	
	<div class="photoTagging">
		<a id="startTagMode" href="javascript: void(0);" onclick="joms.gallery.startTagMode();"><?php echo JText::_('CC TAG THIS PHOTO'); ?></a>
		
		<div class="photoTagSelectFriend">
			<dl id="system-message" class="js-system-message" style="display:none;">
				<dt class="notice"><?php echo JText::_('CC NOTICE');?></dt>
				<dd class="notice message fade">
					<ul>
						<li><?php echo JText::_('CC PLEASE SELECT A FRIEND'); ?></li>
					</ul>
				</dd>
			</dl>
		
			<label for="photoTagFriendFilter"><?php echo JText::_('CC PHOTO TAG TYPE FRIEND'); ?></label>		
			<div class="photoTagFriendFilters">	
				<input type="text" name="photoTagFriendFilter" class="photoTagFriendFilter" onkeyup="joms.gallery.filterPhotoTagFriend();"/>
			</div>
			
			<label><?php echo JText::_('CC PHOTO TAG CHOOSE FRIEND'); ?></label>
			<div class="photoTagFriends">
				<?php foreach($friends as $friend) { ?>
				<label id="photoTagFriend-<?php echo $friend->id;?>" class="photoTagFriend">
					<input name="photoTagFriendsId" type="radio" value="<?php echo $friend->id;?>"/>
					<span><?php echo $friend->getDisplayName();?></span>
				</label>
				<?php } ?>
			</div>
		</div>
		
		<div class="photoTagFriendsActions">
			<button class="photoTagFriendsAction _select">[<?php echo JText::_('CC SELECT PERSON');?>]</button>
			<button class="photoTagFriendsAction _cancel">[<?php echo JText::_('CC CANCEL');?>]</button>
		</div>

		<div class="photoTagInstructions">
			<?php echo JText::_('CC PHOTO TAG INSTRUCTIONS'); ?>
			<button class="photoTagInstructionsAction" onclick="joms.gallery.stopTagMode();"><?php echo JText::_('CC DONE TAGGING'); ?></button>
		</div>
	</div>
	<?php } ?>	
	
</div>




<?php
	if($photos || $default)
	{
?>
<script type="text/javascript" language="javascript">
if( typeof wallRemove !=='function' )
{
	function wallRemove( id )
	{
		if(confirm('<?php echo JText::_('CC CONFIRM REMOVE WALL'); ?>'))
		{
			joms.jQuery('#wall_'+id).fadeOut('normal').remove();
			jax.call('community','photos,ajaxRemoveWall', id );
		}
	}
}

</script>
<?php
if( $showWall )
{
?>
<!-- Load walls for this photo -->
<div id="community-photo-walls-title"><?php echo JText::_('CC COMMENTS');?></div>
<?php
}
?>
<div id="community-photo-walls"></div>
<div id="wallContent"></div>

<script type="text/javascript" language="javascript">
joms.jQuery(document).ready(function(){ 
	joms.gallery.init(); 
	joms.jQuery('#relatedReport').html(jsPlaylist.photos[jsPlaylist.currentPlaylistIndex].related);
});
</script>

<?php
	}
}
else
{
?>
	<div id="no-photos"><?php echo JText::_('CC NO PHOTOS AVAILABLE FOR PREVIEW');?></div>
<?php
}
?>