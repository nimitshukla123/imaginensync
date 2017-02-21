<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Regular License.
 * You may not use any part of the code in whole or part in any other software
 * or product or website.
 *
 * @author		MagenThemes
 * @copyright	Copyright (c) 2014 MagenThemes
 * @license		Regular License http://themeforest.net/licenses/regular 
 */

class MagenThemes_Dataporter_Helper_Cfgporter_Data extends Mage_Core_Helper_Abstract
{
	const PRESET_FILE_MAIN_DIR		= 'config';

	/**
	 * Modules associated with package
	 *
	 * @var array
	 */
	protected $_packageModules = array(
		'MagenThemes_MTFastion' =>
			array('MagenThemes_MTFastion', 'MT_Attribute', 'MagenThemes_AjaxCart', 'MT_Filter', 'MT_Newsletter'),
	);

	/**
	 * Human-readable names of modules
	 *
	 * @var array
	 */
	protected $_moduleNames = array(
		'MagenThemes_MTFastion' => 'MTFastion',
		'MT_Attribute'			=> 'Attribute',
		'MagenThemes_AjaxCart'			=> 'AjaxCart',
		'MT_Filter'			=> 'Filter',
		'MT_Newsletter'		=> 'Newsletter',
	);

	/**
	 * File path elements
	 *
	 * @var string
	 */
	protected $_presetFileExt = 'xml';
	protected $_presetFileModuleDirType = 'etc';
	protected $_presetFileTmpBaseDir;

	/**
	 * Resource initialization
	 */
	public function __construct()
	{
		$this->_presetFileTmpBaseDir = Mage::getBaseDir('export');
	}

	/**
	 * Get modules associated with package
	 *
	 * @param string
	 * @return array
	 */
	public function getPackageModules($package)
	{
		if (isset($this->_packageModules[$package]))
		{
			return $this->_packageModules[$package];
		}
	}

	/**
	 * Get human-readable names of modules
	 *
	 * @return array
	 */
	public function getModuleNames()
	{
		return $this->_moduleNames;
	}

	/**
	 * Get human-readable name of a module
	 *
	 * @param string
	 * @return string
	 */
	public function getModuleName($module)
	{
		if (isset($this->_moduleNames[$module]))
		{
			return $this->_moduleNames[$module];
		}
	}

	/**
	 * Determines and returns file path of the config preset file
	 *
	 * @param string
	 * @param string
	 * @return string
	 */
	public function getPresetFilepath($filename, $modulename)
	{
		$baseDir = $this->getPresetDir($modulename);
		return $baseDir . DS . $filename . '.' . $this->_presetFileExt;
	}

	/**
	 * Determines and returns path of the directory with config preset files
	 *
	 * @param string
	 * @return string
	 */
	public function getPresetDir($modulename)
	{
		if ($modulename)
		{
			$baseDir = Mage::getConfig()->getModuleDir($this->_presetFileModuleDirType, $modulename);
		}
		else
		{
			$baseDir = $this->_presetFileTmpBaseDir . DS . MagenThemes_Dataporter_Helper_Data::FILE_TOP_LEVEL_DIR;
		}

		return $baseDir . DS . MagenThemes_Dataporter_Helper_Data::FILE_MAIN_DIR . DS . self::PRESET_FILE_MAIN_DIR;
	}
}
