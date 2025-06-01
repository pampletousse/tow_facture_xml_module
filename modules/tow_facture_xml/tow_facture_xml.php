<?php
/**
* 2007-2025 PrestaShop
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
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tow_facture_xml extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tow_facture_xml';
        $this->tab = 'administration';
        $this->version = '0.0.1';
        $this->author = 'Vysuel';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Tow Facture XML Module');
        $this->description = $this->l('Génère une facture XML UBL 2.1');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TOW_FACTURE_XML_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionOrderDetail') &&
            $this->registerHook('displayAdminOrderMainBottom') &&
            $this->registerHook('displayAdminOrderTop') &&
            $this->registerHook('actionGetAdminOrderButtons') &&
            $this->installTab();
    }

    public function uninstall()
    {
        Configuration::deleteByName('TOW_FACTURE_XML_LIVE_MODE');

        return parent::uninstall() && $this->uninstallTab();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTow_facture_xmlModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
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
        $helper->submit_action = 'submitTow_facture_xmlModule';
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
                        'name' => 'TOW_FACTURE_XML_LIVE_MODE',
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
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'TOW_FACTURE_XML_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'TOW_FACTURE_XML_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
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
            'TOW_FACTURE_XML_LIVE_MODE' => Configuration::get('TOW_FACTURE_XML_LIVE_MODE', true),
            'TOW_FACTURE_XML_ACCOUNT_EMAIL' => Configuration::get('TOW_FACTURE_XML_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'TOW_FACTURE_XML_ACCOUNT_PASSWORD' => Configuration::get('TOW_FACTURE_XML_ACCOUNT_PASSWORD', null),
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
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
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
    }

    public function hookActionOrderDetail()
    {
        /* Place your code here. */
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'Tow_Facture_XmlGenerateFacturePeppol';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->active = 1;
        $tab->name = [];

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'Facture Peppol (hidden)';
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        return $tab->add();
    }

    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('Tow_Facture_XmlGenerateFacturePeppol');
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }

    public function hookActionGetAdminOrderButtons(array $params)
    {
        $idOrder = (int) Tools::getValue('id_order');
        $this->generateFactureXml($idOrder);
    }

    public function generateFactureXml(int $idOrder)
    {
        // Prestashop 
        $idOrder = (int) Tools::getValue('id_order');
        $order = new Order($idOrder);

        if (!Validate::isLoadedObject($order)) {
            die('Commande introuvable.');
        }

        $customer = new Customer($order->id_customer);
        $currency = new Currency($order->id_currency);
        $products = $order->getProducts();
        $invoiceNumber = 'INV-' . $order->id;
        $issueDate = date('Y-m-d');
        $currencyCode = $currency->iso_code;

        $totalHt = $order->total_products + $order->total_shipping_tax_excl;
        $totalTtc = $order->total_paid;
        $totalTax = $totalTtc - $totalHt;

        // Création XML
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $invoice = $xml->createElement('Invoice');
        $invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->appendChild($invoice);

        $invoice->appendChild($xml->createElement('cbc:CustomizationID', 'urn:cen.eu:en16931:2017'));
        $invoice->appendChild($xml->createElement('cbc:ProfileID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0'));
        $invoice->appendChild($xml->createElement('cbc:ID', $invoiceNumber));
        $invoice->appendChild($xml->createElement('cbc:IssueDate', $issueDate));
        $invoice->appendChild($xml->createElement('cbc:InvoiceTypeCode', '380'));
        $invoice->appendChild($xml->createElement('cbc:DocumentCurrencyCode', $currencyCode));

        // Vendeur
        $supplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $partySupplier = $xml->createElement('cac:Party');
        $partyNameSupplier = $xml->createElement('cac:PartyName');
        $partyNameSupplier->appendChild($xml->createElement('cbc:Name', htmlspecialchars(Configuration::get('PS_SHOP_NAME'))));
        $partySupplier->appendChild($partyNameSupplier);
        $supplierParty->appendChild($partySupplier);
        $invoice->appendChild($supplierParty);

        // Acheteur
        $customerParty = $xml->createElement('cac:AccountingCustomerParty');
        $partyCustomer = $xml->createElement('cac:Party');
        $partyNameCustomer = $xml->createElement('cac:PartyName');
        $partyNameCustomer->appendChild($xml->createElement('cbc:Name', htmlspecialchars($customer->firstname . ' ' . $customer->lastname)));
        $partyCustomer->appendChild($partyNameCustomer);
        $customerParty->appendChild($partyCustomer);
        $invoice->appendChild($customerParty);

        // Produits
        $lineId = 1;
        foreach ($products as $product) {
            $invoiceLine = $xml->createElement('cac:InvoiceLine');
            $invoiceLine->appendChild($xml->createElement('cbc:ID', $lineId++));
            $invoiceLine->appendChild($xml->createElement('cbc:InvoicedQuantity', $product['product_quantity']));
            $invoiceLine->appendChild($xml->createElement('cbc:LineExtensionAmount', number_format($product['total_price_tax_excl'], 2, '.', '')));

            $item = $xml->createElement('cac:Item');
            $item->appendChild($xml->createElement('cbc:Name', htmlspecialchars($product['product_name'])));
            $invoiceLine->appendChild($item);

            $price = $xml->createElement('cac:Price');
            $price->appendChild($xml->createElement('cbc:PriceAmount', number_format($product['unit_price_tax_excl'], 2, '.', '')));
            $invoiceLine->appendChild($price);

            $invoice->appendChild($invoiceLine);
        }

        // Frais de livraison
        if ((float)$order->total_shipping_tax_excl > 0) {
            $invoiceLine = $xml->createElement('cac:InvoiceLine');
            $invoiceLine->appendChild($xml->createElement('cbc:ID', $lineId++));
            $invoiceLine->appendChild($xml->createElement('cbc:InvoicedQuantity', 1));
            $invoiceLine->appendChild($xml->createElement('cbc:LineExtensionAmount', number_format($order->total_shipping_tax_excl, 2, '.', '')));

            $item = $xml->createElement('cac:Item');
            $item->appendChild($xml->createElement('cbc:Name', 'Frais de livraison'));
            $invoiceLine->appendChild($item);

            $price = $xml->createElement('cac:Price');
            $price->appendChild($xml->createElement('cbc:PriceAmount', number_format($order->total_shipping_tax_excl, 2, '.', '')));
            $invoiceLine->appendChild($price);

            $invoice->appendChild($invoiceLine);
        }

        // Taxes
        $taxTotal = $xml->createElement('cac:TaxTotal');
        $taxTotal->appendChild($xml->createElement('cbc:TaxAmount', number_format($totalTax, 2, '.', '')));
        $invoice->appendChild($taxTotal);

        // Totaux
        $monetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');
        $monetaryTotal->appendChild($xml->createElement('cbc:LineExtensionAmount', number_format($totalHt, 2, '.', '')));
        $monetaryTotal->appendChild($xml->createElement('cbc:TaxExclusiveAmount', number_format($totalHt, 2, '.', '')));
        $monetaryTotal->appendChild($xml->createElement('cbc:TaxInclusiveAmount', number_format($totalTtc, 2, '.', '')));
        $monetaryTotal->appendChild($xml->createElement('cbc:PayableAmount', number_format($totalTtc, 2, '.', '')));
        $invoice->appendChild($monetaryTotal);

        // Enregistrement
        $filename = 'generated_invoice_' . $order->id . '.xml';
        $moduleDir = _PS_MODULE_DIR_ . 'tow_facture_xml/invoices/';
        if (!is_dir($moduleDir)) {
            mkdir($moduleDir, 0755, true);
        }
        $xml->save($moduleDir . $filename);
    }

    public function hookDisplayAdminOrderTop($params)
    {
        /*$orderId = (int)$params['id_order'];

        $this->context->smarty->assign([
            'order_id' => $orderId,
            'custom_link' => $this->context->link->getModuleLink(
                $this->name,
                'customaction',
                ['id_order' => $orderId]
            )
        ]);
        return $this->display(__FILE__, 'views/templates/hook/custom_button.tpl');*/
    }

    public function hookDisplayAdminOrderMainBottom($params)
    {
        /*$orderId = (int)$params['id_order'];

        $this->context->smarty->assign([
            'order_id' => $orderId,
            'custom_link' => $this->context->link->getModuleLink(
                $this->name,
                'customaction',
                ['id_order' => $orderId]
            )
        ]);
        return $this->display(__FILE__, 'views/templates/hook/custom_button.tpl');*/
    }
}
