<?php


class RemoveAllCartButtonAjaxModuleFrontController extends ModuleFrontController {

    public function initContent() {

        $this->removeAll();

        Tools::redirect('index.php?controller=cart&action=show');

    }

    
    public function removeAll(){
        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->deleteProduct($product["id_product"],$product["id_product_attribute"]);
        }
    }

}