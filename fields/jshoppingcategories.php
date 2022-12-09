<?php
/**
 * @package     WT JShopping JForm fields
 * @version     1.0.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

class JFormFieldJshoppingcategories extends JFormFieldList
{

	protected $type = 'jshoppingcategories';

	protected function getOptions()
	{

		$options = array(
			'0' => Text::_('JALL'),
		);
		if ((new Version())->isCompatible('4.0') == true)
		{

			if (!class_exists('JSHelper') && file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
			{
				require_once(JPATH_SITE . '/components/com_jshopping/bootstrap.php');
			}
			elseif (!file_exists(JPATH_SITE . '/components/com_jshopping/bootstrap.php'))
			{
				return '-- JoomShopping component is not installled --';
			}

		}
		else
		{
			if (file_exists(JPATH_SITE . '/components/com_jshopping/lib/factory.php'))
			{
				require_once(JPATH_SITE . '/components/com_jshopping/lib/factory.php');
				require_once(JPATH_SITE . '/components/com_jshopping/lib/functions.php');
			}
			else
			{
				return '-- JoomShopping component is not installled --';
			}
		}


		if ((new Version())->isCompatible('4.0') == true)
		{
			$allcats = \JSHelper::buildTreeCategory(0);
		}
		else
		{
			define('_JSHOP_CATEGORY', 'Category');
			$allcats = buildTreeCategory(0);
		}

		foreach ($allcats as $category)
		{

			$options[] = HTMLHelper::_('select.option', $category->category_id, $category->name);

		}

		return $options;

	}
}

?>