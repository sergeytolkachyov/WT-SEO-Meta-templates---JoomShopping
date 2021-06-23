<?php
/**
 * @package     WT SEO Meta Templates
 * @subpackage  WT SEO Meta Templates - JoomShopping
 * @version     1.0.0
 * @Author Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2020 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0
 */
// No direct access
defined( '_JEXEC' ) or die;
use Joomla\CMS\Application\CMSApplication as App;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
class plgSystemWt_seo_meta_templates_joomshopping extends CMSPlugin
{
	public function __construct( &$subject, $config )
	{

		parent::__construct( $subject, $config );
		$this->loadLanguage();
	}

	public function onWt_seo_meta_templatesAddVariables(){
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: start');
		$app = Factory::getApplication();
		$option = $app->input->get('option');
		if($option == 'com_jshopping'){
			$lang = Factory::getLanguage();
			$current_lang = $lang->getTag();

		// Load JoomShopping config and models
			if (!class_exists( 'JSFactory' )) {
				require_once (JPATH_SITE . '/components/com_jshopping/lib/factory.php');
			}
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping config');

			$variables = array();
			// Short codes for JoomShopping category view
			if($app->input->get('controller') == 'category' && $app->input->get('task') == 'view'){
				$category_id = $app->input->getInt('category_id');

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before load JoomShopping category');
				$jshop_category = JSFactory::getTable('category', 'jshop');
				$jshop_category->load($category_id);
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping category');


				/*
				 * JoomShopping category variables for short codes
				 */
				//JoomShopping category name
				$variables[] = [
						'variable' => 'JSHOP_CATEGORY_NAME',
						'value'    => $jshop_category->{'name_'.$current_lang},
					];
				//JoomShopping category id
				$variables[] = [
						'variable' => 'JSHOP_CATEGORY_ID',
						'value'    => $jshop_category->category_id,
					];

				//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
				$seo_meta_template = array();

				/*
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if($this->params->get('show_debug') == 1){
					echo '<h4>WT SEO Meta templates - JoomShopping provider plugin debug</h4>';
					echo '<p><strong>JoomShopping Title</strong>: '.$jshop_category->{'meta_title_'.$current_lang}.'</p>';
					echo '<p><strong>JoomShopping Meta desc:</strong> '.$jshop_category->{'meta_description_'.$current_lang}.'</p>';
				}

				if($this->params->get('global_jshop_category_title_replace') == 1){

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_category->{'meta_title_'.$current_lang};
					 */

					if($this->params->get('global_jshop_category_title_replace_only_empty') == 1){
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE_ONLY_EMPTY');
						}
						if(empty($jshop_category->{'meta_title_'.$current_lang}) == true){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_TITLE_FOUND');
							}
							$title_template = $this->params->get('joomshopping_category_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}else{
					//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE');
						}
						$title_template = $this->params->get('joomshopping_category_title_template');
						$seo_meta_template['title'] = $title_template;
					}

				}

				/*
				 * Если включена глобальная перезапись description категории. Все по формуле.
				 */

				if($this->params->get('global_jshop_category_description_replace') == 1){

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $jshop_category->{'meta_description_'.$current_lang}
					 */

					if($this->params->get('global_jshop_category_description_replace_only_empty') == 1){
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY');
						}

						if(empty($jshop_category->{'meta_description_'.$current_lang}) == true){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_META_DESCRIPTION_FOUND');
							}
							$description_template = $this->params->get('joomshopping_category_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}else{
						//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE');
						}
						$description_template = $this->params->get('joomshopping_category_meta_description_template');
						$seo_meta_template['description'] = $description_template;
					}
				}

