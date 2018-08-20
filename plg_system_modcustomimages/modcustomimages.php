<?php
/**
 * @package    System - Custom Module Images Plugin
 * @version    1.0.1
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

jimport('joomla.filesystem.folder');

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
	 * Imagefolder helper helper
	 *
	 * @var    new imageFolderHelper
	 *
	 * @since 1.0.0
	 */
	protected $imageFolderHelper = null;

	/**
	 * Constructor
	 *
	 * @param   object &$subject   The object to observe
	 * @param   array  $config     An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since 1.0.0
	 */
	public function __construct($subject, array $config = array())
	{
		JLoader::register('imageFolderHelper', JPATH_PLUGINS . '/fieldtypes/ajaximage/helpers/imagefolder.php');
		$this->imageFolderHelper = new imageFolderHelper('images/modules');

		parent::__construct($subject, $config);
	}

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
		$moduleId   = $moduleData->get('id', 0);

		if ($app->isAdmin() && $app->input->get('option') == 'com_modules' &&
			$form->getName() == 'com_modules.module' && $moduleData->get('module') == 'mod_custom')
		{
			Form::addFormPath(__DIR__);
			$form->loadFile('form', false);

			$saveurl = Uri::base(true) . '/index.php?option=com_ajax&plugin=modcustomimages&group=system&format=json&id='
				. $moduleId . '&field=images';
			$form->setFieldAttribute('images', 'saveurl', $saveurl . 'images');

			if (!empty($moduleId))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select('images')
					->from('#__modcustomimages')
					->where('id = ' . $moduleId);
				$db->setQuery($query);

				$form->setValue('images', '', $db->loadResult());
			}
		}

		return true;
	}

	/**
	 * Method to save images
	 *
	 * @param string  $context
	 * @param object  $item
	 * @param boolean $isNew
	 *
	 * @return boolean
	 *
	 * @since 1.0.0
	 */
	function onExtensionAfterSave($context, $item, $isNew)
	{
		if ($context == 'com_modules.module' && $item->module == 'mod_custom')
		{
			$data = Factory::getApplication()->input->post->get('jform', array(), 'array');

			$id = $item->id;

			// Save images
			$data['imagefolder'] = (!empty($data['imagefolder'])) ? $data['imagefolder'] :
				$this->imageFolderHelper->getItemImageFolder($id);

			if ($isNew)
			{
				$data['images'] = (isset($data['images'])) ? $data['images'] : array();
			}

			if (isset($data['images']))
			{
				$this->imageFolderHelper->saveItemImages($id, $data['imagefolder'], '#__modcustomimages', 'images', $data['images']);
			}

		}

		return true;
	}

	/**
	 * Method for delete imageFolder
	 *
	 * @param string $context
	 * @param object $item
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function onExtensionAfterDelete($context, $item)
	{
		if ($context == 'com_modules.module' && $item->module == 'mod_custom')
		{
			$this->imageFolderHelper->deleteItemImageFolder($item->id);
		}
	}

	/**
	 * Ajax Save images
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function onAjaxModCustomImages()
	{
		$app   = Factory::getApplication();
		$id    = $app->input->get('id', 0, 'int');
		$value = $app->input->get('value', '', 'raw');
		if (!empty($id))
		{
			$this->imageFolderHelper->saveImagesValue($id, '#__modcustomimages', 'images', $value);
		}
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
			$module = $this->replaceImageFolder($module);
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
		$module = $this->replaceImageFolder($module);

		return;
	}

	/**
	 * Method to replace {imageFolder}
	 *
	 * @param  object $module The module object.
	 *
	 * @return object
	 *
	 * @since 1.0.0
	 */
	protected function replaceImageFolder($module)
	{
		if ($module->module == 'mod_custom')
		{
			$module->imageFolder = (!empty($module->imageFolder)) ? $module->imageFolder :
				$this->imageFolderHelper->getItemImageFolder($module->id);

			$module->content = str_replace('{imageFolder}', $module->imageFolder, $module->content);
		}

		return $module;
	}
}