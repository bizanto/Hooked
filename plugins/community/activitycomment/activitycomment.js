/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * ActivityComment is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */

function activitySubscribe( actId )
{
	jax.call('community','plugins,activitycomment,subscribe',actId );
	jQuery('#activity-unsubscribe-'+actId).css('display','inline');
	jQuery('#activity-subscribe-'+actId).css('display','none');
}

function activityUnsubscribe(actId)
{
	jax.call('community','plugins,activitycomment,unsubscribe',actId );
	jQuery('#activity-unsubscribe-'+actId).css('display','none');
	jQuery('#activity-subscribe-'+actId).css('display','inline');
}

function nextPhoto( total )
{
	var current	= Number( joms.jQuery('#photo-share-current').val() );
	
	if( current >= total )
		return;
	
	
	var next 	= current + 1;

	joms.jQuery( '#photo-share-' + current ).css('display' , 'none' );
	joms.jQuery( '#photo-share-' + next ).css('display' , 'block' );

	joms.jQuery('#photo-share-current').val( next );
	joms.jQuery( '#photo-share-source' ).val( joms.jQuery('#photo-share-' + next ).attr('src') );
	joms.jQuery( '#image-count').html( next );
}

function prevPhoto()
{
	var current	= Number( joms.jQuery('#photo-share-current').val() );
	
	if( current == 1 )
		return;
	
	var prev	= current - 1;
	
	joms.jQuery( '#photo-share-' + current ).css('display' , 'none' );
	joms.jQuery( '#photo-share-' + prev ).css('display' , 'block' );

	joms.jQuery('#photo-share-current').val( prev );
	joms.jQuery( '#photo-share-source' ).val( joms.jQuery('#photo-share-' + prev ).attr('src') );
	joms.jQuery( '#image-count').html( prev );	
}

function activityAddNote( view )
{
	var msg	= jQuery('#share-note').val();
	jax.call('community' , 'plugins,activitycomment,addNote' , msg , view );
}

function activityAddPhoto( view )
{
	var url	= jQuery('#share-photo').val();
	var msg = jQuery('#share-photo-title').val();
	jax.call('community' , 'plugins,activitycomment,addPhoto' , url , msg , view);
}

function activityAddUrl( view )
{
	var url	= jQuery('#share-url').val();
	var noImg = 0;
	var source = jQuery('#photo-share-source').val();
	
	if( joms.jQuery('#no-image:checked').val() == 'on' )
		noImg = 1;

	jax.call('community' ,'plugins,activitycomment,addUrl' , url , view ,  noImg , source );
}

function activityshowmeta()
{
	jQuery('#share-url-meta').show();
}

function activityshowpostbutton()
{
	jQuery('#share-url-button').show();
}

function activityhidepost()
{
	jQuery('#share-url-button').hide();
}
function showwait()
{
	var url	= jQuery('#share-url').val();
	
	if(url != '' )
	{
		jax.call('community' ,'plugins,activitycomment,waiting' );
	}
}

function activitygetmeta()
{
	var url	= jQuery('#share-url').val();
	
	jax.call('community' ,'plugins,activitycomment,getMeta' , url );
}

function activityshareshow( type )
{
	if( type == 'message' )
	{
		jQuery('#activity-share-note').show();
		jQuery('#activity-share-photos').hide();
		jQuery('#activity-share-videos').hide();
		jQuery('#activity-share-links').hide();
		
		jQuery('#share-action-note').addClass('share-selected');
		jQuery('#share-action-photos').removeClass('share-selected');
		jQuery('#share-action-videos').removeClass('share-selected');
		jQuery('#share-action-links').removeClass('share-selected');
	}
	else if( type == 'links')
	{
		jQuery('#activity-share-links').show();
		jQuery('#activity-share-note').hide();
		jQuery('#activity-share-photos').hide();
		jQuery('#activity-share-videos').hide();

		jQuery('#share-action-links').addClass('share-selected');
		jQuery('#share-action-note').removeClass('share-selected');
		jQuery('#share-action-photos').removeClass('share-selected');
		jQuery('#share-action-videos').removeClass('share-selected');

	}
	else if( type == 'photos' )
	{
		jQuery('#activity-share-note').hide();
		jQuery('#activity-share-photos').show();
		jQuery('#activity-share-videos').hide();
		jQuery('#activity-share-links').hide();
		
		jQuery('#share-action-note').removeClass('share-selected');
		jQuery('#share-action-photos').addClass('share-selected');
		jQuery('#share-action-videos').removeClass('share-selected');
		jQuery('#share-action-links').removeClass('share-selected');

	}
	else if( type == 'videos')
	{
		jQuery('#activity-share-note').hide();
		jQuery('#activity-share-photos').hide();
		jQuery('#activity-share-videos').show();
		jQuery('#activity-share-links').hide();

		jQuery('#share-action-note').removeClass('share-selected');
		jQuery('#share-action-photos').removeClass('share-selected');
		jQuery('#share-action-videos').addClass('share-selected');
		jQuery('#share-action-links').removeClass('share-selected');

	}
}

function activityShowComment(id)
{
	jQuery('#activity-' +id+'-comment').show();
}
function activityHideComment(id)
{
	jQuery('#activity-' +id+'-comment').hide();
	jQuery('#activity-' +id+'-comment-errors').html('');
}

function activityInsertComment(id , val, type)
{
	if( type == 'asc')
	{
	jQuery('#comment-holder-'+ id).append(val);
	jQuery('#commentval-'+id).val('');
	}
	else
	{
	jQuery('#comment-holder-'+ id).prepend(val);
	jQuery('#commentval-'+id).val('');
	}
}

function activityMoreComments(id)
{
	jax.call('community','plugins,activitycomment,morecomments' , id );
}

function activityMoreCommentsReplace(id , content )
{
	jQuery('#activity-comment-more-'+ id).after(content);
	jQuery('#activity-comment-more-' + id ).hide();
}

function activityRemovecomment( id)
{
	jQuery('#activity-comment-item-' +id).fadeOut(500);
}
function activityLikeItem(id,userid)
{
	jQuery('#likes-holder-' + id ).children().remove();
	jax.call('community','plugins,activitycomment,likeitem',id,userid);
	jQuery('#activity-unlike-'+id).css('display','inline');
	jQuery('#activity-like-'+id).css('display','none');
}

function activityInsertLike(id,val)
{
	jQuery('#likes-holder-'+id).prepend(val);
}
function activityUnlikeItem(id,userid)
{
	jQuery('#likes-holder-' + id ).children().remove();
	jax.call('community','plugins,activitycomment,unlikeitem',id,userid);
	jQuery('#activity-unlike-'+id).css('display','none');
	jQuery('#activity-like-'+id).css('display','inline');
}

function activityRepost(actId ,view )
{
	jax.call('community','plugins,activitycomment,repost' , actId );
}