				// JoomShopping parent category name
				// Перенесли ниже остальных переменных из-за того, что вызов
				// родительской категории затирает данные основной
				// и определение пустых/не пустых значений title и meta-description работает не правильно.
				if($jshop_category->category_parent_id != 0){
					$jshop_category->load($jshop_category->category_parent_id);
					$jshop_parent_category_name = $jshop_category->{'name_'.$current_lang};
					$variables[] = [
						'variable' => 'JSHOP_PARENT_CATEGORY_NAME',
						'value'    => $jshop_parent_category_name,
					];
				}

			}
			// Short codes for JoomShopping product details view
			elseif ($app->input->get('controller') == 'product' && $app->input->get('task') == 'view'){
				//JoomShopping functions include. For priceformat
				require_once (JPATH_SITE . '/components/com_jshopping/lib/functions.php');
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before load JoomShopping product');
				$product_id = $app->input->getInt('product_id');
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: After load JoomShopping product');

				$category_id = $app->input->getInt('category_id');
				$jshop_product = JSFactory::getTable('product', 'jshop');
				$jshop_product->load($product_id);

				$jshop_category = JSFactory::getTable('category', 'jshop');
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
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_OLD_PRICE',
					'value'    => formatprice($jshop_product->product_old_price),
				];

				//JoomShopping product price
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_PRICE',
					'value'    => formatprice($jshop_product->product_price),
				];

				//JoomShopping product min price
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_MIN_PRICE',
					'value'    => formatprice($jshop_product->min_price),
				];

				//JoomShopping product name
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_NAME',
					'value'    => $jshop_product->{'name_'.$current_lang},
				];

				//JoomShopping product category name
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_CATEGORY_NAME',
					'value'    => $jshop_category->{'name_'.$current_lang},
				];

				//JoomShopping product weight
				$variables[] = [
					'variable' => 'JSHOP_PRODUCT_WEIGHT',
					'value'    => formatweight($jshop_product->product_weight),
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

				if($this->params->get('global_jshop_product_title_replace') == 1){

						/*
						 * Если переписываем только пустые. Там, где пустое
						 * $jshop_category->{'meta_title_'.$current_lang}
						 */

						if($this->params->get('global_jshop_product_title_replace_only_empty') == 1){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_TITLE_REPLACE_ONLY_EMPTY');
							}
							if(empty($jshop_product->{'meta_title_'.$current_lang}) == true){
								if($this->params->get('show_debug') == 1)
								{
									echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_PRODUCT_TITLE_FOUND');
								}
								$title_template = $this->params->get('joomshopping_product_title_template');
								$seo_meta_template['title'] = $title_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_TITLE_REPLACE');
							}
							$title_template = $this->params->get('joomshopping_product_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}

					/*
					 * Если включена глобальная перезапись description товара. Все по формуле.
					 */

					if($this->params->get('global_jshop_product_meta_description_replace') == 1){

						/*
						 * Если переписываем только пустые. Там, где пустое
						 * $jshop_product->meta_description
						 */

						if($this->params->get('global_jshop_product_meta_description_replace_only_empty') == 1){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_META_DESCRIPTION_REPLACE_ONLY_EMPTY');
							}

							if(empty($jshop_product->{'meta_description_'.$current_lang}) == true){
								if($this->params->get('show_debug') == 1)
								{
									echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_EMPTY_PRODUCT_META_DESCRIPTION_FOUND');
								}
								$description_template = $this->params->get('joomshopping_category_meta_description_template');
								$seo_meta_template['description'] = $description_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_JOOMSHOPPING_DEBUG_GLOBAL_PRODUCT_META_DESCRIPTION_REPLACE');
							}
							$description_template = $this->params->get('joomshopping_product_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}


			}//elseif ($app->input->get('controller') == 'product' && $app->input->get('task') == 'view')


			$data = array(
				'variables' => $variables,
				'seo_tags_templates' => $seo_meta_template,
			);


			if($this->params->get('show_debug') == 1)
			{
				echo '<details><summary><strong>$data array sends to WT SEO Meta templates plugin</strong></summary><pre>';
				print_r($data);
				echo '</pre></details><br/>';
			}
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - JoomShopping provider plugin</strong>: Before return data. End.');
			return $data;
		}//if($option == 'com_jshopping')
	}



}//plgSystemWt_seo_meta_templates_joomshopping