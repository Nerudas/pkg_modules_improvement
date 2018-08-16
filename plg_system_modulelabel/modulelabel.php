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
		$app       = Factory::getApplication();
		$component = $app->input->get('option', '');
		$view      = $app->input->get('view', '');
		if ($app->isAdmin() && $component == 'com_modules' && ($view == 'modules' || empty($view)))
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
