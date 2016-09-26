<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Forbidden2login
 *
 * @copyright   Copyright (C) 2016 José A. Cidre Bardelás. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class PlgSystemForbidden2login extends JPlugin
{
	/**
	 * Load the language file on instantiation. Note this is only available in Joomla 3.1 and higher.
	 * If you want to support 3.0 series you must override the constructor
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	public function onAfterRoute()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		// Set the variables.
		$input = JFactory::getApplication()->input;
		$extension = $input->getCmd('option', '');
		$view = $input->getCmd('view', '');
		$articleId = $input->getInt('id', 0);


		// Check if the plugin should be activated in this environment.
		if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
		{
			return true;
		}

		$user = JFactory::getUser();

		if($extension == 'com_content' && $view == 'article' && $user->guest)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->clear()
				->select('access')
				->from('#__content')
				->where('id = ' . $articleId);
			$db->setQuery($query);

			try
			{
				$access = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$access = false;
			}

			if($access == 2)
			{
				$app = JFactory::getApplication();

				$uri = JUri::getInstance();
				$redirectURL= urlencode(base64_encode($uri->toString())); 
				$link = 'index.php?option=com_users&view=login&return=' . $redirectURL;
				$msg = JText::_('PLG_SYSTEM_FORBIDDEN2LOGIN_COM_CONTENT_ALERT');
				$app->redirect($link, $msg);
			}
		}

		return true;
	}
}
