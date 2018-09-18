<?php
/**
 * @package    System - Custom Module Images Plugin
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

JLoader::register('FieldTypesFilesHelper', JPATH_PLUGINS . '/fieldtypes/files/helper.php');

class plgSystemModCustomImages extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Images root path
	 *
	 * @var    string
	 *
	 * @since  1.2.0
	 */
	protected $images_root = 'images/modules';

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

		$moduleData = new Registry($data);

		if ($app->isAdmin() && $app->input->get('option') == 'com_modules' &&
			$form->getName() == 'com_modules.module' && $moduleData->get('module') == 'mod_custom')
		{
			Form::addFormPath(__DIR__);
			$form->loadFile('form', false);

			// Set images folder root
			$form->setFieldAttribute('images_folder', 'root', $this->images_root);
		}

		return true;
	}

	/**
	 * Method to save images
	 *
	 * @param string  $context
	 * @param object  $extension
	 * @param boolean $isNew
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	function onExtensionAfterSave($context, $extension, $isNew)
	{
		if ($context == 'com_modules.module' && $extension->module == 'mod_custom')
		{
			$data = Factory::getApplication()->input->post->get('jform', array(), 'array');

			// Save images
			if ($isNew && !empty($data['images_folder']))
			{
				$filesHelper = new FieldTypesFilesHelper();
				$filesHelper->moveTemporaryFolder($data['images_folder'], $extension->id, $this->images_root);
			}
		}

		return true;
	}

	/**
	 * Method for delete imageFolder
	 *
	 * @param string $context
	 * @param object $extension
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function onExtensionAfterDelete($context, $extension)
	{
		if ($context == 'com_modules.module' && $extension->module == 'mod_custom')
		{
			$filesHelper = new FieldTypesFilesHelper();
			$filesHelper->deleteItemFolder($extension->id, $this->images_root);
		}
	}
}