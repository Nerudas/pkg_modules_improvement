<?php
/**
 * @package    System - Custom Module Images Plugin
 * @version    1.0.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

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
}