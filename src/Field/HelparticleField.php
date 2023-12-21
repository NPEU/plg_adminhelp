<?php
namespace NPEU\Plugin\System\AdminHelp\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\UsergrouplistField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\Database\DatabaseInterface;

defined('_JEXEC') or die;

#JFormHelper::loadFieldClass('list');

/**
 * Form field for a list of admin groups.
 */
class HelpArticleField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'HelpArticle';

    /**
     * The message text.
     *
     * @var    string
     */
    protected $article_id;

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
            $db = Factory::getDBO();
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
	public function renderField($options = [])
    {
        // For some reason the base FormField class does not expose the class attribute to the
        // layout date / options. Forcing it here.
        $options['class'] = $this->class;
        return parent::renderField($options);
    }
}