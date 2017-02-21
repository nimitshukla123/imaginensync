<?php 
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Regular License.
 * You may not use any part of the code in whole or part in any other software
 * or product or website.
 *
 * @author		ZooExtension.com
 * @license		Regular License http://themeforest.net/licenses/regular
 */
class MagenThemes_Dataporter_Adminhtml_CfgporterController extends Mage_Adminhtml_Controller_Action{
    protected $_helper;

    protected $_hc;

    protected $_event;

    protected function _construct() {
        $this->_helper= Mage::helper("dataporter");
        $this->_hc = Mage::helper("dataporter/cfgporter_data");
        $this->_event= "dataporter_cfgporter";
    }

    public function indexAction() {
        $actionType = $this->getRequest()->getParam("action_type");
        if ($actionType === "export"){ $blockType = $this->getLayout()->createBlock("dataporter/adminhtml_cfgporter_export_edit");}
        elseif ($actionType === "import"){ $blockType = $this->getLayout()->createBlock("dataporter/adminhtml_cfgporter_import_edit");}
        elseif ($actionType === NULL){
            $this->getResponse()->setRedirect($this->getUrl("adminhtml/dashboard"));
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('magenthemes');
        $this->_addBreadcrumb($this->_helper->__("Config Import and Export"), $this->_helper->__("Config Import and Export"));
        $this->_addContent($blockType);
        $this->renderLayout();
    }

    public function exportAction() {
        $this->loadLayout();
        $modules = $this->exportModule();
        if (!empty($modules)){
                try {
                $filePath = $this->getFilePath();
                $this->checkFilePermission($filePath);
                $store_id = $this->getRequest()->getParam("stores");
                if (is_array($store_id)){
                    throw new Exception("Website/Store ID retrieved as array. Expected string.");
                }
                $this->exportConfigFile($modules, "default", $store_id, $filePath);
                Mage::getSingleton("adminhtml/session")->addSuccess( $this->_helper->__("Successfully exported to file %s", $filePath));
            } catch (Exception $e) {
                    Mage::logException($e);Mage::getSingleton("adminhtml/session")->addError( $this->_helper->__("An error occurred during export to file %s", $filePath) . "<br/>" . $this->_helper->__("Exception: %s", $e->getMessage()));
                }
        }else{
            Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred: no source module selected for export.") );
        }
        $this->renderLayout();
        $this->getResponse()->setRedirect($this->getUrl("*/*/", $this->returnUrlParams()));
    }

    protected function getFilePath() {
        return $this->_hc->getPresetFilepath( $this->getRequest()->getParam("preset_name"), $this->getRequest()->getParam("package"));
    }

    protected function checkFilePermission($filePath) {
        $permission = 0777;
        $dir = dirname($filePath);
        if (is_dir($dir)){
            if (!is_writable($dir)) {
                chmod($dir, $permission); }
        }else {
            if (!mkdir($dir, $permission, true)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    protected function exportModule() {
        $modules = $this->getRequest()->getParam("modules");
        $exportModule = array();
        if (!empty($modules)){
            foreach ($modules as $module) {
                if (!empty($module)){
                    $exportModule[] = $module;
                }
            }
        }
        return $exportModule;
    }

    public function importAction() {
        $this->loadLayout();
        $fileData = $this->getFileData();
        if (file_exists($fileData)){
            try {
                $store = $this->getRequest()->getParam("stores");
                $storeData = Mage::getSingleton("mtfastion/config_scope")->decodeScope($store);
                $this->saveConfig($storeData["scope"], $storeData["scopeId"], $fileData);
                Mage::getSingleton("adminhtml/session")->addSuccess( $this->_helper->__("Successfully imported from file %s", $fileData));
                $eventData = array("portScope" => $storeData["scope"], "portScopeId" => $storeData["scopeId"]);
                Mage::dispatchEvent($this->_event . "_import_after", $eventData);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton("adminhtml/session")->addError( $this->_helper->__("An error occurred during import from file %s", $fileData) . "<br/>" . $this->_helper->__("Exception: %s", $e->getMessage()));
            }
        }else{
            Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred: unable to read file %s", $fileData) );
        }
        $this->renderLayout();$this->getResponse()->setRedirect($this->getUrl("*/*/", $this->returnUrlParams()));
    }

    protected function getFileData() {
        $filePath = $this->_helper->getTmpFileBaseDir();
        $fileImporterData = '';
        if (!empty($_FILES["data_import_file"]["name"])) {
            if (file_exists($_FILES["data_import_file"]["tmp_name"])){
                try{
                    $fileUpload = new Varien_File_Uploader("data_import_file");
                    $fileUpload->setAllowedExtensions(array('xml'));
                    $fileUpload->setAllowCreateFolders(true);
                    $fileUpload->setAllowRenameFiles(false);
                    $fileUpload->setFilesDispersion(false);
                    $fileUpload->save($filePath, $_FILES["data_import_file"]["name"]);
                    $fileImporterData = $filePath . $_FILES["data_import_file"]["name"];
                } catch(Exception $e){
                    Mage::getSingleton("adminhtml/session")->addError($this->_helper->__("An error occurred during upload of file %s", $_FILES["data_import_file"]["name"]). "<br/>" .$this->_helper->__("Exception: %s", $e->getMessage()) );
                }
            }
        }else {
            $fileImporterData = $this->_hc->getPresetFilepath($this->getRequest()->getParam("preset_name"),$this->getRequest()->getParam("package") );
        }
        if (!empty($fileImporterData)){
            return $fileImporterData;
        }else{
            return '';
        }
    }

    protected function returnUrlParams() {
        $params = array();
        $params["action_type"] = $this->getRequest()->getParam("action_type");
        $params["package"] = $this->getRequest()->getParam("package");
        return $params;
    }

    protected function exportConfigFile($modules, $descendPath, $store, $filePath, $defaultConfig=TRUE, $removeNonExistConfig = true) {
        $xmlSchemaObject = $this->getXMLSchemaConfig($modules, $descendPath);
        $nonExistElements = array();
        foreach ($xmlSchemaObject->children() as $section){
            foreach ($section->children() as $group) {
                foreach ($group->children() as $field){
                    if ($field->hasChildren()) {
                        continue;
                    }
                    $configPath = $section->getName() . '/' . $group->getName() . '/' . $field->getName();
                    $configValue = Mage::getStoreConfig($configPath,$store );
                    if ($store > 0 && '' === $configValue) {
                        if ($defaultConfig){
                            $defaultConfigValue = Mage::getStoreConfig($configPath,0 );
                            $configValue = $defaultConfigValue;
                            if ($removeNonExistConfig) {
                                if ('' === $defaultConfigValue){
                                    $nonExistElements[] = $configPath;
                                    continue;
                                }
                            }
                        }
                    }
                    $group->{$field->getName()} = $configValue;
                }
            }
        }

        foreach ($nonExistElements as $elementPath){
            $element = $xmlSchemaObject->xpath($elementPath);
            unset($element[0][0]);
        }
        $fileContent = $xmlSchemaObject->asNiceXml();
        if (!file_put_contents($filePath, $fileContent)){
            throw new Exception("Unable to write file.");
        }
    }

    protected function getXMLSchemaConfig($modules, $descendPath) {
        $xmlSchemaObject = simplexml_load_string("<defaul></defaul>", "Varien_Simplexml_Element");
        foreach ($modules as $module){
            $descendContent = $this->getDescendContentXML($module, $descendPath);
            if ($descendContent && ($descendContent instanceof SimpleXMLElement)) {
                foreach ($descendContent->children() as $child){
                    $xmlSchemaObject->appendChild($child);
                }
            }
        }
        return $xmlSchemaObject;
    }

    protected function getDescendContentXML($module, $descendPath) {
        $configFile = Mage::getConfig()->getModuleDir("etc", $module) . DS . "config.xml";
        if (file_exists($configFile)){
            $sContent = file_get_contents($configFile);
            if ($sContent !== FALSE) {
                $simpleXMLElement = simplexml_load_string($sContent, "Varien_Simplexml_Element");
                $descendContent = $simpleXMLElement->descend($descendPath);
                return $descendContent;
            }
        }
        return NULL;
    }

    protected function saveConfig($scope, $scopeId, $fileImport) {
        $xmlObject = $this->readImportFile($fileImport);
        if (!$xmlObject){
            throw new Exception("Unable to read XML data from file - empty file or invalid format.");
        }
        foreach ($xmlObject->children() as $section){
            foreach ($section->children() as $group) {
                foreach ($group->children() as $field){
                    if ($field->hasChildren()) { continue; }
                    $value = (string) $field;
                    if ('' === $value) {
                        $value = NULL;
                    }
                    Mage::getConfig()->saveConfig($section->getName() . '/' . $group->getName() . '/' . $field->getName(),$value,$scope,$scopeId );
                }
            }
        }
    }

    protected function readImportFile($file) {
        $fileContent = file_get_contents($file);
        return simplexml_load_string($fileContent, "Varien_Simplexml_Element");
    }
}