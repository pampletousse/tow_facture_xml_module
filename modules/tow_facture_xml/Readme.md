# Tow Facture XML Module

## Fonction

Génére des factures XML UBL 2.1 PEPPOL à partir des commandes.

## Utilisation

Cliquez sur une commande à partir de la page commandes de l'administration génère une facture automatiquement placée dans le répertoire invoices du module.

## Exemple de facture générée

```
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
  <cbc:CustomizationID>urn:cen.eu:en16931:2017</cbc:CustomizationID>
  <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
  <cbc:ID>INV-2</cbc:ID>
  <cbc:IssueDate>2025-06-01</cbc:IssueDate>
  <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
  <cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
  <cac:AccountingSupplierParty>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name>Tow Facture XML</cbc:Name>
      </cac:PartyName>
    </cac:Party>
  </cac:AccountingSupplierParty>
  <cac:AccountingCustomerParty>
    <cac:Party>
      <cac:PartyName>
        <cbc:Name>John DOE</cbc:Name>
      </cac:PartyName>
    </cac:Party>
  </cac:AccountingCustomerParty>
  <cac:InvoiceLine>
    <cbc:ID>1</cbc:ID>
    <cbc:InvoicedQuantity>2</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount>158.00</cbc:LineExtensionAmount>
    <cac:Item>
      <cbc:Name>The adventure begins Framed poster - Size : 80x120cm</cbc:Name>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount>79.00</cbc:PriceAmount>
    </cac:Price>
  </cac:InvoiceLine>
  <cac:InvoiceLine>
    <cbc:ID>2</cbc:ID>
    <cbc:InvoicedQuantity>1</cbc:InvoicedQuantity>
    <cbc:LineExtensionAmount>11.90</cbc:LineExtensionAmount>
    <cac:Item>
      <cbc:Name>Mug Today is a good day</cbc:Name>
    </cac:Item>
    <cac:Price>
      <cbc:PriceAmount>11.90</cbc:PriceAmount>
    </cac:Price>
  </cac:InvoiceLine>
  <cac:TaxTotal>
    <cbc:TaxAmount>0.00</cbc:TaxAmount>
  </cac:TaxTotal>
  <cac:LegalMonetaryTotal>
    <cbc:LineExtensionAmount>169.90</cbc:LineExtensionAmount>
    <cbc:TaxExclusiveAmount>169.90</cbc:TaxExclusiveAmount>
    <cbc:TaxInclusiveAmount>169.90</cbc:TaxInclusiveAmount>
    <cbc:PayableAmount>169.90</cbc:PayableAmount>
  </cac:LegalMonetaryTotal>
</Invoice>

```
