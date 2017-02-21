<?php
/**
 * @category    MT
 * @package     MT_Filter
 * @copyright   Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_Filter_Helper_Data extends Mage_Core_Helper_Abstract{
    public function getConfig($path=null){
        if ($path) return Mage::getStoreConfig($path);
        else{
            $module = Mage::app()->getRequest()->getModuleName();
            if($module != 'catalog' && $module != 'catalogsearch') $enableFilter =  true;
            $bar    = $this->getConfig('mtfilter/general/bar');
            if($module != 'catalog' && $module != 'catalogsearch') $bar = false;
            return Mage::helper('core')->jsonEncode(
                array(
                    'mainDOM'   => trim($this->getConfig("mtfilter/{$module}/main_selector")),
                    'layerDOM'  => trim($this->getConfig("mtfilter/{$module}/layer_selector")),
                    'enable'    => $enableFilter ? $enableFilter : (bool)$this->getConfig("mtfilter/{$module}/enable"),
                    'bar'       => (bool)$bar
                )
            );
        }
    }

    public function isPriceEnable(){
        $module = Mage::app()->getRequest()->getModuleName();
        if($module != 'catalog' && $module != 'catalogsearch') return true;
        return $this->getConfig("mtfilter/{$module}/price");
    }
}
