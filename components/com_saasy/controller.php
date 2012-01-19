<?php
defined('_JEXEC') or die ('Restricted Access');

jimport ('joomla.application.component.controller');
jimport ('joomla.error.log');
jimport ('joomla.user.helper');

class SaasyController extends JController
{
	var $username = null;
	var $password = null;
	var $subscriptionURL = null;
	var $activatePrivateKey = null;
	var $deactivatePrivateKey = null;
	var $updatePrivateKey = null;
	var $nonpaymentPrivateKey = null;
	
	function _init()
	{
		$saasyConfig = &JComponentHelper::getParams( 'com_saasy' );
		$this->username = $saasyConfig->get('username');
		$this->password = $saasyConfig->get('password');
		$this->company = $saasyConfig->get('company');
		$this->product = $saasyConfig->get('product');
		$this->subscriptionURL = $saasyConfig->get('subscriptionURL');
		$this->activatePrivateKey = $saasyConfig->get('activatePrivateKey');
		$this->deactivatePrivateKey = $saasyConfig->get('deactivatePrivateKey');
		$this->updatePrivateKey = $saasyConfig->get('updatePrivateKey');
		$this->nonpaymentPrivateKey = $saasyConfig->get('nonpaymentPrivateKey');
		
		$this->contact = $saasyConfig->get('contact');
		$this->submit = $saasyConfig->get('submit');
		$this->myaccount = $saasyConfig->get('myaccount');
		$this->mylistings = $saasyConfig->get('mylistings');
	}

