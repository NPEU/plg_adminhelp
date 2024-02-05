<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.AdminHelp
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Plugin\System\AdminHelp\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * When using an admin form it's often helpful to have comprehensive help to hand. This plugin will look for a specific article in a specific category (based on naming convention) for any admin form, and, if found, load the article in a separate form tab.
 */
class AdminHelp extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    /**
     * An internal flag whether plugin should listen any event.
     *
     * @var bool
     *
     * @since   4.3.0
     */
    protected static $enabled = false;

    /**
     * Constructor
     *
     */
    public function __construct($subject, array $config = [], bool $enabled = true)
    {
        // The above enabled parameter was taken from teh Guided Tour plugin but it ir always seems
        // to be false so I'm not sure where this param is passed from. Overriding it for now.
        $enabled = true;


        #$this->loadLanguage();
        $this->autoloadLanguage = $enabled;
        self::$enabled          = $enabled;

        parent::__construct($subject, $config);
    }

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return self::$enabled ? [
            'onContentPrepareForm' => 'onContentPrepareForm',
        ] : [];
    }

    /**
     * Prepare form and add my field.
     *
     * @param   Form  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   <your version>
     */
    public function onContentPrepareForm(Event $event): void
    {
        $args    = $event->getArguments();
        $form    = $args[0];
        $data    = $args[1];

        if (!($form instanceof \Joomla\CMS\Form\Form)) {
            throw new GenericDataException(Text::_('JERROR_NOT_A_FORM'), 500);
            return;
        }

        #$app    = Factory::getApplication();
        #$option = $app->input->get('option');

        // Check if there's an admin help article for this form:

        // Forms have names in the form of 'com_siteareas.sitearea'.
        // The Admin Help categories organisation mimicks this, though Joomla stores aliases with
        // hyphens like this:
        // path: admin-help/com-siteareas
        // We can constuct a category path from the form name BEFORE the dot, thus getting the
        // Category ID, and the use that to look for an article with an alias that matches the
        // part of the form name AFTER the dot.
        $db = Factory::getDBO();

        $form_name = $form->getName();
        $form_name_parts = explode('.', $form_name);

        $root_cat_id = $this->params->get('root_cat_id');
        $query = '
            SELECT `path`
            FROM #__categories
            WHERE id = "' . $root_cat_id . '";
        ';
        $db->setQuery($query);
        $root_cat_alias = $db->loadResult();

        $cat_path = $root_cat_alias . '/' . str_replace('_', '-', $form_name_parts[0]);
        $query = '
            SELECT `id`
            FROM #__categories
            WHERE path = "' . $cat_path . '";
        ';
        $db->setQuery($query);
        $cat_id = $db->loadResult();

        // If there's no cat_id we're done:
        if (empty($cat_id)) {
            return;
        }

        // Next, look for an article:
        $article_alias = str_replace('_', '-', $form_name_parts[1]);
        $query = '
            SELECT `id`
            FROM #__content
            WHERE alias = "' . $article_alias . '"
            AND catid = ' . $cat_id . ';
        ';
        $db->setQuery($query);
        $article_id = $db->loadResult();

        // If there's no article_id we're done:
        if (empty($article_id)) {
            return;
        }

        // Add the extra fields to the form.
        $plg_dir = dirname(dirname(dirname(__FILE__)));
        #echo "<pre>\n"; var_dump($plg_dir); echo "</pre>\n";exit;
        FormHelper::addFieldPrefix('NPEU\\Plugin\\System\\AdminHelp\\Field');
        FormHelper::addFormPath($plg_dir . '/forms');
        $form->loadFile('helparticleform', false);

        // Set the article id on the field:
        $form->setFieldAttribute('help', 'article_id', $article_id);
        return;
    }
}