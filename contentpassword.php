<?php
/**
* @version		$Id$ 2011/08/16 contentpassword.php
* @package		Contentpassword
* @copyright	ProgAndy
* @license		GNU General Public License 2 or later
*/
 
// No direct access allowed to this file
defined( '_JEXEC' ) or die( 'Restricted access' );
 
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR); 
 
// Import Joomla! Plugin library file
jimport('joomla.plugin.plugin');
 
//The Content plugin Loadmodule
class plgContentContentPassword extends JPlugin
{
	private $session;
	private $method;
	private static $uniqid = 0;
	
	function __construct(& $subject, $config = array()) {
		parent::__construct($subject, $config);
		$this->session = JFactory::getSession();
		$this->loadLanguage();
		$this->tmpl = new plgViewContentContentPassword(dirname(__FILE__), $this->_type, $this->_name);
		$this->method = ($this->params->get('allowget')) ? 'REQUEST' : 'POST';
	}
	
	/**
	*	Prevent caching of volatile contentpassword content.
	*/
	public function onCacheSaveData($id, $group, &$data) {
		if (strpos($data, '<!--CPW_VOLATILE-->')) return false;
		return true;
	}
	
	/**
	*	Legacy function.
	*/
	public function onPrepareContent(&$article, &$params, $page = 0) {
		$this->onContentPrepare('text', $article, $params, $page);
	}
	
