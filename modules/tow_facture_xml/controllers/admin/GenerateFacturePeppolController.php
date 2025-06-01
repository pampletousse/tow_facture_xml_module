<?php

class Tow_Facture_XmlGenerateFacturePeppolController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->context = Context::getContext();
    }

    public function initContent()
    {
        parent::initContent();
        $orderId = (int) Tools::getValue('id_order');
        echo 'Génération de la facture PEPPOL pour la commande #' . $orderId;
        exit;
    }
}