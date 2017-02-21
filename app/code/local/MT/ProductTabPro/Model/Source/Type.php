<?php
/**
 * @category    MT
 * @package     MT_ProductTabPro
 * Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

class MT_ProductTabPro_Model_Source_Type{
    public function toOptionArray(){
        $types = array(
            array('value' => 'product', 'label' => Mage::helper('producttabpro')->__('Product')),
            array('value' => 'block', 'label' => Mage::helper('producttabpro')->__('Block'))
        );

        return $types;
    }
}
