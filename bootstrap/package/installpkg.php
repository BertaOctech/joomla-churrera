<?php
/**
 * @package    [PACKAGE_NAME]
 *
 * @author     [AUTHOR] <[AUTHOR_EMAIL]>
 * @copyright  [COPYRIGHT]
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       [AUTHOR_URL]
 */
defined('_JEXEC' ) or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class pkg_fooInstallerScript
{
	public function preflight($type, $parent) {

		$this->minimum_joomla_release = $parent->get( "manifest" )->attributes()->version;

		if(!$this->phpCheck())
		{
			return false;
		}

		if(!$this->joomlaCheck())
		{
			return false;
		}
	}

	public function update()
	{

		return true;
	}

	public function install($adapter)
	{
		return true;
	}


	public function uninstall($adapter)
	{

	}

	/**
	 * Check Joomla version installed
	 *
	 * @return	boolean
	 */
	protected function joomlaCheck()
	{
		$jversion = new JVersion;

		// Abort if the current Joomla release is older
		if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf("PKG_FOO_INSTALLER_LOW_JOOMLA_WARNING", $this->minimum_joomla_release), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to check php version of the site
	 *
	 * @return boolean
	 */
	protected function phpCheck()
	{
		// Only allow to install on PHP 5.3.1 or later
		if (defined('PHP_VERSION'))
		{
			$version = PHP_VERSION;
		}
		elseif (function_exists('phpversion'))
		{
			$version = phpversion();
		}
		else
		{
			// We set this version as reference
			$version = '5.0.0';
		}

		if (!version_compare($version, '5.6.0', 'ge'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf("PKG_FOO_INSTALLER_LOW_PHP_WARNING", JText::_("PKG_FOO_INSTALLER_PACKAGE_NAME")), 'error');

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Actions after installation
	 *
	 * @param	string	$type	Type of change (install, update or discover_install)
	 * @param	string	$parent	Class calling this method
	 *
	 * @return	void
	 **/
	public function postflight($type, $parent)
	{
		if ($type == 'install')
		{
			// We enable plugins
			$pluginFamilies = array("system", "search", "user", "content");
			$db = JFactory::getDbo();
			$query = "UPDATE #__extensions SET enabled=1 WHERE folder IN ('" . implode("',", $pluginFamilies) . "') and type='plugin' and element='foo'";
			$db->setQuery($query);
			$db->query();
		}

	}

	/**
	 * Manifest validation
	 *
	 * @param	string	$manifest	Manifest files
	 *
	 * @return	boolean
	 **/
	public function getValidManifestFile($manifest)
	{
		$manifestdata = JApplicationHelper::parseXMLInstallFile($manifest);

		if (!$manifestdata)
		{
			return false;
		}

		return $manifestdata;
	}
}
