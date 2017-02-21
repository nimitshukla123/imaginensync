<?php
/**
 * @category    MT
 * @package     MT_ProductionTabPro
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_ProductTabPro_Model_Source_Tab_Mode{
    public function toOptionArray(){
        return array(
            array('value' => 'latest', 'label' => Mage::helper('producttabpro')->__('Latest Products')),
            array('value' => 'new', 'label' => Mage::helper('producttabpro')->__('New Products')),
            array('value' => 'bestsell', 'label' => Mage::helper('producttabpro')->__('Best Sell Products')),
            array('value' => 'mostviewed', 'label' => Mage::helper('producttabpro')->__('Most Viewed Products')),
            //array('value' => 'specificed', 'label' => Mage::helper('productiontabpro')->__('Specified Products')),
            array('value' => 'random', 'label' => Mage::helper('producttabpro')->__('Random Products')),
            //array('value' => 'related', 'label' => Mage::helper('productiontabpro')->__('Related Products')),
            //array('value' => 'up', 'label' => Mage::helper('productiontabpro')->__('Up-sell Products')),
            //array('value' => 'cross', 'label' => Mage::helper('productiontabpro')->__('Cross-sell Products')),
            array('value' => 'discount', 'label' => Mage::helper('producttabpro')->__('Discount Products')),
            array('value' => 'rating', 'label' => Mage::helper('producttabpro')->__('Top Rated Products'))
        );
    }
}
