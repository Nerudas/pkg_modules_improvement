<?php
/**
 * @package    Advanced Module Assignment Plugin
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class plgSystemAdvModAssign extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add Advanced Assignment fields
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
		if ($app->isAdmin() && $app->input->get('option') == 'com_modules' &&
			$form->getName() == 'com_modules.module')
		{
			Form::addFormPath(__DIR__);
			$form->loadFile('form', false);
		}

		return true;
	}

	/**
	 *  Advanced Assignment check in modules array
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
			if (!$this->checkAdvancedAssignment($module))
			{
				unset($modules[$key]);
			}
		}
	}

	/**
	 * Advanced Assignment check on render module
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
		$module = ($this->checkAdvancedAssignment($module)) ? $module : false;

		return;
	}

	/**
	 * Advanced Assignment check
	 *
	 * @param $module
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	protected function checkAdvancedAssignment($module)
	{
		$params = new Registry ($module->params);
		if ($params->get('advmodassign'))
		{
			$assignments = ($params->get('advmodassign_clauses', '')) ?
				ArrayHelper::fromObject($params->get('advmodassign_clauses'), false) : array();
			foreach ($assignments as $assignment)
			{
				$value = array_map('trim', explode(',', $assignment->value));
				$equal = ($assignment->operator != 'not_equal');

				$parameter = Factory::getApplication()->input->get(trim($assignment->parameter), '', 'raw');
				if ($assignment->parameter == 'region')
				{
					BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/components/com_location/models', 'LocationModel');
					$regionModel = BaseDatabaseModel::getInstance('Regions', 'LocationModel', array('ignore_request' => true));
					$user        = Factory::getUser();
					if ((!$user->authorise('core.edit.state', 'com_location')) &&
						(!$user->authorise('core.edit', 'com_location')))
					{
						$regionModel->setState('filter.published', 1);
					}
					else
					{
						$regionModel->setState('filter.published', array(0, 1));
					}

					$parameter = $regionModel->getVisitorRegion()->id;
				}

				if (!empty($parameter) && !empty($value))
				{
					if (($equal && !in_array($parameter, $value)) || (!$equal && in_array($parameter, $value)))
					{
						return false;
					}
				}
			}
		}

		return true;
	}
}