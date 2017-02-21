<?php
/**
 * @category    MT
 * @package     MT_Widget
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_Widget_CollectionController extends Mage_Core_Controller_Front_Action{
    public function getAction(){
        //if (!$this->_validateFormKey()) return null;
        if (!$this->getRequest()->isPost()) return null;

        $type   = $this->getRequest()->getPost('type');
        $value  = $this->getRequest()->getPost('value');

        if (!$type && !$value) return null;

        $limit      = $this->getRequest()->getPost('limit', 10);
        $carousel   = $this->getRequest()->getPost('carousel', 0);
        $column     = $this->getRequest()->getPost('column', 4);
        $loadmore     = $this->getRequest()->getPost('loadmore', 0);
        $row        = $this->getRequest()->getPost('row', 1);
        $offset        = $this->getRequest()->getPost('offset', 0);
        $num_loadmore        = $this->getRequest()->getPost('num_loadmore');
        $cid        = $this->getRequest()->getPost('cid');
        $template   = $this->getRequest()->getPost('template', 'mt/widget/collection.phtml');

        /* @var $helper MT_Widget_Helper_Data */
        $helper = Mage::helper('mtwidget');
        /* @var $block MT_Widget_Block_Widget_Collection */
        $block = $this->getLayout()->createBlock('mtwidget/widget_collection');
        $params = array();

        if ($cid){
            $params['category_ids'] = explode(',', $cid);
        }

        $newoffset = $limit;

        if($this->getRequest()->getPost('show_filter') && $type == 'category'){
            $filterHtml = $this->_renderFilterCollection($value, $limit, $num_loadmore, $offset, $column);
        }

        $block->setTemplate($template);
        $block->setData(array(
            'row'           => $row,
            'column'        => $column,
            'type'          => $type,
            'offset'        => $newoffset,
            'value'          => $value,
            'show_loadmore'  => $loadmore,
            'num_loadmore'  => $num_loadmore,
            'category_ids'   => $cid,
            'carousel'      => $carousel,
            'filter_html'   => isset($filterHtml) ? $filterHtml : '',
            'collection'    => $helper->getProductCollection($type, $value, $params, $limit, $offset)
        ));

        if($offset > 1){
            $response = array('offset' => $newoffset, 'data' =>  $block->toHtml());
            return $this->getResponse()->setBody( json_encode($response) );
        }else{
            return $this->getResponse()->setBody( $block->toHtml() );
        }


    }

    public function loadmoreAction(){
        if (!$this->getRequest()->isPost()) return null;

        $type   = $this->getRequest()->getPost('type');
        $value  = $this->getRequest()->getPost('value');

        if (!$type && !$value) return null;

        $column     = $this->getRequest()->getPost('column', 4);
        $row        = $this->getRequest()->getPost('row', 1);
        $offset        = $this->getRequest()->getPost('offset', 1);
        $num_loadmore        = $this->getRequest()->getPost('num_loadmore');
        $cid        = $this->getRequest()->getPost('cid');
        $template   = $this->getRequest()->getPost('template', 'mt/widget/collection_more.phtml');

        /* @var $helper MT_Widget_Helper_Data */
        $helper = Mage::helper('mtwidget');
        /* @var $block MT_Widget_Block_Widget_Collection */
        $block = $this->getLayout()->createBlock('mtwidget/widget_collection');
        $params = array();
        if ($cid){
            $params['category_ids'] = explode(',', $cid);
        }


        $new_offset = $offset + $num_loadmore;
        $block->setTemplate($template);
        $block->setData(array(
            'row'           => $row,
            'column'        => $column,
            'type'          => $type,
            'offset'        => $new_offset,
            'value'          => $value,
            'num_loadmore'  => $num_loadmore,
            'category_ids'   => $cid,
            'carousel'      => 0,
            'collection'    => $helper->getProductCollection($type, $value, $params, $num_loadmore, $offset)
        ));

        if($offset > 1){
            $response = array('offset' => $new_offset, 'data' =>  $block->toHtml());
            return $this->getResponse()->setBody( json_encode($response) );
        }else{
            return $this->getResponse()->setBody( $block->toHtml() );
        }


    }

    protected function _renderFilterCollection($catId, $limit, $num_loadmore, $offset, $column)
    {
        $category = Mage::getModel('catalog/category')->load($catId);
        $modelLayer = Mage::getSingleton('catalog/layer');
        $modelLayer->setData('current_category', $category);
        $filterableAttributes = $modelLayer->getFilterableAttributes();

        $filters = array();
        foreach ($filterableAttributes as $attribute) {
            if ($attribute->getAttributeCode() == 'price') {
                $filterBlockName = 'catalog/layer_filter_price';
            } elseif ($attribute->getBackendType() == 'decimal') {
                $filterBlockName = 'catalog/layer_filter_decimal';
            } else {
                $filterBlockName = 'catalog/layer_filter_attribute';;
            }
            $block = $this->getLayout()->createBlock($filterBlockName)
                ->setLayer($modelLayer)
                ->setAttributeModel($attribute)
                ->init()
                ->setData(array(
                    'url'    => $category->getUrl(),
                    'num_loadmore'    => $num_loadmore,
                    'offset'    => $offset,
                    'column'    => $column,
                    'limit'    => $limit
                ));
            $block->setTemplate('mt/widget/detail_filter.phtml');

            $filters[] = $block;
        }

        $block = $this->getLayout()->createBlock('mtwidget/widget_collection');

        $block->setData(array(
            'url'    => $category->getUrl()."?",
            'num_loadmore'    => $num_loadmore,
            'typeSoft' => '',
            'offset'    => 0,
            'column'    => $column,
            'limit'    => $limit,
            'filters'    => $filters
        ));
        $block->setTemplate('mt/widget/filter.phtml');

        return $block->toHtml();
    }
}
