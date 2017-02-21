<?php
/**
 *
 * ------------------------------------------------------------------------------
 * @category Mato
 * @package      MT_AjaxCart
 * ------------------------------------------------------------------------------
 * @copyright    Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license      GNU General Public License version 2 or later;
 * @author       ZooExtension.com
 * @email        magento@cleversoft.co
 * ------------------------------------------------------------------------------
 *
 */


class MagenThemes_AjaxCart_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = array())
    {
        if(Mage::getStoreConfig('web/secure/use_in_frontend')) return $this->_getUrlModel()->getUrl($route, array('_secure' => true));
        else return $this->_getUrlModel()->getUrl($route, $params);
    }


    protected function _getProductCollection()
    {
        if($this->getData('type') == 'collection'){
            $this->_productCollection = parent::_getProductCollection();

            $select = $this->_productCollection->getSelect();
            if(!$this->getData('offset')) $limit = $this->getData('limit');
            else $limit = $this->getData('num_loadmore');
            $select->limit($limit, $this->getData('offset'));
            $name = $this->getRequest()->getParam('order');
            $dir  = $this->getRequest()->getParam('dir');
            if($name && $dir) $this->_productCollection->setOrder($name,$dir);
            $productCollection = $this->_productCollection->load();
            return $productCollection;
        }else{
            return parent::_getProductCollection();
        }

    }
}
