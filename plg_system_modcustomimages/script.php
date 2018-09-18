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

use Joomla\CMS\Factory;

class PlgSystemModCustomImagesInstallerScript
{

	/**
	 * Runs right after any installation action is preformed on the component.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	function postflight()
	{
		$folder = JPATH_ROOT . '/images/modules';
		if (!JFolder::exists($folder))
		{
			JFolder::create($folder);
			JFile::write($folder . '/index.html', '<!DOCTYPE html><title></title>');
		}

		return true;
	}

	/**
	 * update
	 *
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 *
	 * @since  1.1.0
	 */
	public function update($parent)
	{
		$db = Factory::getDbo();

		// Remove table
		$db->setQuery($db->convertUtf8mb4QueryToUtf8('DROP TABLE IF EXISTS  `#__modcustomimages`'));
		$db->execute();

		// Replace shortcodes
		$query = $db->getQuery(true)
			->select(array('id', 'content'))
			->from('#__modules')
			->where($db->quoteName('module') . ' = ' . $db->quote('mod_custom'));
		$db->setQuery($query);
		$modules = $db->loadObjectList();


		$images_root = 'images/modules';

		foreach ($modules as $module)
		{
			$imagefolder     = $images_root . '/' . $module->id . '/content';
			$module->content = str_replace('{imageFolder}', $imagefolder, $module->content);

			$db->updateObject('#__modules', $module, array('id'));
		}

		$folders = JFolder::folders(JPATH_ROOT . '/' . $images_root);
		foreach ($folders as $moduleID)
		{
			$moduleFolder  = JPATH_ROOT . '/' . $images_root . '/' . $moduleID;
			$contentFolder = $moduleFolder . '/content';
			if (!JFolder::exists($contentFolder))
			{
				JFolder::create($contentFolder);
				JFile::write($contentFolder . '/index.html', '<!DOCTYPE html><title></title>');
			}
			$files = JFolder::files($moduleFolder, '', false);
			foreach ($files as $file)
			{
				if ($file != 'index.html')
				{
					JFile::move($moduleFolder . '/' . $file, $contentFolder . '/' . $file);
				}
			}
		}
	}
}