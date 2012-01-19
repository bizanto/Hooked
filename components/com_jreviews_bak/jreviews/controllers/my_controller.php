<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class MyController extends S2Controller 
{
    var $tmpl_list = null; 
    
    function beforeFilter() 
    {    
		# Init Access
		if(isset($this->Access))
        {
            $this->Access->init($this->Config);            
        }
                      
		# Dynamic Community integration loading
		$community_extension = Configure::read('Community.extension');
		$community_extension = $community_extension != '' ? $community_extension : 'community_builder';
		
        App::import('Model',$community_extension,'jreviews');
		$this->Community = new CommunityModel();
		
		# Set Theme	
		$this->viewTheme = $this->Config->template;
		$this->viewImages = S2Paths::get('jreviews', 'S2_THEMES_URL') . 'default' . _DS . 'theme_images' . _DS;		
			
		# Set template type for lists and template suffix
		$this->__initTemplating();
                
		# Set pagination vars
		// First check url, then menu parameter. Otherwise the limit list in pagination doesn't respond b/c menu params always wins
        $this->limit = Sanitize::getInt($this->params,'limit',Sanitize::getInt($this->data,'limit_special',Sanitize::getInt($this->data,'limit')));
//		$this->passedArgs['limit'] = $this->limit;

		$this->page = Sanitize::getInt($this->data,'page',Sanitize::getInt($this->params,'page',1));
		
        if(!$this->limit) 
        {
	 		if(Sanitize::getVar($this->params,'action')=='myreviews') {
				$this->limit = Sanitize::getInt($this->params,'limit',$this->Config->user_limit);						
			} else {
				$this->limit = Sanitize::getInt($this->params,'limit',$this->Config->list_limit);			
			}
		} 
        // Set a hard code limit to prevent abuse
        $this->limit = max(min($this->limit, 50),1);

		// Need to normalize the limit var for modules
		if(isset($this->params['module'])) {
			$module_limit = Sanitize::getInt($this->params['module'],'module_limit',5);
		} else {
			$module_limit = 5;
		}

		$this->module_limit = Sanitize::getInt($this->data,'module_limit',$module_limit);
		$this->module_page = Sanitize::getInt($this->data,'module_page',1);
		$this->module_page = $this->module_page === 0 ? 1 : $this->module_page;
		$this->module_offset = (int)($this->module_page-1) * $this->module_limit;	
		if($this->module_offset < 0) $this->module_offset = 0;
		
		$this->page = $this->page === 0 ? 1 : $this->page;
		
		$this->offset = (int)($this->page-1) * $this->limit;
		
		if($this->offset < 0) $this->offset = 0;
	
		# Add global javascript variables
		if(!defined('MVC_GLOBAL_JS_VARS') && !$this->ajaxRequest && $this->action != '_save') // action conditional is for new listing submission, otherwise the form hangs 
		{
            cmsFramework::addScript('<script type="text/javascript">
            //<![CDATA[
            var xajaxUri = "'.getXajaxUri().'";
            //]]>
            </script>');
            cmsFramework::addScript('<script type="text/javascript">
            //<![CDATA[
            var s2AjaxUri = "'.getAjaxUri().'";
            //]]>
            </script>');
            cmsFramework::addScript('<script type="text/javascript">
                var jr_translate= new Array();
                jr_translate["cancel"] = "'.__t("Cancel",true).'";
                jr_translate["submit"] = "'.__t("Submit",true).'";
            </script>');    

			$javascriptcode = '<script type="text/javascript">%s</script>';
			
			# Set calendar image
			cmsFramework::addScript(sprintf($javascriptcode,'var datePickerImage = "'.$this->viewImages.'calendar.gif";'));

            # Find and set one public Itemid to use for Ajax requests
            $menu_id = '';
            if(!defined('MVC_FRAMEWORK_ADMIN')){
                App::import('Model','menu','jreviews');
                $MenuModel = RegisterClass::getInstance('MenuModel');    
                $menu_id = $MenuModel->get('jreviews_public');
                $menu_id = $menu_id != '' ? $menu_id : 99999;                
                $this->set('public_menu_id',$menu_id);
            }  
        			
            # Set JReviews public menu
            cmsFramework::addScript(sprintf($javascriptcode,'var jr_publicMenu = '.$menu_id.';'));

			define('MVC_GLOBAL_JS_VARS',1);			
		}

        # Init plugin system
        $this->_initPlugins();
    }             

    function afterFilter() 
    {        
        if(!$this->ajaxRequest && class_exists('AssetsHelper'))
        {   
            $Assets = RegisterClass::getInstance('AssetsHelper'); 
            // Need to override name and action because using $this->requestAction in theme files replaces the original values (i.e. related listings prevents detail page js/css from loading)
            $Assets->name = $this->name;  
            $Assets->action = $this->action;            
            $Assets->_user = & $this->_user;  
            
            if(!isset($this->Access)) // View cache
            {
                App::import('Component','access');
                $Access = new AccessComponent();
                $Access->gid = $this->_user->id;
                $Assets->Access = &$Access;
            }
                               
            if(!empty($this->assets))
            {
                $Assets->assets = $this->assets;
            }
            
            $Assets->load(); 
        }
    }        
    
/**********************************************************
*  Plugin callbacks
**********************************************************/
    /**
    * Plugin system initialization
    * 
    * @param object $model - include for lazy loading of plugin callbacks for a particular model. This may be required when trying to trigger a callback in a model outside it's main controller
    */
    function _initPlugins($model = null)
    {               
        // Load plugins
        $App = &App::getInstance();
        $registry = &$App->jreviewsPaths;
        $plugins = array_keys($registry['Plugin']);
        if(!empty($plugins))
        {
            unset($App,$registry);
            $plugins = str_replace('.php','',$plugins);
            App::import('Plugin',$plugins);
            $this->__initComponents($plugins);
            foreach($plugins AS $plugin)
            {              
                $component_name = Inflector::camelize($plugin);
                
                if(isset($this->{$component_name}) && $this->{$component_name}->published)
                {                          
                    // Register all the plugin callbacks in the controller
                    $plugin_methods = get_class_methods($this->{$component_name});
                    
                    foreach($plugin_methods AS $callback)
                    {                                
                        if(substr($callback,0,3)=='plg')
                        {                            
                            if(method_exists($this,'getPluginModel')) 
                            {                            
                                if(is_null($model))
                                    {
                                        $this->{$component_name}->plgModel = & $this->getPluginModel();                                    
                                    }   
                                else 
                                    {        
                                        $this->{$component_name}->plgModel = & $this->{$model};                                                                            
                                    }                   
                               
                                $plgModel = & $this->{$component_name}->plgModel;

                                if(!isset($this->{$component_name}->validObserverModels)
                                    ||
                                        (
                                            isset($this->{$component_name}->validObserverModels)
                                            && !empty($this->{$component_name}->validObserverModels)
                                            && in_array($plgModel->name,$this->{$component_name}->validObserverModels)
                                        ) 
                                    )
                                {                                         
                                    $plgModel->addObserver($callback,$this->{$component_name});
                                }
                            }                            
                        }
                    }                    
                    if(method_exists($this->{$component_name},'plgBeforeRender'))
                    {                
                        $this->plgBeforeRender[] = $component_name;
                    }                 
                }
            }
        }        
    }

    function locateThemeFile($action,$file,$ext='.thtml')
    {
        $action = strtolower($action);
        $App = &App::getInstance($this->app);
        $location = 'Theme';
        if(isset($App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$this->viewSuffix.$ext]))
        {
            return $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$this->viewSuffix.$ext];
        } elseif(isset($App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$ext]))
        {
            return $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$ext];
        } elseif(isset($App->{$this->app.'Paths'}[$location]['default'][$action][$file.$this->viewSuffix.$ext])) {
            return $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$this->viewSuffix.$ext];
        } else {
            return $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$ext];
        }      
    }
      
    function __initTemplating() 
    {
        $tmpl_list = null;
        $tmpl_suffix = '';
     
        // If tmpl_list is set we use that, otherwise we check the menu parameters 
        if(null!=Sanitize::getString($this->data,'tmpl_list'))
        {
            $this->data['tmpl_list'] = Sanitize::getString($this->data,'tmpl_list');
        } 
        elseif(null!=Sanitize::getString($this->data,'listview'))
        {                
            $this->data['tmpl_list'] = Sanitize::getString($this->data,'listview');
        }                                                                                         
        elseif(null!=Sanitize::getString($this->params,'tmpl_list'))
        {                            
            $this->data['tmpl_list'] = Sanitize::getString($this->params,'tmpl_list');
        } 
        else 
        {
            $this->data['tmpl_list'] = null;
        }                                                                                         

        if(null!=$this->data['tmpl_list']) 
        {
            $this->tmpl_list = $tmpl_list = $this->__listTypeConversion($this->data['tmpl_list']);
        }

		if(Sanitize::getVar($this->params,'module')) {
			$this->params['tmpl_suffix'] = Sanitize::getString($this->params['module'],'tmpl_suffix');
		}

		if($suffix = Sanitize::getString($this->data,'tmpl_suffix',Sanitize::getString($this->params,'tmpl_suffix'))) {

			$tmpl_suffix = $suffix;
		
		} 
        
        if(isset($this->params['module'])){
            
            $task = 'module';
            
        } elseif($this->name == 'categories') {

			$task = $this->action;
		
		} else {

			$task = $this->name;
		
		}

		switch($task) 
        {
			case 'com_content':		
			case 'category':		
                    $cat_id = Sanitize::getInt($this->params,'cat');
                    if(!$cat_id) break;
					$dbSettings = $this->Category->getTemplateSettings($cat_id);
					$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);
				break;
				
			case 'section':
					$dbSettings = $this->Section->getTemplateSettings(Sanitize::getInt($this->params,'section'));
					$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);				
				break;
				
			case 'mylistings':
				
                $this->viewSuffix = $tmpl_suffix;
                
                $Configure = &App::getInstance(); // Get file map

                if(isset($Configure->jreviewsPaths['Theme'][$this->Config->template]['listings']['listings_mylistings.thtml']) ||
                    isset($Configure->jreviewsPaths['Theme']['default']['listings']['listings_mylistings.thtml'])                    
                ){
					$this->tmpl_list = 'mylistings';
				
				} else {

					$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);				
				
				}
				
				break;
				
			case 'favorites':

                $this->viewSuffix = $tmpl_suffix;
					
                $Configure = &App::getInstance(); // Get file map
                    							
                if(isset($Configure->jreviewsPaths['Theme'][$this->Config->template]['listings']['listings_favorites.thtml']) ||
                    isset($Configure->jreviewsPaths['Theme']['default']['listings']['listings_favorites.thtml'])                    
                ){

					$this->tmpl_list = 'favorites';
				
				} else {

					$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);				
				
				}
				
				break;
							
			case 'alphaindex':				

				$menu_params = $this->Menu->get('menuParams'.Sanitize::getInt($this->params,'Itemid'));
				
				$this->tmpl_suffix = Sanitize::getVar($menu_params,'tmpl_suffix');
				
				$tmpl_list = $this->__listTypeConversion(Sanitize::getVar($menu_params,'listview'));
			
				if($tmpl_list != '' ) {
					$this->tmpl_list = $tmpl_list;
				} else {
					$this->tmpl_list = 	$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);
				}
				
			break;		
				
			case 'directories':
			case 'featured':
			case 'toprated':
			case 'topratedauthor':
			case 'latest':
			case 'popular':
			case 'mostreviews':					

					$this->viewSuffix = $tmpl_suffix;

					if($tmpl_list) {
						
						$this->tmpl_list = $tmpl_list;
					
					} else {

						$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);
					}	

				break;	
				
            case 'list':
			case 'search':

				$this->tmpl_list = $this->__listTypeConversion($this->Config->search_display_type);
                $this->viewSuffix = Sanitize::getString($this->params,'tmpl_suffix',$this->Config->search_tmpl_suffix);
				
                break;
				
			case 'listings':

				switch($this->action) {
                    
                    case 'detail':

                        $listing_id = Sanitize::getInt($this->params,'id');
                        if($listing_id)
                        {
                            $dbSettings = $this->Listing->getTemplateSettings($listing_id);
                            $this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);    
                        }
                        break;
					
					case '_loadForm':
						$dbSettings = $this->Category->getTemplateSettings(Sanitize::getInt($this->data['Listing'],'catid'));
						$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);						
						break;
						
					case 'edit':		
						$dbSettings = $this->Listing->getTemplateSettings((int)$this->params['id']);
						$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);
						break;	
						
					case 'create':
						$this->viewSuffix = $tmpl_suffix;
						break;	
					
				}
				
				break;
			
			case 'reviews':
				
				switch($this->action) {
					
					case '_save':

						$dbSettings = $this->Listing->getTemplateSettings((int)$this->data['Review']['pid']);

						$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);				

						break;
						
					case 'edit':

						$review_id = Sanitize::getInt($this->params,'id');

						$dbSettings = $this->Review->getTemplateSettings($review_id);

						$this->__setTemplateVars($dbSettings, $tmpl_list, $tmpl_suffix);				
					
						break;
                        
                    case 'latest': 
                    case 'myreviews' :                     
                        $this->viewSuffix = $tmpl_suffix;
                    break;    

                }

				break;
				
			default:
					$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);
					$this->viewSuffix = $tmpl_suffix;
				break;		
					
		}   
	}
	
	function __listTypeConversion($type) 
    {
	    if(!is_null($this->tmpl_list))
        {
            return $this->tmpl_list;
        }	
		switch($type) {
			case null:
				return null;
				break;  
			case 0:
				return 'tableview';
				break;
			case 1:
				return 'blogview';
				break;
			case 2:
				return 'thumbview';
				break;
			default:
				return null;
				break;	
		}		
		
	}
	
	function __setTemplateVars($dbSettings, $tmpl_list = null, $tmpl_suffix = null) 
    {
		# Set template type
		if(!empty($tmpl_list)) {

			$this->tmpl_list = $tmpl_list;
		
		} else {
			
			if(!empty($dbSettings['Category']['tmpl_list'])) {
				
				$this->tmpl_list = $dbSettings['Category']['tmpl_list'];
			
			} elseif(!empty($dbSettings['Section']['tmpl_list'])) {
				
				$this->tmpl_list = $dbSettings['Section']['tmpl_list'];				
				
			} else {
				
				$this->tmpl_list = $this->__listTypeConversion($this->Config->list_display_type);
			}
				
		}
		
		# Set template suffix
		if(!empty($tmpl_suffix)) {

			$this->viewSuffix = $tmpl_suffix;
		
		} else {
			
			if(!empty($dbSettings['Category']['tmpl_suffix'])) {
				
				$this->viewSuffix = $dbSettings['Category']['tmpl_suffix'];
			
			} elseif(!empty($dbSettings['Section']['tmpl_suffix'])) {
				
				$this->viewSuffix = $dbSettings['Section']['tmpl_suffix'];				
				
			}

		}		
	}
    
}
