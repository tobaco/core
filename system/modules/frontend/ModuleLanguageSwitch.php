<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModuleLanguageSwitch
 *
 * Front end module "language switch".
 * @copyright  Leo Feyer 2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Controller
 */
class ModuleLanguageSwitch extends Module
{

	/**
	 * Related pages
	 * @var array
	 */
	protected $arrRelated = array();

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_language_switch';


	/**
	 * Warn if "addLanguageToUrl" is not set
	 * @return string
	 */
	public function generate()
	{
		if (!$GLOBALS['TL_CONFIG']['addLanguageToUrl'])
		{
			return '<p style="border:1px solid #f90;background:#ffc;padding:6px;margin:6px;font-size:10px">' . $GLOBALS['TL_LANG']['MSC']['switchInfo'] . '</p>';
		}

		global $objPage;
		$time = time();

		$objRelated = $this->Database->prepare("SELECT id FROM tl_page WHERE alias=? AND language!=?" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : "") . " ORDER BY dns DESC")
									 ->execute($objPage->alias, $objPage->language);

		// Return if there are no related pages
		if ($objRelated->numRows < 1)
		{
			return '';
		}

		$this->arrRelated = $objRelated->fetchEach('id');
		return parent::generate();
	}


	/**
	 * Generate the module
	 */
	protected function compile()
	{
		$arrItems = array();
		$arrLanguages = $this->getLanguages();

		foreach ($this->arrRelated as $id)
		{
			$objPage = $this->getPageDetails($id);

			// Skip pages under a different domain
			if ($objPage->domain != '' && $objPage->domain != $this->Environment->host)
			{
				continue;
			}

			$arrItems[] = array
			(
				'language' => $arrLanguages[$objPage->language],
				'href' => $this->generateFrontendUrl($objPage->row()),
				'title' => specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['viewPageIn'], $arrLanguages[$objPage->language])),
				'isoCode' => $objPage->language
			);
		}

		$this->Template->skipId = 'skipNavigation' . $this->id;
		$this->Template->skipNavigation = specialchars($GLOBALS['TL_LANG']['MSC']['skipNavigation']);
		$this->Template->items = $arrItems;
	}
}

?>