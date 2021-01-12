<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.AdminHelp
 *
 * @copyright   Copyright (C) NPEU 2021.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * When using an admin form it's often helpful to have comprehensivbe help to hand. This plugin will look for a specific article in a specific category (based on naming convention) for any admin form, and, if found, load the article in a seperate form tab.
 */
class plgSystemAdminHelp extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * Prepare form.
     *
     * @param   JForm  $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     */
    public function onContentPrepareForm(JForm $form, $data)
    {
        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }

        // Check if there's an admin help article for this form:

        // Forms have names in the form of 'com_siteareas.sitearea'.
        // The Admin Help categories organisation mimicks this, though Joomla stores aliases with
        // hyphens like this:
        // path: admin-help/com-siteareas
        // We can constuct a category path from the form name BEFORE the dot, thus getting the
        // Category ID, and the use that to look for an article with an alias that matches the
        // part of the form name AFTER the dot.
        $db = JFactory::getDBO();

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
            return true;
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
            return true;
        }

        // Add the extra fields to the form.
        JForm::addFormPath(__DIR__ . '/forms');
        $form->loadFile('helparticleform', false);

        // Set the article id on the field:
        $form->setFieldAttribute('help', 'article_id', $article_id);
        return true;
    }
}