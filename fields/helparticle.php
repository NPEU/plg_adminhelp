<?php
// NOTE: Currently unsued but keep for reference:

/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) NPEU 2018.
 * @license     MIT License; see LICENSE.md
 */

defined('JPATH_PLATFORM') or die;

/**
 */
class JFormFieldHelpArticle extends JFormField
{
    /**
     * The message text.
     *
     * @var    string
     */
    protected $article_id;
    
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'HelpArticle';
    
    /**
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    /*public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element->addAttribute('class', 'form-vertical');
        $result = parent::setup($element, $value, $group);

        return $result;
    }*/

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        #return '<p>No help article available.</p>';
        #echo '<pre>'; var_dump($this->element['article_id']); echo '</pre>'; exit;
        
        if (empty($this->element['article_id'])) {
            $help = '<p>No help article available.</p>';
        } else {
            $db = JFactory::getDBO();
            $query = '
                SELECT `title`, `introtext`, `fulltext`
                FROM #__content
                WHERE id = ' . $this->element['article_id'] . ';
            ';
            
            $db->setQuery($query);
            $article = $db->loadObject();
            $help = $article->introtext . $article->fulltext;
        }
        
        $return = '<div class="container-fluid">' . $help . '</div>';

        return $return;
    }
    
    /**
	 * Method to get a control group with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control group
	 *
	 * @since   3.2
	 */
	public function renderField($options = array())
    {
        // For some reason the base FormField class does not expose the class attribute to the 
        // layout date / options. Forcing it here.
        $options['class'] = $this->class;
        return parent::renderField($options);
    }
}
