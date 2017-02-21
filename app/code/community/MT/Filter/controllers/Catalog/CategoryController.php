<?php
/**
 * @category    MT
 * @package     MT_Filter
 * @copyright   Copyright (C) 2008-2015 ZooExtension.com. All Rights Reserved.
 * @license     GNU General Public License version 2 or later
 * @author ZooExtension.com
 * @email       magento@cleversoft.co
 */

include_once 'Mage/Catalog/controllers/CategoryController.php';
class MT_Filter_Catalog_CategoryController extends Mage_Catalog_CategoryController{
    /**
     * Category view action
     * @version 1.6.2.0, 1.7.0.2
     */
    public function viewAction(){
        if ($category = $this->_initCatagory()) {
            $design = Mage::getSingleton('catalog/design');
            $settings = $design->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }

            Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');

            if (!$category->hasChildren()) {
                $update->addHandle('catalog_category_layered_nochildren');
            }

            $this->addActionLayoutHandles();
            $update->addHandle($category->getLayoutUpdateHandle());
            $update->addHandle('CATEGORY_' . $category->getId());
            $this->loadLayoutUpdates();

            // apply custom layout update once layout is loaded
            if ($layoutUpdates = $settings->getLayoutUpdates()) {
                if (is_array($layoutUpdates)) {
                    foreach($layoutUpdates as $layoutUpdate) {
                        $update->addUpdate($layoutUpdate);
                    }
                }
            }

            $this->generateLayoutXml()->generateLayoutBlocks();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $this->getLayout()->helper('page/layout')->applyTemplate($settings->getPageLayout());
            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('categorypath-' . $category->getUrlPath())
                    ->addBodyClass('category-' . $category->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');

            if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('isAjax') == true){

                if($this->getRequest()->getParam('widget')){

                    $block = $this->getLayout()->getBlock('product_list');

                    $filters = $this->getLayout()->getBlock('catalog.leftnav')->getFilters();
                    $filterHtml = $this->_renderFilterCollection($filters, $this->getRequest()->getParam('limit'), $this->getRequest()->getParam('num_loadmore'),
                        $this->getRequest()->getParam('offset'), $this->getRequest()->getParam('href'), $this->getRequest()->getParam('column'));



                    $block->setData(array(
                        'row'           => $this->getRequest()->getParam('row'),
                        'column'        => $this->getRequest()->getParam('column'),
                        'show_loadmore'  => $this->getRequest()->getParam('show_loadmore'),
                        'num_loadmore'  => $this->getRequest()->getParam('num_loadmore'),
                        'type'          => 'collection',
                        'limit'         => (int)$this->getRequest()->getParam('limit'),
                        'offset'         => $this->getRequest()->getParam('offset'),
                        'href'           => $this->getRequest()->getParam('href'),
                        'carousel'      => $this->getRequest()->getParam('carousel')
                    ));
                    $block->setTemplate('mt/widget/collection/collection_filter.phtml');
                    $count = $block->getLoadedProductCollection()->count();
                    if($this->getRequest()->getParam('offset') < 1){
                        $offset = $this->getRequest()->getParam('limit');
                        if($count < $offset) $count = 0;
                    }
                    else $offset = $this->getRequest()->getParam('offset') + $this->getRequest()->getParam('num_loadmore');
                    $output['data'] = $block->toHtml();
                    $output['offset'] =  $offset;
                    $output['href'] =  $this->getRequest()->getParam('href');
                    $output['num_loadmore'] =  $this->getRequest()->getParam('num_loadmore');
                    $output['limit'] =  $this->getRequest()->getParam('limit');
                    $output['count'] =  $count;
                    $output['filter'] = $filterHtml;

                    $this->getResponse()->setBody( Mage::helper('core')->jsonEncode($output));
                }else{

                    $output['main'] = $this->getLayout()->getBlock('content')->toHtml();
                    foreach($this->getLayout()->getAllBlocks() as $block){
                        if ($block->getType() == 'catalog/layer_view'){
                            $output['layer'] = $block->toHtml();
                        }
                    }


                    $this->getResponse()->setHeader('Content-Type', 'application/json');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
                }
            }else $this->renderLayout();
        }elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }


    protected function _renderFilterCollection($filterBlock, $limit, $num_loadmore, $offset, $url, $column)
    {
        $filters = array();
        $layerBlock = $this->getLayout()->getBlock('catalog.leftnav')->getChild('layer_state');

        $activeStates = $layerBlock->getActiveFilters();
        $stateName = array();
        foreach($activeStates as $state){
            $stateName[] = $state->getName();
        }
        foreach ($filterBlock as $block) {
            if(strtolower($block->getName()) == 'category') continue;
            $block->setData(array(
                    'url'    => Mage::registry('current_category')->getUrl(),
                    'num_loadmore'    => $num_loadmore,
                    'offset'    => $offset,
                    'column'    => $column,
                    'limit'    => $limit
                ));
            $block->assign(array('states' => $activeStates));
            $block->setTemplate('mt/widget/detail_filter.phtml');

            $filters[] = $block;
        }
        $params = $_GET;
        $oldPars = '';
        $typeSort = '';
        foreach($params as $key => $pa){
            if($key == 'dir' || $key == 'order') { $typeSort .= $pa; continue; }
            $oldPars .= $key.'='.$pa.'&';
        }


        $block = $this->getLayout()->createBlock('mtwidget/widget_collection');

        $block->setData(array(
            'url'    => Mage::registry('current_category')->getUrl().'?'.$oldPars,
            'typeSoft' => $typeSort,
            'num_loadmore'    => $num_loadmore,
            'offset'    => 0,
            'column'    => $column,
            'limit'    => $limit,
            'stateName' => $stateName,
            'filters'    => $filters
        ));
        $block->setTemplate('mt/widget/filter.phtml');

        return $block->toHtml();
    }
}