	function activate()
	{
		$privatekey = $this->activatePrivateKey;
		//$errorLog =& JLog::getInstance('error.log',null,JPATH_COMPONENT);

		$entry = array('status'=>'OK','comment'=>"Activating Subscription");
		//$errorLog->addEntry($entry);

		$entry = array('status'=>'OK','comment'=>print_r($_POST,true));
		//$errorLog->addEntry($entry);

		if($this->validateRequest($privatekey))
		{
			$entry = array('status'=>'OK','comment'=>"Key is valid");
			//$errorLog->addEntry($entry);

			$subscription_ref = $_POST["subscription_ref"];
			$customer_url = $_POST['customer_url'];

			if($subscription_ref == null) {
				header("HTTP/1.0 404 Not Found");
				return;
			} else {
				//Lookup saasy account link
				$db =& JFactory::getDBO();

				$query = "select userid from #__saasy where subscription_ref='{$subscription_ref}'";
				$db->setQuery($query);
				$accountLink = $db->loadObject();

				//if account link does not exist
				if(!$accountLink)
				{
					$userid = 0;

					//pull full subscription info from saasy
					$url = $this->subscriptionURL."/{$subscription_ref}?user={$this->username}&pass={$this->password}";

					$entry = array('status'=>'OK','comment'=>"Subscription URL");
					//$errorLog->addEntry($entry);

					$entry = array('status'=>'OK','comment'=>$url);
					//$errorLog->addEntry($entry);

					$ch = curl_init($url);
					//					curl_setopt($ch,CURLOPT_USERPWD,"{$this->username}:{$this->password}");
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch,CURLOPT_FRESH_CONNECT,1);

					$subscriptionXML = curl_exec($ch);

					if($subscriptionXML !== false)
					{
						$subscription = simplexml_load_string($subscriptionXML);

						$entry = array('status'=>'OK','comment'=>"Subscription Object");
						//$errorLog->addEntry($entry);
							
						$entry = array('status'=>'OK','comment'=>print_r($subscription,true));
						//$errorLog->addEntry($entry);

						//lookup user account
						$customerEmail = $subscription->customer->email;
						$customerEmail = addslashes(trim($customerEmail));
							
						$query = "select id,usertype from #__users where email='{$customerEmail}'";
						$db->setQuery($query);
						$user = $db->loadObject();
							
						//if user account exists
						if($user)
						{
							$entry = array('status'=>'OK','comment'=>"User Found: ".print_r($user));
							//$errorLog->addEntry($entry);

							$userid = $user->id;
							//update user account
							if($user->usertype == "Registered")
							{
								$query = "update user set usertype='Author' where id={$user->id}";
								$entry = array('status'=>'OK','comment'=>"Updating User: {$query}");
								//$errorLog->addEntry($entry);
								$db->setQuery($query);
								$db->query();
							}
							else
							{
								$entry = array('status'=>'OK','comment'=>"No Update Required");
								//$errorLog->addEntry($entry);
							}
						}
						else
						{
							$entry = array('status'=>'OK','comment'=>"Creating User Account");
							//$errorLog->addEntry($entry);

							//create user account
							$name = $subscription->customer->firstName." ".$subscription->customer->lastName;
							$name = addslashes(trim($name));

							$salt  = JUserHelper::genRandomPassword(32);
							$pass  = JUserHelper::genRandomPassword(8);
							$crypt = JUserHelper::getCryptedPassword($pass, $salt);
							$password = $crypt.':'.$salt;

							$query = "insert into #__users (name,username,email,password,usertype,registerDate)
								values ('{$name}','{$customerEmail}','{$customerEmail}','{$password}',
										'Author',NOW());";

							$entry = array('status'=>'OK','comment'=>"{$query}");
							//$errorLog->addEntry($entry);

							$db->setQuery($query);
							$db->query();

							$userid = $db->insertid();
						}//endif

						$entry = array('status'=>'OK','comment'=>"UserID: {$userid}");
						//$errorLog->addEntry($entry);
							
						//create saasy account link to user account
						if($userid != 0)
						{
							$query = "insert into #__saasy (userid,subscription_ref,customer_url) values
								({$userid},'{$subscription_ref}','{$customer_url}')";

							$entry = array('status'=>'OK','comment'=>"Creating Saasy Record: {$query}");
							//$errorLog->addEntry($entry);

							$db->setQuery($query);
							$db->query();
							
							//Update Listing to published 
							$query = "update #__content set state=1 where created_by={$userid} and sectionid=3";
							$db->setQuery($query);
			
							$entry = array('status'=>'OK','comment'=>"Listing Update Query: {$query}");
							//$errorLog->addEntry($entry);
			
							$db->query();
						}
						else
						{
							$entry = array('status'=>'OK','comment'=>"No User ID, no way to link to subscription");
							//$errorLog->addEntry($entry);

						}
					}
				}
				elseif($accountLink->userid != 0)
				{
					$query = "update #__users set usertype='Author' where id={$accountLink->userid}";

					$entry = array('status'=>'OK','comment'=>"Updating User To Author: {$query}");
					//$errorLog->addEntry($entry);

					$db->setQuery($query);
					$db->query();
					
					//Update Listing to published 
					$query = "update #__content set state=1 where created_by={$accountLink->userid} and sectionid=3";
					$db->setQuery($query);
	
					$entry = array('status'=>'OK','comment'=>"Listing Update Query: {$query}");
					//$errorLog->addEntry($entry);
	
					$db->query();
				}//endif
			}
		}
		else
		{
			$entry = array('status'=>'OK','comment'=>"Key is invalid");
			//$errorLog->addEntry($entry);
		}
	}

	function deactivate()
	{
		//$errorLog =& JLog::getInstance('error.log',null,JPATH_COMPONENT);
		$privatekey = $this->deactivatePrivateKey;

		if($this->validateRequest($privatekey))
		{

			$subscription_ref = $_POST["subscription_ref"];

			$entry = array('status'=>'OK','comment'=>"Working With {$subscription_ref}");
			//$errorLog->addEntry($entry);

			$db =& JFactory::getDBO();

			//Get user by subscription reference
			$query = "select userid from #__saasy where subscription_ref='{$subscription_ref}'";

			$entry = array('status'=>'OK','comment'=>"User Lookup Query: {$query}");
			//$errorLog->addEntry($entry);

			$db->setQuery($query);
			$result = $db->loadObject();

			$entry = array('status'=>'OK','comment'=>"User Lookup Result: ".print_r($result,true));
			//$errorLog->addEntry($entry);

			//If the user exists
			if($result)
			{
				//set the user to registered
				$query = "update #__users set usertype='Registered' where id={$result->userid}";
				$db->setQuery($query);

				$entry = array('status'=>'OK','comment'=>"User Update Query: {$query}");
				//$errorLog->addEntry($entry);

				$db->query();

				//Update Listing to unpublished and owned by admin
				$query = "update #__content set state=0, created_by=103 where created_by={$result->userid} and sectionid=3";
				$db->setQuery($query);

				$entry = array('status'=>'OK','comment'=>"Listing Update Query: {$query}");
				//$errorLog->addEntry($entry);

				$db->query();
			}
		}
		else
		{
			$entry = array('status'=>'OK','comment'=>"Invalid Deactivate Key");
			//$errorLog->addEntry($entry);
		}
	}

	function update()
	{
		//$errorLog =& JLog::getInstance('error.log',null,JPATH_COMPONENT);
		$privatekey = $this->updatePrivateKey;
		if($this->validateRequest($privatekey))
		{
			$entry = array('status'=>'OK','comment'=>"Valid Key, running update request");
			//$errorLog->addEntry($entry);

			$subscription_ref = $_POST["subscription_ref"];
			$subscription_status = $_POST['subscription_status'];

			$url = "https://api.fastspring.com/company/{$this->company}/subscription/{$subscription_ref}?user={$this->username}&pass={$this->password}";

			$entry = array('status'=>'OK','comment'=>"Subscription URL");
			//$errorLog->addEntry($entry);

			$entry = array('status'=>'OK','comment'=>$url);
			//$errorLog->addEntry($entry);

			$ch = curl_init($url);
			//curl_setopt($ch,CURLOPT_USERPWD,"{$this->username}:{$this->password}");
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_FRESH_CONNECT,1);

			$subscriptionXML = curl_exec($ch);

			if($subscriptionXML !== false)
			{
				$subscription = simplexml_load_string($subscriptionXML);
					
				$entry = array('status'=>'OK','comment'=>"Subscription Object ".print_r($subscription,true));
				//$errorLog->addEntry($entry);

				//If the subscription is active
				if("active" == $subscription_status)
				{
					//Get user by subscription reference
					$query = "select userid from #__saasy where subscription_ref='{$subscription_ref}'";
					$db->setQuery($query);
					$result = $db->loadObject();

					//If the user exists
					if($result)
					{
						//set the user to author
						$query = "update #__users set usertype='Author' where id={$result->userid}";
						$db->setQuery($query);
						$db->query();
					}
				}
			}
			else
			{
				$entry = array('status'=>'OK','comment'=>"Invalid Deactivate Key");
				//$errorLog->addEntry($entry);
			}
		}
	}

	function nonpayment()
	{
		$privatekey = $this->nonpaymentPrivateKey;
		if($this->validateRequest($privatekey))
		{
			$subscription_ref = $_POST["subscription_ref"];

			$db =& JFactory::getDBO();

			//Get user by subscription reference
			$query = "select userid from #__saasy where subscription_ref='{$subscription_ref}'";
			$db->setQuery($query);
			$result = $db->loadObject();

			//If the user exists
			if($result)
			{
				//set the user to registered
				$query = "update #__users set usertype='Registered' where id={$result->userid}";
				$db->setQuery($query);
			}
		}
	}

	function manageSubscription()
	{
		$user =& JFactory::getUser();

		if(!$user->guest)
		{
			$db =& JFactory::getDBO();
				
			$query = "select customer_url from #__saasy where userid={$user->id}";
			$db->setQuery($query);
			$subscription = $db->loadObject();
				
			if($subscription !== false)
			{
				if("" != trim($subscription->customer_url))
					$this->setRedirect($subscription->customer_url);
				else
					$this->setRedirect("index.php","Your subscription reference is invalid, please contact support.");
			}
			else
			{
				$this->setRedirect("index.php","We could not find your subscription, please contact support.");
			}
		}
		else
		{
			$this->setRedirect("index.php","Please login before attempting to access a subscription.");
		}
	}
	
	function activateuser()
	{
		global $mainframe;

		// Initialize some variables
		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$document   =& JFactory::getDocument();
		$pathway 	=& $mainframe->getPathWay();

		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$userActivation			= $usersConfig->get('useractivation');
		$allowUserRegistration	= $usersConfig->get('allowUserRegistration');

		// Check to see if they're logged in, because they don't need activating!
		if ($user->get('id')) {
			// They're already logged in, so redirect them to the home page
			$mainframe->redirect( 'index.php' );
		}

		if ($allowUserRegistration == '0' || $userActivation == '0') {
			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return;
		}

		// create the view
		require_once (JPATH_COMPONENT.DS.'views'.DS.'register'.DS.'view.html.php');
		$view = new UserViewRegister();

		$message = new stdClass();

		// Do we even have an activation string?
		$activation = JRequest::getVar('activation', '', '', 'alnum' );
		$activation = $db->getEscaped( $activation );

		if (empty( $activation ))
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
			$view->assign('message', $message);
			$view->display('message');
			return;
		}
		
		$query = 'SELECT id'
		. ' FROM #__users'
		. ' WHERE activation = '.$db->Quote($activation)
		. ' AND block = 1'
		. ' AND lastvisitDate = '.$db->Quote('0000-00-00 00:00:00');
		;
		$db->setQuery( $query );
		$userid = intval( $db->loadResult() );
		
		// Lets activate this user
		jimport('joomla.user.helper');
		if (JUserHelper::activateUser($activation))
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_COMPLETE_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_COMPLETE' );
			
			if($userid != 0)
			{
				$query = "select profile_id from #__community_users where userid={$userid}";
				$db->setQuery($query);
				$profileInfo = $db->loadObject();
					
				//If profile exists
				if($profileInfo !== false)
				{
/* HOOKED - no profile ID
					//If user is a contractor
					if($profileInfo->profile_id == 2) // Contractor Profile ID is 2
					{
*/
						//Forward user to Saasy
						$this->setRedirect("http://sites.fastspring.com/{$this->company}/product/{$this->product}");
/* 					}	 */
				}
			}						
		}
		else
		{
			// Page Title
			$document->setTitle( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ) );
			// Breadcrumb
			$pathway->addItem( JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' ));

			$message->title = JText::_( 'REG_ACTIVATE_NOT_FOUND_TITLE' );
			$message->text = JText::_( 'REG_ACTIVATE_NOT_FOUND' );
		}

		$view->assign('message', $message);
		$view->display('message');
		
	}
	
	function completeSignup()
	{
		$subscriptionRef = JRequest::getVar("subscription_ref","");
		if("" != trim($subscriptionRef))
		{
			$db =& JFactory::getDBO();
			
			$query = "select userid from #__saasy where subscription_ref='{$subscriptionRef}'";
			$db->setQuery($query);
			$userid = $db->loadResult();
			
			if($userid != 0)
			{
				$listingID = 0;
				if(array_key_exists("createdListingID",$_COOKIE))
					$listingID = $_COOKIE['createdListingID'];
				elseif(array_key_exists("claimedListingID",$_COOKIE))
					$listingID = $_COOKIE['claimedListingID'];

				setcookie("createdListingID",0,time()-(24*60*60),"/");
				setcookie("claimedListingID",0,time()-(24*60*60),"/");
				
				if($listingID != 0 && is_numeric($listingID))
				{
					$query = "select created_by from #__content where id={$listingID}";
					$db->setQuery($query);
					$currentOwner = $db->loadResult();
					
					//Only permit claiming if admin is the owner.
					if($currentOwner == 103)
					{
						$query = "update #__content set created_by={$userid} where id={$listingID}";
						$db->setQuery($query);
						$db->query();
					}
					
					$this->setRedirect("/index.php?option=com_content&view=article&id={$listingID}");
				}
				else
				{
				//	print("Listing ID Cookie not set");
				}
			}
			else
			{
			//	print("Not Logged In");
			}
		}
		else
		{
		//	print("No Subscription Ref Set");
		}
	}
	
	function details()
	{		
		require_once (JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'view.html.php');
		$view = new SaasyViewDetails();
		
		$createListing = JRequest::getVar("createdListing",0);
		$claimListing = JRequest::getVar("claimListing",0);
		
		$user =& JFactory::getUser();		
		$db =& JFactory::getDBO();
		
		$contentTitle = "";
		
		if(!$user->guest)
		{
			$query = "select count(*) as count from #__saasy where userid={$user->id}";
			$db->setQuery($query);
			$saasyCount = $db->loadResult();
			
			if($saasyCount > 0) // They have registered with saasy
			{
				$query = "select usertype from #__users where id={$user->id}";
				$db->setQuery($query);
				$userType = $db->loadResult();
				
				if($userType/* == "Author"*/) // They have an active subscription
					$contentTitle = "Saasy Verified Contractor";
				else
					$contentTitle = "Saasy Verified Contractor w/ Suspended Account";
			}
			else
			{
			// HOOKED - no profile types..
/*
				$query = "select profile_id from #__community_users where userid={$user->id}";
				$db->setQuery($query);
				$profileID = $db->loadResult();
				
				if($profileID == 2)
*/
					$contentTitle = "Non-Saasy Verified Contractor";
/*
				else
					$contentTitle = "Non-Contractor Account";
				
*/
			}
		}
		else
		{
			if($createListing == 1)
			{
				$contentTitle = "Just Created Listing As Guest";
			}
			elseif($claimListing != 0)
			{
				setcookie("claimedListingID",$claimListing,time()+(24*60*60),"/");
				$contentTitle = "Just Claimed Listing As Guest";
			}
			else
			{
				$contentTitle = "Not Logged In";
			}			
		}
		
		if("" == trim($contentTitle))
			$contentTitle = "Not Logged In";
						
		$query = "select content from #__saasy_content where title='{$contentTitle}'";
		$db->setQuery($query);
		$content = stripslashes($db->loadResult());
		
		$replace = array('company', 'product', 'contact', 'submit', 'myaccount', 'mylistings');
		foreach ($replace as $param) {
			$content = str_replace('{{'.$param.'}}', $this->$param, $content);
		}
				
		$view->accountPageContent = $content;
		$view->display(null);
	}

	function validateRequest($privatekey)
	{
		if (md5($_REQUEST['security_data'] . $privatekey) != $_REQUEST['security_hash']){
			header("HTTP/1.0 404 Not Found");
			return false;
		}

		return true;
	}
}
