<?php

/**
 * Class Styla_Connect_ProductController
 */
class Styla_Connect_ProductController extends Mage_Core_Controller_Front_Action
{
    protected $initialEnvironmentInfo;

    protected function setStore()
    {
        $currentStoreId = (int)Mage::app()->getStore()->getId();
        $targetStore    = Mage::app()->getRequest()->getParam('store');

        $store = Mage::app()->getStore($targetStore);

        if ($store && $currentStoreId !== (int)$store->getId()) {
            /** @var Mage_Core_Model_App_Emulation $appEmulation */
            $appEmulation                 = Mage::getSingleton('core/app_emulation');
            $this->initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation((int)$store->getId());
        }
    }

    protected function resetStore()
    {
        if ($this->initialEnvironmentInfo) {
            /** @var Mage_Core_Model_App_Emulation $appEmulation */
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $appEmulation->stopEnvironmentEmulation($this->initialEnvironmentInfo);
        }
    }


    /**
     * Load the product's details and return them as a json
     * This is used in ajax calls from the magazine.
     */
    public function infoAction()
    {
        try {
            $this->setStore();

            $product = $this->_initProduct();

            $productInfo = $this->_getProductInfo();
            $productInfo->setProduct($product);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($productInfo->getInfo()));

            $this->resetStore();
        } catch (Styla_Connect_Exception $e) {
            //on known and recoverable exceptions, we're trying to generate a meaningful error response
            $body = array("error" => $this->__($e->getMessage()), "saleable" => false);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode($body));

            return;

        } catch (Exception $e) {
            Mage::logException($e);

            $this->getResponse()->setHeader('HTTP/1.0', '500', true);

            return;
        }
    }

    /**
     * @return bool|Mage_Catalog_Model_Product
     * @throws Exception
     * @throws Styla_Connect_Exception
     */
    protected function _initProduct()
    {
        $productSku = $this->getRequest()->getParam('sku');
        if (!$productSku) {
            throw new Exception("Missing SKU.");
        }

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
        if (!$product || ($product && !$product->getId())) {
            throw new Styla_Connect_Exception("The product was not found.");
        }

        if (!$product->isSaleable()) {
            throw new Styla_Connect_Exception("Product is unavailable.");
        }

        return $product;
    }

    /**
     *
     * @return Styla_Connect_Model_Product_Info
     */
    protected function _getProductInfo()
    {
        return Mage::getSingleton('styla_connect/product_info');
    }
}
