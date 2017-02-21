<?php
include_once 'AW/Blog/controllers/IndexController.php';
class MT_BlogExt_IndexController extends AW_Blog_IndexController
{
    public function listAction()
    {
        $this->loadLayout();

        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getParam('isAjax') == true){
            $output['main'] = $this->getLayout()->getBlock('content')->toHtml();
            foreach($this->getLayout()->getAllBlocks() as $block){
                if ($block->getType() == 'catalog/layer_view'){
                    $output['layer'] = $block->toHtml();
                }
            }
            $this->getResponse()->setHeader('Content-Type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($output));
        } else {


            $this->getLayout()->getBlock('root')->setTemplate(Mage::helper('blog')->getLayout());
            $this->renderLayout();
        }
    }
}