	/**
	* Plugin that protects content with a password
	*/
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		if (is_object($article)) {
			//$idslug = 'article.'.JRequest::getString('option') . $article->id;
			//if (!empty($article->alias)) $idslug .= $article->alias;
			if (!empty($article->fulltext) && strpos($article->fulltext, '{password') !== FALSE) {
				$this->_process($article->fulltext);
			}
			if (!empty($article->introtext) && strpos($article->introtext, '{password') !== FALSE) {
				$this->_process($article->introtext);
			}
			if (!empty($article->text) && strpos($article->text, '{password') !== FALSE) {
				$this->_process($article->text);
			}
		} else if (is_string($article) && strpos($article, '{password') !== FALSE) {
			$this->_process($article);
		}
	
	}
	
	private function _process(&$text) {
		if (!$this->params->get('_enabled', 1)) {
			$text = preg_replace('#\\{/?password(area)?(\\s[^\\}]+)?\\}#', '', $text);
			return 0;
		}
		$password = trim(JRequest::getString('contentpassword_password', '', $this->method, 2));
		if (empty($password)) $password = null;
		$access = true;
		$description = null;
		$title = null;
		$storeId = "article.".md5($text);
		if (preg_match_all('#\\{password\\s([^\\}]+)\\}#', $text, $arguments)) {
			$access = $this->session->get($storeId, FALSE, __CLASS__);;
			if (!empty($arguments)) {
				$allowgroups = array();
				foreach ($arguments[1] as $argument) {
					$params = null;
					if (preg_match_all('#(\\w+)\\s*=\\s*(["\'])(.*?)\\2#', htmlspecialchars_decode($argument, ENT_QUOTES), $params, PREG_SET_ORDER)) {
						foreach ($params as $param) {
							$value = trim($param[3]);
							switch ($param[1]) {
								case 'title':
									$title = $value;
									break;
								case 'desc':
									$description = $value;
									break;
								case 'sql':
									if (!$access) $access = $this->_executeSQL($value, $password);
									break;
								case 'pass':
									if (!$access && !empty($password) && $password == $value) $access = true;
									break;
								case 'group':
									if ($this->_evaluateGroup($value, $password, true)) $access = true;
									break;
								case 'passgroup':
									if (!$access) $access = $this->_evaluateGroup($value, $password);
									break;
								case 'allowgroup':
									if (!empty($value)) $allowgroups[] = $value;
									break;
								case 'allowgroup':
									if (!empty($value)) $allowgroups[] = $value;
									break;
								case 'aclgroup':
									if(!$access && $this->_getGroupName($value)) $access = true;
									break;
							}
						}
					}
				}
			}
			
			if ($access) {
				foreach ($allowgroups as $group) {
					$this->session->set('group.' .$group, true, __CLASS__);
				}
				$text = preg_replace('#\\{password(\\s[^\\}]+)?\\}#', '<!--CPW_VOLATILE-->', $text);
			} else {
				$text = $this->_getDialog($description, $title, !empty($password));
			}
		}
		if ($access) {
			$allowedAreas = $this->session->get($storeId.'.areas', array(), __CLASS__);
			$this->_passwordArea($text, $password, $allowedAreas);
			$this->session->set($storeId.'.areas', $allowedAreas, __CLASS__);
			$this->session->set($storeId, true, __CLASS__);
		}
	}
	
	
	
	private function _passwordArea(&$text, $password, &$allowedAreas) {
		// prcoess nested blocks of {passwordarea}
		$stack = array();
		$start = null;
		while (true) {
			if ($start === null) {
				$start = strpos($text, '{passwordarea');
				while ($start !== FALSE && !preg_match('#\\G\\{passwordarea(\\s[^\\}]+)\\}#', $text, $parameters, 0, $start) ) {
					$start = strpos($text, '{passwordarea', $start + 13);
				}
			}
			$end = strpos($text, '{/passwordarea}');
			
			if ($end === FALSE && $start === FALSE) {
				$stack[] = $text;
				$text = '';
				break;
			} else if ($start === FALSE || $end < $start) {
				$stack[] = substr($text, 0, $end);
				$stack[] = FALSE;
				$text = substr($text, $end+15);
				if ($start) $start -= $end+15;
			} else {
				$stack[] = substr($text, 0, $start);
				$stack[] = array( $parameters[1] );
				$text = substr($text, $start+strlen($parameters[0]));
				$start = null;
			}
		}
		$text = '';
		$areaIndex = 0;
		while (($item = array_shift($stack)) !== null) {
			if (is_string($item)) {
				$text .= $item;
			} else if (is_array($item)) {
				$areaIndex++;
				$access = in_array($areaIndex, $allowedAreas);
				$title = ''; $desc = '';
				$allowgroups = array();
				if (!$access && preg_match_all('#(\\w+)\\s*=\\s*(["\'])(.*?)\\2#', htmlspecialchars_decode($item[0], ENT_QUOTES), $params, PREG_SET_ORDER)) {
					foreach ($params as $param) {
						$value = trim($param[3]);
						switch ($param[1]) {
							case 'title':
								$title = $value;
								break;
							case 'desc':
								$description = $value;
								break;
							case 'sql':
								if (!$access) $access = $this->_executeSQL($value, $password);
								break;
							case 'pass':
								if (!$access && !empty($password) && $password == $value) $access = true;
								break;
							case 'group':
								if ($this->_evaluateGroup($value, $password, true)) $access = true;
								break;
							case 'passgroup':
								if (!$access) $access = $this->_evaluateGroup($value, $password);
								break;
							case 'allowgroup':
								if (!empty($value)) $allowgroups[] = $value;
								break;
							case 'aclgroup':
								if(!$access && $this->_getGroupName($value)) $access = true;
								break;
						}
					}
				}
				if ($access) {
					foreach ($allowgroups as $group) {
						$this->session->set('group.' .$group, true, __CLASS__);
					}
					$allowedAreas[] = $areaIndex;
					$text .= '<!--CPW_VOLATILE-->';
				} else {
					$cnt = 1;
					while ($cnt > 0 && ($item = array_shift($stack)) !== null) {
						if (is_array($item)) $cnt++;
						else if ($item === FALSE) $cnt--;
					}
					$text .= $this->_getDialog($desc, $title, !empty($password));
				}
			} 
		}
	
	}


	private function _getGroupName($group_name){
    		$db = JFactory::getDBO();
    		$db->setQuery($db->getQuery(true)->select('*')->from("#__usergroups"));
    		$groups = $db->loadRowList();
	        $user = JFactory::getUser();
		$groups_acl = $user->groups;
		//$groups_acl = $user->getAuthorisedViewLevels();
		//echo "Value: $group_name";
    		foreach ($groups as $group) {
			foreach ($groups_acl as $group_acl) {				
        				if ($group[0] == $group_acl && $group_name == $group[4]) { // $group[4] holds the name of current group	
						//return $group[4];        // $group[0] holds group ID
            					//echo "| $group[0] $group[4] % $group_acl |";
						return true;
						}
			}
		}
    		return false; // return false if group name not found
	}


	
	private function _getDialog($description, $title, $error = FALSE) {
		self::$uniqid++;
		$formid = 'cpwinp_'.self::$uniqid;
		
		if (empty($description)) $description =  JText::_('PLG_CONTENTPASSWORD_FORM_DESCRIPTION');		
		if (empty($title)) $title = JText::_('PLG_CONTENTPASSWORD_FORM_TITLE');
		
		$this->tmpl->assignRef('description', $description);
		$this->tmpl->assignRef('title', $title);
		$this->tmpl->assign('error', $error);
		$this->tmpl->assign('formid', $formid);
		$this->tmpl->assign('action', JRequest::getURI());
		return '<!--CPW_VOLATILE-->' . $this->tmpl->loadTemplate();

		/*
		if ($error) $error = '<br /><span class="contentpassword-error">' . JText::_('PLG_CONTENTPASSWORD_FORM_ERROR') . '</span>';
		return '<div class="contentpassword"><h2 class="contentpassword-title">'.$title."</h2>\n"
				. '<p class="contentpassword-description">'.$description . ($error!==FALSE?$error:'') . "</p>\n"
				. '<form class="contentpassword-form" name="contentpassword_form" method="post" action="'.JRequest::getURI()."\">\n"
				. '<label for="'.$formid.'" class="contentpassword-label" >'.JText::_('PLG_CONTENTPASSWORD_FORM_LABEL').'</label> '
				. '<input type="password" id="'.$formid.'" class="contentpassword-password" name="contentpassword_password" />'."\n"
				. '<input type="submit" class="contentpassword-submit" name="contentpassword_submit" value="'.JText::_('PLG_CONTENTPASSWORD_FORM_SUBMIT').'" />'
				. '</form></div>';
		*/
	}
	
	private function _evaluateGroup($group, $password, $storeAccess = FALSE) {
		// if storeAccess: check for stored access
		if (empty($group)) return FALSE;
		if ($storeAccess && $this->session->get('group.' .$group, FALSE, __CLASS__)) return true;
		if (empty($password)) return FALSE;
		$dogroupsql = $this->params->get('dogroupsql', 0);
		$access = FALSE;
		$groups = $this->params->get('groups', null);
		$isobj = is_object($groups);
		if ( ($isobj && !empty($groups->$group)) || (is_array($groups) &&  !empty($groups[$group])) ) {
			// execute group sql
			$sql = $isobj ? trim($groups->$group->sql) : trim($groups[$group]['sql']);
			if ($dogroupsql && !empty($sql)) $access = $this->_executeSQL($sql, $password, true);
			
			// compare group passwords
			if (!$access) {
				foreach (explode("\n", $isobj ? $groups->$group->passwords : $groups[$group]['passwords']) as $test) {
					if ($password == trim($test)) {
						$access = true;
						break;
					}
				}
			}
			
		} 
		// if storeAccess: save access
		if ($access && $storeAccess) $this->session->set('group.' .$group, true, __CLASS__);
		return $access;
	}
	
	private function _executeSQL($query, $password, $group=FALSE) {
		if (empty($query) || empty($password)) return FALSE;
		// option: disable
		if (!($group || $this->params->get('dosql', 0))) return FALSE;
		// - simple sanity check
		if (preg_match('/\\b(INSERT|REPLACE|UPDATE|BENCHMARK)\\b/i', $query)) return false;
		// if (stripos($query, 'INSERT') !== FALSE ||stripos($query, 'REPLACE') !== FALSE || stripos($query, 'UPDATE') !== FALSE || stripos($query, 'BENCHMARK') !== FALSE) {
			// return FALSE;
		// }
		
		// insert password, execute query
		$db = JFactory::getDBO();
		return null !== ($db->setQuery(str_replace("{password}", $db->quote($password), $query))->loadResult());
	}
}

