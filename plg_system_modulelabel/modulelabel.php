<?php
/**
 * @package    System - Module Label Plugin
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

class plgSystemModuleLabel extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add style to label in admin panel
	 *
	 * @param  Form  $form The form to be altered.
	 * @param  mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since 1.0.0
	 */
	function onContentPrepareForm($form, $data)
	{
		$app = Factory::getApplication();

		if ($app->isAdmin() && $app->input->get('option', '') == 'com_modules')
		{
			$formName = $form->getName();

			// Admin modules list
			if ($formName == 'com_modules.modules.filter')
			{
				Factory::getDocument()->addScriptDeclaration("jQuery(document).ready(function () {
					jQuery('#moduleList').find('tr a').each(function () {
						var pattern = /\[(.*?)]/g;
						var html = jQuery(this).html();
						if (pattern.test(html)) {
							jQuery(this).html(html.replace(pattern, '<span class=\"label label-inverse\">$1</span>'));
						}
					});
				});");
			}

			// Admin module
			if ($formName == 'com_modules.module')
			{
				// Prepare title
				if (is_object($data) && !empty($data->title))
				{
					$data->labels[] = array();
					preg_match_all('/\[.*?]/', $data->title, $matches);
					if (!empty($matches[0]))
					{
						foreach ($matches[0] as $label)
						{
							$data->labels[] = $label;
							$data->title    = trim(str_replace($label, '', $data->title));
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 *  Replace labels in modules array
	 *
	 * @param   $modules  The module object.
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	function onAfterModuleList(&$modules)
	{
		foreach ($modules as $key => &$module)
		{
			$module->title = trim(preg_replace('~\[(.?)*\]~', '', $module->title));
		}
	}

	/**
	 * Replace labels on module render
	 *
	 * @param  object $module  The module object.
	 * @param  array  $attribs The render attributes
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	function onRenderModule(&$module, &$attribs)
	{
		$module->title = trim(preg_replace('~\[(.?)*\]~', '', $module->title));
	}
}
