<?php
/**
* @version		$Id$ 2011/08/16 cpgroups.php
* @package		Contentpassword
* @copyright	ProgAndy
* @license		GNU General Public License 2 or later
*/

defined('JPATH_PLATFORM') or die;
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldCPGroups extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'CPGroups';
	protected $_name = 'CPGroups';
	
	function fetchElement($elementname, $value, &$node, $control_name)
	{
		$this->id = $control_name . '[' . $elementname . ']';
		$this->name = $this->id;
		$this->value = $value;
		if (!$node->attributes( 'class' )) $this->class = $node->attributes( 'class' );
		return $this->getInput();
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   11.1
	 */
	protected function getInput()
	{
		$class = empty($this->class) ? '' : $this->class;
		$html = '<div class="cpgroupsfield ' . $class . '" id="' . $this->id . '" style="clear:both">'."\n";
		
		$html .= '<p>'.JText::_('FORMFIELD_CPGROUPS_INFO').'</p>';
		$html .= '<p><a href="javascript:void(0);" class="cpgroup-add">'.JText::_('FORMFIELD_CPGROUPS_ADD').'</a></p>';

		if (!empty($this->value ) && is_array($this->value)) {
			foreach ($this->value as $name => $options) {
				if (!is_array($options)) $options = array('sql'=>'', 'passwords'=>'');
				
				$html .= '<h3 class="cpgroup-toggler" id="'.$this->id.'-toggler-'.$name.'"><a href="javascript:void(0);">';
				$html .= $name . '</a><a href="javascript:void(0);" class="cpicon-remove" title="'.JText::_('FORMFIELD_CPGROUPS_REMOVE').'"> '.JText::_('FORMFIELD_CPGROUPS_REMOVE');
				$html .= '</a><a href="javascript:void(0);" class="cpicon-rename" title="'.JText::_('FORMFIELD_CPGROUPS_RENAME').'"> '.JText::_('FORMFIELD_CPGROUPS_RENAME').'</a></h3>'."\n";
				$html .= '<div id="'.$this->id.'-slider-'.$name.'" class="cpgroup-slider">'."\n";
				$html .= '<label for="'.$this->id.'-group-'.$name.'-sql">'.JText::_('FORMFIELD_CPGROUPS_SQL')."</label>\n";
				$html .= '<input type="text" id="'.$this->id.'-group-'.$name.'-sql" name="' . $this->name . '[' . $name . '][sql]" value="'.htmlspecialchars($options['sql'], ENT_COMPAT, 'UTF-8').'" />' . "\n";
				$html .= '<label for="'.$this->id.'-group-'.$name.'-passwords">'.JText::_('FORMFIELD_CPGROUPS_PASSWORDS')."</label>\n";
				$html .= '<textarea id="'.$this->id.'-group-'.$name.'-passwords" name="' . $this->name . '[' . $name . '][passwords]">' . "\n";
				$html .= htmlspecialchars($options['passwords'], ENT_COMPAT, 'UTF-8');
				$html .= '</textarea>' . "\n";
				$html .= "</div>\n\n";
			}
		}

		$html .= '</div>';


		//JFactory::getDocument()->addScriptDeclaration($js);
		$path_field = str_replace(DS, '/', substr(dirname(__FILE__), strlen(JPATH_ROOT)));
		JFactory::getDocument()->addScript(JURI::root(true) . $path_field . '/cpgroupscript.js');
		JFactory::getDocument()->addStyleSheet(JURI::root(true) . $path_field . '/cpgroups.css');
		JFactory::getDocument()->addScriptDeclaration('var CPGroupLang = { rename: \'' . JText::_('FORMFIELD_CPGROUPS_RENAME') . '\', remove: \'' . JText::_('FORMFIELD_CPGROUPS_REMOVE')
					. '\', sql: \'' . JText::_('FORMFIELD_CPGROUPS_SQL') . '\', passwords: \'' . JText::_('FORMFIELD_CPGROUPS_PASSWORDS') 
					. '\', askname: \'' . JText::_('FORMFIELD_CPGROUPS_ASKNAME') . '\', invalidname: \'' . JText::_('FORMFIELD_CPGROUPS_INVALIDNAME') 
					. '\', nameexists: \'' . JText::_('FORMFIELD_CPGROUPS_NAMEEXISTS') . "' };\n\n"
					. 'window.addEvent("domready", function() { var cpw_' . $this->id . ' = new CPGroupClass("' . $this->id . '", "' . $this->name . '")});');
		
		return $html;
	}
}

?>