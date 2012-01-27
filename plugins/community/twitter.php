<?php
/**
 * @category	Plugins
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .DS.'components' .DS.'com_community' .DS.'libraries' .DS.'core.php');

if(!class_exists('plgCommunityTwitter'))
{
	class plgCommunityTwitter extends CApplications
	{
		var $name 		= "Twitter";
		var $_name		= 'twitter';
		var $_path		= '';
		var $timelines  = array(
		                        'public'    => 'https://api.twitter.com/1/statuses/public_timeline.json',
		                        'friends'   => 'https://api.twitter.com/1/statuses/friends_timeline.json',
		                        'home'      => 'https://api.twitter.com/1/statuses/home_timeline.json',
		                        'user'      => 'https://api.twitter.com/1/statuses/user_timeline.json',
		                        'update'    => 'https://api.twitter.com/1/statuses/update.json'
						);
		var $users       = array(
		                        'show'      => 'https://api.twitter.com/1/users/show.json'
						);

		public function getConsumer()
		{
			static $consumer    = null;

			if( is_null( $consumer ) )
			{
			    $my             = CFactory::getUser();

                $consumer = new Zend_Oauth_Consumer(plgCommunityTwitter::getConfiguration() );
			}

			return $consumer;
		}

		public function getConfiguration()
		{
		    static $configuration = null;
			
			$params = new JParameter('');
		    if( is_null( $configuration ) )
		    {
				$plugin 	=& JPluginHelper::getPlugin('community','twitter');
				$params 	= new JParameter( $plugin->params );
		        $my 		= CFactory::getUser();
                $callback   = JURI::root() . 'index.php?option=com_community&view=oauth&task=callback&app=twitter';
				$configuration = array(
				    'version' => '1.0',
				    'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
				    'signatureMethod' => 'HMAC-SHA1',
				    'callbackUrl' => $callback,
				    'requestTokenUrl' => 'https://api.twitter.com/oauth/request_token',
				    'authorizeUrl' => 'https://api.twitter.com/oauth/authorize',
				    'accessTokenUrl' => 'https://api.twitter.com/oauth/access_token',
				    'consumerKey' => $params->get('consumerKey' , '0rSLnHLm1cpX1sTsqkQaQ'),
				    'consumerSecret' => $params->get('consumerSecret' ,'nsCObKFeJFP9YYGOZoHAHAWfvjZIZ4Hv7M8Y1w1flQ')
				);
			}
			return $configuration;
		}

		function onProfileDisplay()
		{
			JPlugin::loadLanguage( 'plg_twitter', JPATH_ADMINISTRATOR );
			$user		= CFactory::getRequestUser();
			
			$document	=& JFactory::getDocument();
			$css		= JURI::base() . 'plugins/community/twitter/style.css';
			$document->addStyleSheet($css);
			$my         = CFactory::getUser();
			$oauth      =& JTable::getInstance( 'Oauth' , 'CTable' );
			if( !$oauth->load( $user->id , 'twitter' ) )
			{
			    return JText::_('PLG_TWITTER NOT SET');
			}
			
			return $this->_getTwitterHTML( $user->id );
		}

		function _getTwitterHTML( $userId )
		{
		    $this->loadUserParams();
		    $my     			= CFactory::getUser( $userId );
		    $this->userparams	= $my->getAppParams( $this->_name );
		    
			$showFriends	= $this->userparams->get( 'showFriends' , false );
			$oauth    		=& JTable::getInstance( 'Oauth' , 'CTable' );
			$loaded			= $oauth->load( $my->id , 'twitter');
			ob_start();
			if( $loaded && !is_null($oauth->accesstoken) && !empty($oauth->accesstoken))
			{
			    $count          = $this->userparams->get( 'count' , 5 );
			    $accessToken    = unserialize( $oauth->accesstoken );
				$client         = $accessToken->getHttpClient( plgCommunityTwitter::getConfiguration() );
			    $timeline       = $showFriends ? 'home' : 'user';
			    
			    $client->setUri( $this->timelines[ $timeline ] );
                $client->setMethod(Zend_Http_Client::GET);
                $client->setParameterGet( 'count', $count );
                if( $showFriends )
                {
                    $client->setParameterGet( 'id', $accessToken->getParam( 'screen_name' ) );
				}
                $response       = $client->request();
				$data           = Zend_Json::decode( $response->getBody() , Zend_Json::TYPE_OBJECT );
				
			    $client->setUri( $this->users['show'] );
                $client->setMethod(Zend_Http_Client::GET);
                $client->setParameterGet( 'screen_name', $accessToken->getParam('screen_name') );
                $response       = $client->request();
               	$userinfo		= Zend_Json::decode( $response->getBody() , Zend_Json::TYPE_OBJECT );
				
				if( !$userinfo )
				{
			?>
				<div><?php echo JText::_('PLG_TWITTER UNABLE TO CONTACT SERVER');?></div>
			<?php
				}
				else
				{
			?>
				<div id="application-twitter">
            		<ul>
           				<li>
							<!-- start twitter avatar-->
							<div class="twitter-user-avatar">
								<a href="http://twitter.com/<?php echo $userinfo->screen_name; ?>" target="blank"><img class="avatar" src="<?php echo $userinfo->profile_image_url; ?>" alt="<?php echo $userinfo->screen_name; ?>"/></a>
							</div>
							<!--end twitter avatar-->
							<!--start twitter post-->
							<div class="twitter-user-detail">
	                            <a href="http://twitter.com/<?php echo $userinfo->screen_name; ?>" target="blank"><strong><?php echo $userinfo->name; ?></strong></a>
	                            <br />
								<i class="small">
	                                <?php echo $userinfo->statuses_count; ?> tweets, <?php echo $userinfo->followers_count; ?> followers
	                            </i>
			  				    <div class="small"><?php echo $userinfo->description; ?></div>

			  				</div>
			  				<!--end twitter post-->
		  				</li>
	            		<?php
						if( is_object($data))
						{
							if(isset($data->error))
							{
								echo $data->error;
							}
						}
						else
						{
							CFactory::load( 'helpers' , 'linkgenerator' );
							
			  				for($i = 0; $i< count($data); $i++)
							{
								$tweet  =& $data[ $i ];
								$date   = JFactory::getDate( $tweet->created_at );
								
								$text	= CLinkGeneratorHelper::replaceURL( $tweet->text , true , true );
								$text	= $this->replaceAliasURL( $text );
			  				?>
								<li>
									<?php if ( ($i == 0 && $showFriends) || $showFriends) { ?>
									<!--twitter avatar-->
									<div class="twitter-avatar">
									<a href="http://twitter.com/<?php echo $tweet->user->screen_name; ?>" target="blank">
										<img width="23" height="23" class="avatar" src="<?php echo $tweet->user->profile_image_url; ?>" alt="<?php echo $tweet->user->screen_name; ?>"/>
									</a>
									</div>
									<!--twitter avatar-->
									<?php } ?>
									<!--twitter post-->
									<div class="twitter-post">
					  				    <?php echo $text; ?>
					  				</div>
				  					<div class="clr"></div>
				  					<!--twitter avatar-->
				  					<div class="small"><?php echo $date->toFormat(JText::_('DATE_FORMAT_LC2')); ?></div>
				  				</li>
		                    <?php
							}
						}
				?>
	            </ul>
	            </div>
	            <div class="clr"></div>
            <?php
            	}
			}
			else
			{
			?>
	            <div class="icon-nopost">
	                <img src="<?php echo JURI::base()?>components/com_community/assets/error.gif" alt="" />
	            </div>
	            <div class="content-nopost">
	                <?php echo JText::_('PLG_TWITTER NOT UPDATES');?>
	            </div>
            <?php
			}
			$html   = ob_get_contents();
			ob_end_clean();
			
			return $html;
		}

		public function replaceAliasURL( $message )
		{
			$pattern	= '/@(("(.*)")|([A-Z0-9][A-Z0-9_-]+)([A-Z0-9][A-Z0-9_-]+))/i';
			
			preg_match_all( $pattern , $message , $matches );
		
			if( isset($matches[0]) && !empty($matches[0]) )
			{
				CFactory::load( 'helpers' , 'user' );
				CFactory::load( 'helpers' , 'linkgenerator' );
				
				$usernames	= $matches[ 0 ];
		
				for( $i = 0 ; $i < count( $usernames ); $i++ )
				{
					$username	= $usernames[ $i ];
					$username	= JString::str_ireplace( '"' , '' , $username );
					$username	= explode( '@' , $username );
					$username	= $username[1];
					
					$message	= JString::str_ireplace( $username , '<a href="http://twitter.com/' . $username . '" target="_blank" rel="nofollow">' . $username . '</a>', $message );
				}
			}
			
			return $message;
		}
	
		function onProfileStatusUpdate( $userid, $old_status, $new_status)
		{
			$oauth    =& JTable::getInstance( 'Oauth' , 'CTable' );
			$user				= CFactory::getUser( $userid );
			$this->userparams	= $user->getAppParams( $this->_name );

			if( $oauth->load( $userid , 'twitter' ) && $this->userparams->get("updateTwitter") )
			{
				$accessToken    = unserialize( $oauth->accesstoken );
				$client         = $accessToken->getHttpClient( plgCommunityTwitter::getConfiguration() );
				$client->setUri( $this->timelines['update'] );
				$client->setMethod(Zend_Http_Client::POST);
				$client->setParameterPost('status', $new_status);

				$response       = $client->request();
				$data           = Zend_Json::decode( $response->getBody());
			}
		}
	}
}

