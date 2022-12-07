<?php
/**
 * @package     WT SEO Meta Templates
 * @subpackage  WT SEO Meta Templates - JoomShopping
 * @version     1.4.2
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2020 Sergey Tolkachyov
 * @license     GNU General Public License v3.0
 * @since       1.0
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Version;

class plgSystemWt_seo_meta_templates_joomshopping extends CMSPlugin
{
	protected $autoloadLanguage = true;

	public function onWt_seo_meta_templatesAddVariables()
	{
		//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
		$seo_meta_template = array();

		if ((new Version())->isCompatible('4.0') == true)
		{
			// Joomla 4
			$isJoomla4 = true;
		}
		else
		{
			// only for Joomla 3.x
			$isJoomla4 = false;
		}


		// Load JoomShopping config and models
		if (!class_exists('\JSFactory') && $isJoomla4 == false)
		{
			JLoader::register('\JSFactory', JPATH_SITE . '/components/com_jshopping/lib/factory.php');
		}
		if (!class_exists('\JSHelper') && $isJoomla4 == true)
		{
			JLoader::register('\JSFactory', JPATH_SITE . '/components/com_jshopping/bootstrap.php');
		}


		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: start');
		$app    = Factory::getApplication();
		$option = $app->input->get('option');
		if ($option == 'com_jshopping')
		{
			$lang         = Factory::getApplication()->getLanguage();
			$current_lang = $lang->getTag();

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping config');

			$variables = array();
			// Short codes for JoomShopping category view
			if ($app->input->get('controller') == 'category' && $app->input->get('task') == 'view')
			{
				$category_id = $app->input->getInt('category_id');

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before load JoomShopping category');

				$jshop_category = JSFactory::getTable('category', 'jshop');
				$jshop_category->load($category_id);

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping category');


				/**
				 * JoomShopping category variables for short codes
				 */
				//JoomShopping category name

				$lang = JSFactory::getLang();

				$name             = $lang->get('name');
				$title            = $lang->get('meta_title');
				$meta_description = $lang->get('meta_description');

				$variables[] = [
					'variable' => 'JSHOP_CATEGORY_NAME',
					'value'    => $jshop_category->$name,
				];
				//JoomShopping category id
				$variables[] = [
					'variable' => 'JSHOP_CATEGORY_ID',
					'value'    => $jshop_category->category_id,
				];

				if ($isJoomla4 == false)
				{
					$category_count_products = $jshop_category->getCountProducts('');
				}
				else
				{
					$productlist = \JSFactory::getModel('category', 'Site\\Productlist');
					$productlist->setTable($jshop_category);
					$productlist->load();
					$category_count_products = $productlist->getTotal();
				}
				//JoomShopping category id
				$variables[] = [
					'variable' => 'JSHOP_CATEGORY_COUNT_PRODUCTS',
					'value'    => $category_count_products,
				];



				/*
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if ($this->params->get('show_debug') == 1)
				{
					$this->prepareDebugInfo('JoomShopping Title', '<p>' . $jshop_category->$title . '</p>');
					$this->prepareDebugInfo('JoomShopping Meta desc', '<p>' . $jshop_category->$meta_description . '</p>');
				}
				if ($this->params->get('global_jshop_category_title_replace') == 1)
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_category->{'meta_title_'.$current_lang};
					 */

					if ($this->params->get('global_jshop_category_title_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE_ONLY_EMPTY') . '</p>');
						}
						if (empty($jshop_category->$title) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_TITLE_FOUND') . '</p>');
							}
							$title_template             = $this->params->get('joomshopping_category_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE') . '</p>');
						}
						$title_template             = $this->params->get('joomshopping_category_title_template');
						$seo_meta_template['title'] = $title_template;
					}

				}

				/*
				 * Если включена глобальная перезапись description категории. Все по формуле.
				 */

				if ($this->params->get('global_jshop_category_description_replace') == 1)
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_category->{'meta_description_'.$current_lang}
					 */

					if ($this->params->get('global_jshop_category_description_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY') . '</p>');
						}

						if (empty($jshop_category->$meta_description) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_META_DESCRIPTION_FOUND') . '</p>');
							}
							$description_template             = $this->params->get('joomshopping_category_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE') . '</p>');
						}
						$description_template             = $this->params->get('joomshopping_category_meta_description_template');
						$seo_meta_template['description'] = $description_template;
					}
				}


				/*
				 * Добавляем или нет суффикс к title и meta-description страницы
				 * для страниц пагинации.
				 */

				//$limitstart - признак страницы пагинации, текущая страница пагинации
				$limitstart = $app->input->get('limitstart');
				if (isset($limitstart) && (int) $limitstart > 0)
				{

					if ($this->params->get('enable_page_title_and_metadesc_pagination_suffix') == 1)
					{
						//Всего товаров в категории

						if ($isJoomla4 == false)
						{
							$total_jshop_category_products = $jshop_category->getCountProducts('');
							$products_per_page           = $jshop_category->getCountProductsPerPage();
						}
						else
						{
							$productlist = \JSFactory::getModel('category', 'Site\\Productlist');
							$productlist->setTable($jshop_category);
							$productlist->load();
							$total_jshop_category_products = $productlist->getTotal();
							$products_per_page           = $productlist->getCountProductsPerPage();
						}

						//Товаров на странице из настроек категории

						$pagination                  = new Pagination($total_jshop_category_products, $limitstart, $products_per_page);
						$current_pagination_page_num = $pagination->pagesCurrent;

						if (!empty($this->params->get('page_title_pagination_suffix_text')))
						{
							// Тексты суффиксов из параметров плагина
							$pagination_suffix_title = sprintf(Text::_($this->params->get('page_title_pagination_suffix_text')), $current_pagination_page_num);
							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['title']) && !empty($pagination_suffix_title))
							{
								$seo_meta_template['title'] = $seo_meta_template['title'] . ' ' . $pagination_suffix_title;
							}
							elseif (!empty($pagination_suffix_title))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['title'] = $jshop_category->$title . ' ' . $pagination_suffix_title;
							}

						}

						if (!empty($this->params->get('page_metadesc_pagination_suffix_text')))
						{

							$pagination_suffix_metadesc = sprintf(Text::_($this->params->get('page_metadesc_pagination_suffix_text')), $current_pagination_page_num);

							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['description']) && !empty($pagination_suffix_metadesc))
							{
								$seo_meta_template['description'] = $seo_meta_template['description'] . ' ' . $pagination_suffix_metadesc;
							}
							elseif (!empty($pagination_suffix_metadesc))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['description'] = $jshop_category->$meta_description . ' ' . $pagination_suffix_metadesc;
							}
						}
					}

				}


				// JoomShopping parent category name
				// Перенесли ниже остальных переменных из-за того, что вызов
				// родительской категории затирает данные основной
				// и определение пустых/не пустых значений title и meta-description работает не правильно.
				if ($jshop_category->category_parent_id != 0)
				{
					$jshop_category->load($jshop_category->category_parent_id);
					$jshop_parent_category_name = $jshop_category->$name;
					$variables[]                = [
						'variable' => 'JSHOP_PARENT_CATEGORY_NAME',
						'value'    => $jshop_parent_category_name,
					];
				}

			}
			// Short codes for JoomShopping product details view
			elseif ($app->input->get('controller') == 'product' && $app->input->get('task') == 'view')
			{
				//JoomShopping functions include. For priceformat
//				require_once(JPATH_SITE . '/components/com_jshopping/lib/functions.php');
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before load JoomShopping product');
				$product_id = $app->input->getInt('product_id');
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping product');

				$category_id   = $app->input->getInt('category_id');
				if($isJoomla4 == false){
					$jshop_product = JSFactory::getTable('product', 'jshop');
				} else {
					$jshop_product = \JSFactory::getTable('product', 'jshop');
				}

				$jshop_product->load($product_id);
				if($isJoomla4 == false)
				{
					$jshop_category = JSFactory::getTable('category', 'jshop');
				} else {
					$jshop_category = \JSFactory::getTable('category', 'jshop');
				}
				$jshop_category->load($category_id);
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping product category');




				/*
				 * JoomShopping category variables for short codes
				 */
				//JoomShopping product id
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_ID',
					'value'    => $jshop_product->product_id,
				];
				//JoomShopping product EAN
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_EAN',
					'value'    => $jshop_product->product_ean,
				];

				//JoomShopping product manufacturer code
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_MFG_CODE',
					'value'    => $jshop_product->manufacturer_code,
				];

				//JoomShopping product quantity
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_QTY',
					'value'    => $jshop_product->product_quantity,
				];

				//JoomShopping product old price

				if ($isJoomla4 == false)
				{
					$old_price = formatprice($jshop_product->product_old_price);
				}
				else
				{
					$old_price = \JSHelper::formatprice($jshop_product->product_old_price);
				}

				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_OLD_PRICE',
					'value'    => $old_price,
				];

				//If we have a product's zero price we can replace digits with text
				if ((int) $jshop_product->product_price == 0 && $this->params->get('replace_zero_price_with_text', 0) == 1 && !empty($this->params->get('zero_price_replace_text', '')))
				{
					$jshop_product_price = $this->params->get('zero_price_replace_text', '');
				}
				else
				{
					if ($isJoomla4 == false)
					{
						$jshop_product_price = formatprice($jshop_product->product_price);
					}
					else
					{
						$jshop_product_price = \JSHelper::formatprice($jshop_product->product_price);
					}
				}

				//JoomShopping product price
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_PRICE',
					'value'    => $jshop_product_price,
				];

				//If we have a product's zero minimal price we can replace digits with text
				if ((int) $jshop_product->product_price == 0 && $this->params->get('replace_zero_price_with_text', 0) == 1 && !empty($this->params->get('zero_price_replace_text', '')))
				{
					$jshop_product_minprice = $this->params->get('zero_price_replace_text', '');
				}
				else
				{
					if ($isJoomla4 == false)
					{
						$jshop_product_minprice = formatprice($jshop_product->min_price);
					}
					else
					{
						$jshop_product_minprice = \JSHelper::formatprice($jshop_product->min_price);
					}

				}

				//JoomShopping product min price
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_MIN_PRICE',
					'value'    => $jshop_product_minprice,
				];

				//JoomShopping product name
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_NAME',
					'value'    => $jshop_product->{'name_' . $current_lang},
				];

				//JoomShopping product category name
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_CATEGORY_NAME',
					'value'    => $jshop_category->{'name_' . $current_lang},
				];

				//JoomShopping product weight

				if ($isJoomla4 == false)
				{
					$jshop_product_weight = formatweight($jshop_product->product_weight);
				}
				else
				{
					$jshop_product_weight = \JSHelper::formatweight($jshop_product->product_weight);
				}
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_WEIGHT',
					'value'    => $jshop_product_weight,
				];

				//JoomShopping product rating
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_RATING',
					'value'    => $jshop_product->average_rating,
				];

				//JoomShopping product hits
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_HITS',
					'value'    => $jshop_product->hits,
				];


				/**
				 * Специфичные сео-формулы для товаров конкретной категории
				 */
				$current_lang_tag = Factory::getApplication()->getLanguage()->getTag();
				$custom_templates_for_products_in_specified_category = array();
				foreach ($this->params->get('custom_templates_for_products_in_specified_category') as $custom_template)
				{
					if($current_lang_tag == $custom_template->template_lang && $category_id == $custom_template->category){
						$custom_templates_for_products_in_specified_category[$custom_template->category]['title']    = $custom_template->title;
						$custom_templates_for_products_in_specified_category[$custom_template->category]['metadesc'] = $custom_template->metadesc;
					}elseif ($current_lang_tag == $custom_template->template_lang &&$custom_template->category == 0){
						// Мультиязычность формул - для "всех" категорий
						$custom_templates_for_products_in_specified_category['all']['title']    = $custom_template->title;
						$custom_templates_for_products_in_specified_category['all']['metadesc'] = $custom_template->metadesc;
					}
				}

				if ($this->params->get('global_jshop_product_title_replace') == 1)
				{

					/**
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_category->{'meta_title_'.$current_lang}
					 */

					if ($this->params->get('global_jshop_product_title_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_TITLE_REPLACE_ONLY_EMPTY') . '</p>');
						}
						if (empty($jshop_product->{'meta_title_' . $current_lang}) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_PRODUCT_TITLE_FOUND') . '</p>');
							}

							if (isset($custom_templates_for_products_in_specified_category[$category_id]))
							{
								// Специфичная сео-формула для материалов данной категории
								$title_template = $custom_templates_for_products_in_specified_category[$category_id]['title'];
								if ($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
								}
							}elseif(isset($custom_templates_for_products_in_specified_category['all'])){
								// Специфичная сео-формула для материалов данной категории
								$title_template = $custom_templates_for_products_in_specified_category['all']['title'];
								if ($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
								}
							}
							else
							{
								// Глобальная сео-формула для всех товаров
								$title_template = $this->params->get('joomshopping_product_title_template');
							}

						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_TITLE_REPLACE') . '</p>');
						}

						if (isset($custom_templates_for_products_in_specified_category[$category_id]))
						{
							// Специфичная сео-формула для материалов данной категории
							$title_template = $custom_templates_for_products_in_specified_category[$category_id]['title'];
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
							}
						}elseif(isset($custom_templates_for_products_in_specified_category['all'])){
							// Специфичная сео-формула для материалов данной категории
							$title_template = $custom_templates_for_products_in_specified_category['all']['title'];
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
							}
						}
						else
						{
							// Глобальная сео-формула для всех товаров
							$title_template = $this->params->get('joomshopping_product_title_template');
						}
					}
					$seo_meta_template['title'] = $title_template;
				}

				/*
				 * Если включена глобальная перезапись description товара. Все по формуле.
				 */

				if ($this->params->get('global_jshop_product_meta_description_replace') == 1)
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_product->meta_description
					 */

					if ($this->params->get('global_jshop_product_meta_description_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_META_DESCRIPTION_REPLACE_ONLY_EMPTY') . '</p>');
						}

						if (empty($jshop_product->{'meta_description_' . $current_lang}) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_PRODUCT_META_DESCRIPTION_FOUND') . '</p>');
							}

							if (isset($custom_templates_for_products_in_specified_category[$category_id]))
							{
								// Специфичная сео-формула для товаров данной категории
								$description_template = $custom_templates_for_products_in_specified_category[$category_id]['metadesc'];
								if ($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - - meta description</p>');
								}
							}elseif(isset($custom_templates_for_products_in_specified_category['all'])){
								// Специфичная сео-формула для материалов данной категории
								$description_template = $custom_templates_for_products_in_specified_category['all']['metadesc'];
								if ($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
								}
							}
							else
							{
								// Глобальная сео-формула для всех товаров
								$description_template = $this->params->get('joomshopping_product_meta_description_template');
							}

						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_META_DESCRIPTION_REPLACE') . '</p>');
						}

						if (isset($custom_templates_for_products_in_specified_category[$category_id]))
						{
							// Специфичная сео-формула для товаров данной категории
							$description_template = $custom_templates_for_products_in_specified_category[$category_id]['metadesc'];
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - - meta description</p>');
							}
						}elseif(isset($custom_templates_for_products_in_specified_category['all'])){
							// Специфичная сео-формула для материалов данной категории
							$description_template = $custom_templates_for_products_in_specified_category['all']['metadesc'];
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_CUSTOM_TEMPLATE_FOR_PRODUCTS_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
							}
						}
						else
						{
							// Глобальная сео-формула для всех товаров
							$description_template = $this->params->get('joomshopping_product_meta_description_template');
						}

					}

					$seo_meta_template['description'] = $description_template;
				}


			}//elseif ($app->input->get('controller') == 'product' && $app->input->get('task') == 'view')
			// JoomShopping manufacturer list products view
			elseif ($app->input->get('controller') == 'manufacturer' && $app->input->get('task') == 'view')
			{


				$manufacturer_id = $app->input->getInt('manufacturer_id');

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before load JoomShopping manufacturer product list');
				if ($isJoomla4 == false)
				{
					$manufacturer = JSFactory::getTable('manufacturer', 'jshop');
				}
				else
				{
					$manufacturer = JSFactory::getTable('manufacturer', 'jshop');
				}

				$manufacturer->load($manufacturer_id);
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping manufacturer product list');

				/**
				 * JoomShopping category variables for short codes
				 */
				//JoomShopping category name
				$lang             = JSFactory::getLang();
				$name             = $lang->get('name');
				$title            = $lang->get('meta_title');
				$meta_description = $lang->get('meta_description');

				$variables[] = [
					'variable' => 'JSHOP_MFG_NAME',
					'value'    => $manufacturer->$name,
				];
				//JoomShopping category id
				$variables[] = [
					'variable' => 'JSHOP_MFG_ID',
					'value'    => $manufacturer_id,
				];



				if ($isJoomla4 == false)
				{
					$mfg_count_products = $manufacturer->getCountProducts('');
				}
				else
				{
					$productlist = \JSFactory::getModel('manufacturer', 'Site\\Productlist');
					$productlist->setTable($manufacturer);
					$productlist->load();
					$mfg_count_products = $productlist->getTotal();
				}

				//JoomShopping category id
				$variables[] = [
					'variable' => 'JSHOP_MFG_COUNT_PRODUCTS',
					'value'    => $mfg_count_products,
				];

				/**
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if ($this->params->get('show_debug') == 1)
				{
					$this->prepareDebugInfo('JoomShopping Title', '<p>' . $manufacturer->$title . '</p>');
					$this->prepareDebugInfo('JoomShopping Meta desc', '<p>' . $manufacturer->$meta_description . '</p>');

				}
				if ($this->params->get('global_manufacturer_title_replace') == 1)
				{

					/**
					 * Если переписываем только пустые. Там, где пустое
					 * $manufacturer->{'meta_title_'.$current_lang};
					 */

					if ($this->params->get('global_manufacturer_title_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_MANUFACTURER_TITLE_REPLACE_ONLY_EMPTY') . '</p>');
						}
						if (empty($manufacturer->$title) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_TITLE_FOUND') . '</p>');
							}
							$title_template             = $this->params->get('joomshopping_manufacturer_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_MANUFACTURER_TITLE_REPLACE') . '</p>');
						}
						$title_template             = $this->params->get('joomshopping_manufacturer_title_template');
						$seo_meta_template['title'] = $title_template;
					}

				}

				/**
				 * Если включена глобальная перезапись description категории. Все по формуле.
				 */

				if ($this->params->get('global_manufacturer_description_replace') == 1)
				{

					/**
					 * Если переписываем только пустые. Там, где пустое
					 * $manufacturer->{'meta_description_'.$current_lang}
					 */

					if ($this->params->get('global_manufacturer_description_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_MANUFACTURER_META_DESCRIPTION_REPLACE_ONLY_EMPTY') . '</p>');
						}

						if (empty($manufacturer->$meta_description) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_META_DESCRIPTION_FOUND') . '</p>');
							}
							$description_template             = $this->params->get('joomshopping_manufacturer_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_MANUFACTURER_META_DESCRIPTION_REPLACE') . '</p>');
						}
						$description_template             = $this->params->get('joomshopping_manufacturer_meta_description_template');
						$seo_meta_template['description'] = $description_template;
					}
				}

				/**
                * Добавляем или нет суффикс к title и meta-description страницы
                * для страниц пагинации.
                */

				//$limitstart - признак страницы пагинации, текущая страница пагинации
				$limitstart = $app->input->get('limitstart');
				if (isset($limitstart) && (int) $limitstart > 0)
				{

					if ($this->params->get('enable_page_title_and_metadesc_pagination_suffix') == 1)
					{
						//Всего товаров в категории
						if ($isJoomla4 == false)
						{
							$total_manufacturer_products = $manufacturer->getCountProducts('');
							$products_per_page           = $manufacturer->getCountProductsPerPage();
						}
						else
						{
							$productlist = \JSFactory::getModel('manufacturer', 'Site\\Productlist');
							$productlist->setTable($manufacturer);
							$productlist->load();
							$total_manufacturer_products = $productlist->getTotal();
							$products_per_page           = $productlist->getCountProductsPerPage();
						}
						//Товаров на странице из настроек категории

						$pagination                  = new Pagination($total_manufacturer_products, $limitstart, $products_per_page);
						$current_pagination_page_num = $pagination->pagesCurrent;

						if (!empty($this->params->get('page_title_pagination_suffix_text')))
						{
							// Тексты суффиксов из параметров плагина
							$pagination_suffix_title = sprintf(Text::_($this->params->get('page_title_pagination_suffix_text')), $current_pagination_page_num);
							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['title']) && !empty($pagination_suffix_title))
							{
								$seo_meta_template['title'] = $seo_meta_template['title'] . ' ' . $pagination_suffix_title;
							}
							elseif (!empty($pagination_suffix_title))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['title'] = $manufacturer->$title . ' ' . $pagination_suffix_title;
							}

						}

						if (!empty($this->params->get('page_metadesc_pagination_suffix_text')))
						{
							$pagination_suffix_metadesc = sprintf(Text::_($this->params->get('page_metadesc_pagination_suffix_text')), $current_pagination_page_num);

							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['description']) && !empty($pagination_suffix_metadesc))
							{
								$seo_meta_template['description'] = $seo_meta_template['description'] . ' ' . $pagination_suffix_metadesc;
							}
							elseif (!empty($pagination_suffix_metadesc))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['description'] = $manufacturer->$meta_description . ' ' . $pagination_suffix_metadesc;
							}
						}
					}
				}
			}


			/**
			 * Include files with custom SEO variables and overrides from
			 * plugins/system/wt_seo_meta_templates_joomshopping/customvariables
			 */
			if (Folder::exists(__DIR__ . '/customvariables'))
			{
				$custom_variables = Folder::files(__DIR__ . '/customvariables');
				if ($this->params->get('show_debug') == 1)
				{
					$this->prepareDebugInfo('Custom variables folder found', __DIR__ . '/customvariables');
					$this->prepareDebugInfo('Custom variables files found (' . count($custom_variables) . ')', $custom_variables);
				}
				foreach ($custom_variables as $custom_variable)
				{
					require_once(__DIR__ . '/customvariables/' . $custom_variable);
				}

			}

			$data = array(
				'variables'          => $variables,
				'seo_tags_templates' => $seo_meta_template,
			);


			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before return data. End.');

			$this->prepareDebugInfo('SEO variables', $data);

			if ($this->params->get('show_debug') == 1)
			{
				$session    = Factory::getSession();
				$debug_info = $session->get("wtseometatemplatesdebugoutput");

				echo "<details style='border:1px solid #0FA2E6; margin-bottom:5px; padding:10px;'>";
				echo "<summary style='background-color:#384148; color:#fff; padding:10px;'>WT SEO Meta templates debug information</summary>";
				echo $debug_info;
				echo '</details>';
				$session->clear("wtseometatemplatesdebugoutput");

			}

			return $data;
		}//if($option == 'com_jshopping')
	}

	/**
	 * Prepare html output for debug info from main function
	 *
	 * @param $debug_section_header string
	 * @param $debug_data           string|array
	 *
	 * @return void
	 * @since 1.4.0
	 */
	private function prepareDebugInfo($debug_section_header, $debug_data): void
	{
		if ($this->params->get('show_debug') == 1)
		{
			$session      = Factory::getSession();
			$debug_output = $session->get("wtseometatemplatesdebugoutput");
			if (!empty($debug_section_header))
			{
				$debug_output .= "<details style='border:1px solid #0FA2E6; margin-bottom:5px;'>";
				$debug_output .= "<summary style='background-color:#384148; color:#fff; padding:10px;'>" . $debug_section_header . "</summary>";
			}

			if (is_array($debug_data) || is_object($debug_data))
			{
				$debug_data   = print_r($debug_data, true);
				$debug_output .= "<pre style='background-color: #eee; padding:10px;'>";
			}

			$debug_output .= $debug_data;
			if (is_array($debug_data) || is_object($debug_data))
			{
				$debug_output .= "</pre>";
			}
			if (!empty($debug_section_header))
			{
				$debug_output .= "</details>";
			}
			$session->set("wtseometatemplatesdebugoutput", $debug_output);
		}
	}
}//plgSystemWt_seo_meta_templates_joomshopping