class plgViewContentContentPassword {
	
	protected $_name;
	protected $_type;
	protected $_params;
	
	function __construct($path, $type, $name) {
		$this->pluginPath = dirname(__FILE__);
		$this->_name = $name;
		$this->_type = $type;
		$this->_params = array();
		if (!empty($path)) {
			$this->pluginPath = $path;
		}
	}
	
	function clear($key = null) {
		if (empty($key)) {
			foreach ($this->_params as $key => $val) {
				unset($this->$key);
			}
			$this->_params = array();
			return true;
		} else if (isset($this->_params[$key])) {
			unset($this->$key);
			unset($this->_params[$key]);
			return true;
		}
		return false;
	}
	
	function assign($key, $val)
    {
        if (is_string($key) && substr($key, 0, 1) != '_')
        {
            unset($this->$key);
            $this->$key = $val;
			$this->_params[$key] = true;
            return true;
        }
        return false;
    }

	function assignRef($key, &$val)
    {
        if (is_string($key) && substr($key, 0, 1) != '_')
        {
            unset($this->$key);
            $this->$key =& $val;
			$this->_params[$key] = true;
            return true;
        }
        return false;
    }
    
    function loadTemplate($name = 'default')
    {
        $override = JPATH_SITE.DS.'templates'.DS.JFactory::getApplication()->getTemplate().DS.'html'.DS
            .'plg_'.$this->_type.'_'.$this->_name.DS.$name.'.php';
        ob_start();
        if (is_readable($override)) {
            include($override);
        }
        else if (is_readable($this->pluginPath.DS.'tmpl'.DS.$name.'.php')) {
            include($this->pluginPath.DS.'tmpl'.DS.$name.'.php');
        }
        else
        {
            ob_end_clean();
            JError::raiseError(500, JText::_('Failed to load template '.$name.'.php'));
            return '';
        }
        return trim(ob_get_clean());
    }
}

?>
