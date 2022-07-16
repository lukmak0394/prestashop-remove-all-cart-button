<?php


class RemoveAllCartButtonAjaxModuleFrontController extends ModuleFrontController {

    public function initContent() {

        parent::initContent();

        $action = Tools::getValue('action');

        $this->ajax = true;

        switch($action) {
            case 'remove':
                $this->removeAll();
                Tools::redirect('index.php?controller=cart&action=show');
                break;
            case 'cancel':
                Tools::redirect('index.php?controller=cart&action=show');
                break;
        }
        
    }

    public function displayAjax() {

        $modal = array('modal' => $this->module->displayModal());

        exit(json_encode($modal));
        
    }

    public function removeAll(){
        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->deleteProduct($product["id_product"],$product["id_product_attribute"],$product['id_customization']);
        }
     
    }

}