<?php
$related_photos = getRelatedThumb($listing['Listing']['listing_id']);
if (count($related_photos)) {
	$document =& JFactory::getDocument();
	$document->_metaTags['property']['og:image'] = JURI::base().$related_photos[0]->thumbnail; // **depends on core hax**
	$desc = trim(strip_tags($listing['Listing']['text']));
	if (strlen($desc)) {
		$document->setDescription($desc);
	}
}
?>

<div class="fl">
<div class="article-likes">

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) {return;}
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<div class="fb-like" data-href="<?php echo (JURI::getInstance()->toString()); ?>" data-send="true" data-layout="button_count" data-width="100" data-show-faces="false"></div>


    </div>
    <div class="article-tweets">
        <div class="width50 fl">
	    <a href="http://twitter.com/share" class="twitter-share-button" data-count="none" data-via="hookedno">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
	    </div>
	    <div class="width50 fr">
        <a href="#" class="mailfriend">&nbsp;</a>
        </div>
    </div>
    {loadposition tell-a-friend}
    </div>