<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class RemoveAllCartButton extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'removeAllCartButton';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Łukasz Makowski';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Remove all products from cart');
        $this->description = $this->l('Module adding button to cart which allows to remove all products after clicking');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('REMOVEALLCARTBUTTON_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') && 
            $this->registerHook('displayShoppingCartFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('REMOVEALLCARTBUTTON_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitRemoveAllCartButtonModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRemoveAllCartButtonModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'REMOVEALLCARTBUTTON_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Remove All Button Text',
                        'name' => 'REMOVEALLCARTBUTTON_REMOVE_ALL_BTN_TXT' ,
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Modal header text',
                        'name' => 'REMOVEALLCARTBUTTON_MODAL_HEADER_TXT' ,
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Modal - confirm button text',
                        'name' => 'REMOVEALLCARTBUTTON_MODAL_CONFIRM_TXT'
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Modal - cancel button text',
                        'name' => 'REMOVEALLCARTBUTTON_MODAL_CANCEL_TXT'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'REMOVEALLCARTBUTTON_LIVE_MODE' => Configuration::get('REMOVEALLCARTBUTTON_LIVE_MODE', true),
            'REMOVEALLCARTBUTTON_REMOVE_ALL_BTN_TXT' => Configuration::get('REMOVEALLCARTBUTTON_REMOVE_ALL_BTN_TXT', true),
            'REMOVEALLCARTBUTTON_MODAL_HEADER_TXT' => Configuration::get('REMOVEALLCARTBUTTON_MODAL_HEADER_TXT', true),
            'REMOVEALLCARTBUTTON_MODAL_CONFIRM_TXT' => Configuration::get('REMOVEALLCARTBUTTON_MODAL_CONFIRM_TXT', true),
            'REMOVEALLCARTBUTTON_MODAL_CANCEL_TXT' => Configuration::get('REMOVEALLCARTBUTTON_MODAL_CANCEL_TXT', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');

        $link = new Link;
        $controller_link = $link->getModuleLink('removeAllCartButton','ajax');
        Media::addJsDef(array(
            "controller_link" => $controller_link
        ));

    }

    public function hookDisplayShoppingCartFooter() {

        $config = $this->getConfigFormValues();

        $removeAllButtonText = null;

        $config['REMOVEALLCARTBUTTON_REMOVE_ALL_BTN_TXT'] ? $removeAllButtonText = $config['REMOVEALLCARTBUTTON_REMOVE_ALL_BTN_TXT'] : $removeAllButtonText = 'Remove All';

        $this->context->smarty->assign(
            [
                'controller_link' => $controller_link,
                'button_text' => $removeAllButtonText
            ]
        );
        
        if(count($this->context->cart->getProducts()) > 0) {
            return $this->display(__FILE__,'removeAllBtn.tpl');
        }
        
    }

    public function displayModal() {

        $link = new Link;
        $controller_link = $link->getModuleLink('removeAllCartButton','ajax');

        $config = $this->getConfigFormValues();

        $modalHeaderText = null;
        $modalConfirmBtnText = null;
        $modalCancelBtnText = null;

        $config['REMOVEALLCARTBUTTON_MODAL_HEADER_TXT'] ? $modalHeaderText = $config['REMOVEALLCARTBUTTON_MODAL_HEADER_TXT'] : $modalHeaderText = 'Are you sure that you want to delete all products from cart?';
        $config['REMOVEALLCARTBUTTON_MODAL_CONFIRM_TXT'] ? $modalConfirmBtnText = $config['REMOVEALLCARTBUTTON_MODAL_CONFIRM_TXT'] : $modalConfirmBtnText = 'Yes, please';
        $config['REMOVEALLCARTBUTTON_MODAL_CANCEL_TXT'] ? $modalCancelBtnText = $config['REMOVEALLCARTBUTTON_MODAL_CANCEL_TXT'] : $modalCancelBtnText = 'No, thanks';

        $this->context->smarty->assign(
            [
                'controller_link' => $controller_link,
                'modal_header_text' => $modalHeaderText,
                'confirm_button_text' => $modalConfirmBtnText,
                'cancel_button_text' => $modalCancelBtnText,
            ]
        );

        return $this->display(__FILE__,'modal.tpl');
        
    }


}
