<?php
/**
 * @category    MT
 * @package     MT_ProductTabPro
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_ProductTabPro_Block_Producttabpro extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface {
    protected $_productCollection;

    public function renderCollection($type){
        /* @var $block MT_ProductTabPro_Block_Collection */
        $block = $this->getLayout()->createBlock('producttabpro/collection');
        $blockType = $this->getData($type.'_block');
        if($blockType == 'product'){

            $block->setData(array(
                'row'           => $this->getConfig($type.'_row'),
                'column'        => $this->getConfig($type.'_column'),
                'carousel'      => $this->getConfig('scroll'),
                'type'          => $type,
                'collection'    => $this->_getCollection($type)
            ));

            $block->setTemplate('mt/producttabpro/collection.phtml');

            return $block->toHtml();

        } else {
            return "XXXXXXXX";
            $block->setData(array('block' => $this->getBlock($type)));

            $block->setTemplate('mt/producttabpro/block.phtml');

            return $block->toHtml();
        }

    }

    protected function _getCollection($type){

		/* @var $helper MT_Quart_Helper_Data */
		$helper = Mage::helper('producttabpro');
		$params = array();

		if($this->getData($type.'_category'))
                $params['category_ids'] = explode(',', $this->getData($type.'_category'));
		
		$collectionType = $this->getData($type.'_collection');

		$this->_productCollection = $helper->getProductCollection('collection', $collectionType, $params, $this->getLimit());


        return $this->_productCollection;
    }

    public function getLimit(){
        return is_numeric($this->getData('limit')) ? $this->getData('limit') : 10;
    }

    public function getBlock($type){

        $storeId    = Mage::app()->getStore()->getId();

        $blockId = $this->getData($type.'_static_block');

        /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
        $collection = Mage::getResourceModel('cms/block_collection')
            ->addFieldToFilter('identifier', array('eq' => $blockId));

        if ($collection->count()){
            /* @var $block Mage_Cms_Model_Block */
            $block = $collection->getFirstItem();
            $block->load($block->getId());
            $storeIds = $block->getStoreId();
            if ($block->getIsActive() && (in_array($storeId, $storeIds) || in_array(0, $storeIds))){
                return $this->getLayout()->createBlock('cms/block')->setStoreId()->setBlockId($blockId)->toHtml();
            }
        }

        return '';
    }

    public function getTabs($type){
        $tabs = array();
        $categoryIds = explode(',', $this->getData($type.'_category'));
        foreach ($categoryIds as $index => $categoryId){
            /* @var $categoryModel Mage_Catalog_Model_Category */
            $categoryModel = Mage::getModel('catalog/category')->load($categoryId, array('name'));
            if ($categoryModel->getId()){
                $tabs[] = array(
                    'type'  => 'category',
                    'id'    => 'category-' . $categoryModel->getId(),
                    'label' => $categoryModel->getName(),
                    'value' => $categoryModel->getId()
                );
            }
        }

        return $tabs;
    }


    protected function _getKenburnsImages(){
        $prefix = Mage::getBaseUrl('media');
        $images = $this->getData('kenburns_images');
        $out = array();

        if (!is_array($images)){
            $images = explode(',', $images);
        }

        foreach ($images as $image){
            if ($image){
                $out[] = strpos($image, 'http') !== false ? $image : $prefix . $image;
            }
        }

        if (count($out) == 1){
            $out[] = $out[0];
        }

        return $out;
    }

    public function getConfig($name, $type=null){
        /* @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');

        switch ($name){
            case 'countdown':
                return $helper->jsonEncode(array(
                    'enable'            => (bool) $this->getData('countdown'),
                    'yearText'          => Mage::helper('mtwidget')->__('years'),
                    'monthText'         => Mage::helper('mtwidget')->__('months'),
                    'weekText'          => Mage::helper('mtwidget')->__('weeks'),
                    'dayText'           => Mage::helper('mtwidget')->__('days'),
                    'hourText'          => Mage::helper('mtwidget')->__('hours'),
                    'minText'           => Mage::helper('mtwidget')->__('mins'),
                    'secText'           => Mage::helper('mtwidget')->__('secs'),
                    'yearSingularText'  => Mage::helper('mtwidget')->__('year'),
                    'monthSingularText' => Mage::helper('mtwidget')->__('month'),
                    'weekSingularText'  => Mage::helper('mtwidget')->__('week'),
                    'daySingularText'   => Mage::helper('mtwidget')->__('day'),
                    'hourSingularText'  => Mage::helper('mtwidget')->__('hour'),
                    'minSingularText'   => Mage::helper('mtwidget')->__('min'),
                    'secSingularText'   => Mage::helper('mtwidget')->__('sec'),
                    'engineSrc'         => Mage::getBaseUrl('js') . 'mt/extensions/jquery/plugins/jquery.jcountdown.min.js'
                ));
                break;
            case 'kenburns':
                return $helper->jsonEncode(array(
                    'enable'    => $this->getData('background') == 'kenburns',
                    'images'    => $this->_getKenburnsImages(),
                    'overlay'   => $this->getData('background_overlay'),
                    'engineSrc' => Mage::getBaseUrl('js') . 'mt/extensions/jquery/plugins/kenburns.js'
                ));
                break;
            case 'parallax':
                return $helper->jsonEncode(array(
                    'enable'    => $this->getData('background') == 'parallax',
                    'type'      => $this->getData('parallax_type'),
                    'overlay'   => $this->getData('background_overlay'),
                    'video'     => array(
                        'src'       => $this->getData('parallax_video_src'),
                        'volume'    => (bool) $this->getData('parallax_video_volume'),
                    ),
                    'image'     => array(
                        'src'       => $this->getData('parallax_image_src') ?
                                (
                                strpos($this->getData('parallax_image_src'), 'http') === 0 ?
                                    $this->getData('parallax_image_src') :
                                    Mage::getBaseUrl('media') . $this->getData('parallax_image_src')
                                ):
                                null,
                        'fit'       => $this->getData('parallax_image_fit'),
                        'repeat'    => $this->getData('parallax_image_repeat')
                    ),
                    'file'      => array(
                        'poster'    => $this->getData('parallax_video_poster') ?
                                (
                                strpos($this->getData('parallax_video_poster'), 'http') === 0 ?
                                    $this->getData('parallax_video_poster') :
                                    Mage::getBaseUrl('media') . $this->getData('parallax_video_poster')
                                ):
                                null,
                        'mp4'       => $this->getData('parallax_video_mp4') ?
                                (
                                strpos($this->getData('parallax_video_mp4'), 'http') === 0 ?
                                    $this->getData('parallax_video_mp4') :
                                    Mage::getBaseUrl('media') . $this->getData('parallax_video_mp4')
                                ):
                                null,
                        'webm'      => $this->getData('parallax_video_webm') ?
                                (
                                strpos($this->getData('parallax_video_webm'), 'http') === 0 ?
                                    $this->getData('parallax_video_webm') :
                                    Mage::getBaseUrl('media') . $this->getData('parallax_video_webm')
                                ):
                                null,
                        'volume'    => (bool) $this->getData('parallax_video_volume')
                    )
                ));
                break;
            case 'carousel':
                return $helper->jsonEncode(array(
                    'enable'        => (bool) $this->getData('scroll'),
                    'pagination'    => (bool) $this->getData('paging'),
                    'autoPlay'      => is_numeric($this->getData('autoplay')) ? (int) $this->getData('autoplay') : false,
                    'items'         => is_numeric($this->getData($type.'_column')) ? (int) $this->getData($type.'_column') : 4,
                    'singleItem'    => $this->getData('column') == 1,
                    'lazyLoad'      => true,
                    'lazyEffect'    => false,
                    'addClassActive'=> true,
                    'navigation'    => (bool) $this->getData('navigation'),
                    'navigationText'=> array($this->getData('navigation_prev'), $this->getData('navigation_next')),
                    'engineSrc'     => Mage::getBaseUrl('js') . 'mt/extensions/jquery/plugins/owl-carousel/owl.carousel.js'
                ));
                break;
            case 'animation':
                return $helper->jsonEncode(array(
                    'enable'        => (bool) $this->getData('animate'),
                    'animationName' => $this->getData('animation'),
                    'animationDelay'=> is_numeric($this->getData('animation_delay')) ? (int) $this->getData('animation_delay') : 300,
                    'itemSelector'  => $this->getData('animate_item_selector'),
                    'engineSrc'     => Mage::getBaseUrl('js') . 'mt/extensions/wow/wow.js'
                ));
                break;
            case 'quartz_title':
                return $this->escapeHtml($this->getData('quartz_title'));
                break;
            case 'limit':
                return is_numeric($this->getData('limit')) ? (int) $this->getData('limit') : 1;
                break;
            case 'classes':
                return $this->getData('classes') . ($this->getData('animate') ? ' ' . $this->getData('animation') : '');
                break;
            default:
                return $this->getData($name);
        }
    }
}
