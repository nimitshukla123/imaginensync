<?php
/**
 * @category    MT
 * @package     MT_ProductTabPro
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_ProductTabPro_CollectionController extends Mage_Core_Controller_Front_Action{
    public function getAction(){
        //if (!$this->_validateFormKey()) return null;
        if (!$this->getRequest()->isPost()) return null;

        $type   = $this->getRequest()->getPost('type');
        $value  = $this->getRequest()->getPost('value');

        if (!$type && !$value) return null;

        $limit      = $this->getRequest()->getPost('limit', 10);
        $carousel   = $this->getRequest()->getPost('carousel', 0);
        $column     = $this->getRequest()->getPost('column', 4);
        $row        = $this->getRequest()->getPost('row', 1);
        $cid        = $this->getRequest()->getPost('cid');
        $template   = $this->getRequest()->getPost('template', 'mt/quartz/collection.phtml');

        /* @var $helper MT_ProductTabPro_Helper_Data */
        $helper = Mage::helper('mtquartz');
        /* @var $block MT_ProductTabPro_Block_Collection */
        $block = $this->getLayout()->createBlock('mtquartz/collection');
        $params = array();

        if ($cid){
            $params['category_ids'] = explode(',', $cid);
        }

        $block->setTemplate($template);
        $block->setData(array(
            'row'           => $row,
            'column'        => $column,
            'carousel'      => $carousel,
            'collection'    => $helper->getProductCollection($type, $value, $params, $limit)
        ));

        return $this->getResponse()->setBody($block->toHtml());
    }
}
