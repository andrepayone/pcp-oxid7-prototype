<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Core;

use OxidEsales\Eshop\Core\Registry;
use PayoneCommercePlatform\Sdk\CommunicatorConfiguration;
use PayoneCommercePlatform\Sdk\ApiClient\CheckoutApiClient;
use PayoneCommercePlatform\Sdk\ApiClient\CommerceCaseApiClient;
use PayoneCommercePlatform\Sdk\ApiClient\OrderManagementCheckoutActionsApiClient;
use PayoneCommercePlatform\Sdk\ApiClient\PaymentExecutionApiClient;
use PayoneCommercePlatform\Sdk\Models\Address;
use PayoneCommercePlatform\Sdk\Models\AddressPersonal;
use PayoneCommercePlatform\Sdk\Models\AmountOfMoney;
use PayoneCommercePlatform\Sdk\Models\BankAccountInformation;
use PayoneCommercePlatform\Sdk\Models\BusinessRelation;
use PayoneCommercePlatform\Sdk\Models\CartItemInput;
use PayoneCommercePlatform\Sdk\Models\CartItemInvoiceData;
use PayoneCommercePlatform\Sdk\Models\CheckoutReferences;
use PayoneCommercePlatform\Sdk\Models\CompleteFinancingPaymentMethodSpecificInput;
use PayoneCommercePlatform\Sdk\Models\CompletePaymentRequest;
use PayoneCommercePlatform\Sdk\Models\ContactDetails;
use PayoneCommercePlatform\Sdk\Models\CreateCheckoutRequest;
use PayoneCommercePlatform\Sdk\Models\CreateCommerceCaseRequest;
use PayoneCommercePlatform\Sdk\Models\Customer;
use PayoneCommercePlatform\Sdk\Models\DeliverRequest;
use PayoneCommercePlatform\Sdk\Models\DeliverType;
use PayoneCommercePlatform\Sdk\Models\FinancingPaymentMethodSpecificInput;
use PayoneCommercePlatform\Sdk\Models\OrderLineDetailsInput;
use PayoneCommercePlatform\Sdk\Models\OrderRequest;
use PayoneCommercePlatform\Sdk\Models\OrderType;
use PayoneCommercePlatform\Sdk\Models\PaymentChannel;
use PayoneCommercePlatform\Sdk\Models\PaymentMethodSpecificInput;
use PayoneCommercePlatform\Sdk\Models\PaymentProduct3391SpecificInput;
use PayoneCommercePlatform\Sdk\Models\PaymentProduct3392SpecificInput;
use PayoneCommercePlatform\Sdk\Models\PersonalInformation;
use PayoneCommercePlatform\Sdk\Models\PersonalName;
use PayoneCommercePlatform\Sdk\Models\ProductType;
use PayoneCommercePlatform\Sdk\Models\References;
use PayoneCommercePlatform\Sdk\Models\Shipping;
use PayoneCommercePlatform\Sdk\Models\ShoppingCartInput;

class PayoneApiService
{
    protected CommunicatorConfiguration $config;
    protected CommerceCaseApiClient $commerceCaseClient;
    protected CheckoutApiClient $checkoutClient;
    protected OrderManagementCheckoutActionsApiClient $orderManagementClient;
    protected PaymentExecutionApiClient $paymentExecutionClient;
    protected string $merchantId;

    public function __construct()
    {
        $oConfig = Registry::getConfig();
        $this->merchantId = $oConfig->getConfigParam('pcpMerchantId');

        $this->config = new CommunicatorConfiguration(
            apiKey: $oConfig->getConfigParam('pcpApiKey'),
            apiSecret: $oConfig->getConfigParam('pcpApiSecret'),
            host: $oConfig->getConfigParam('pcpApiEndpoint'),
        );

        $this->commerceCaseClient = new CommerceCaseApiClient($this->config);
        $this->checkoutClient = new CheckoutApiClient($this->config);
        $this->orderManagementClient = new OrderManagementCheckoutActionsApiClient($this->config);
        $this->paymentExecutionClient = new PaymentExecutionApiClient($this->config);
    }

    public function sendRequestAuthorization($oOrder, $oUser, array $aDynvalue)
    {
        $oSession = Registry::getSession();
        $sPaymentId = $oSession->getVariable('paymentid');

        if ($sPaymentId === 'pcpsecureinstallment') {
            return $this->completeInstallmentPayment($oUser);
        }

        $sMerchantReference = $this->generateReference('dm');
        $blAutoExecute = ($sPaymentId !== 'pcppayinstore');

        $request = new CreateCommerceCaseRequest(
            merchantReference: $sMerchantReference,
            customer: $this->buildCustomerFromOrder($oOrder, $oUser),
            checkout: new CreateCheckoutRequest(
                references: new CheckoutReferences(merchantReference: $this->generateReference('ck')),
                amountOfMoney: $this->buildAmountOfMoney(),
                shipping: $this->buildShippingFromOrder($oOrder),
                shoppingCart: $this->buildShoppingCartFromOrder($oOrder),
                orderRequest: $blAutoExecute
                    ? new OrderRequest(
                        orderReferences: new References(merchantReference: $this->generateReference('or')),
                        orderType: OrderType::FULL,
                        paymentMethodSpecificInput: $this->buildPaymentMethodSpecificInput($oUser, $sPaymentId),
                    )
                    : null,
                autoExecuteOrder: $blAutoExecute,
            ),
        );

        $response = $this->commerceCaseClient->createCommerceCase($this->merchantId, $request);

        $oSession->setVariable('pcpCommerceCaseId', $response->getCommerceCaseId());
        $oSession->setVariable('pcpCheckoutId', $response->getCheckout()->getCheckoutId());
        $oSession->setVariable('pcpMerchantReference', $sMerchantReference);

        return $response;
    }

    public function getInstallmentOptions($oUser)
    {
        $oSession = Registry::getSession();
        $oBasket = $oSession->getBasket();

        $commerceCase = $this->commerceCaseClient->createCommerceCase(
            $this->merchantId,
            new CreateCommerceCaseRequest(
                merchantReference: $this->generateReference('dm'),
                customer: $this->buildCustomerFromUser($oUser),
                checkout: new CreateCheckoutRequest(
                    references: new CheckoutReferences(merchantReference: $this->generateReference('ck')),
                    amountOfMoney: $this->buildAmountOfMoney(),
                    shipping: $this->buildShippingFromUser($oUser),
                    shoppingCart: $this->buildShoppingCartFromBasket($oBasket),
                ),
            ),
        );

        $sCommerceCaseId = $commerceCase->getCommerceCaseId();
        $sCheckoutId = $commerceCase->getCheckout()->getCheckoutId();

        $oSession->setVariable('bnplInstallmentCommerceCaseId', $sCommerceCaseId);
        $oSession->setVariable('bnplInstallmentCheckoutId', $sCheckoutId);

        $orderResponse = $this->orderManagementClient->createOrder(
            $this->merchantId,
            $sCommerceCaseId,
            $sCheckoutId,
            new OrderRequest(
                orderReferences: new References(merchantReference: $this->generateReference('or')),
                orderType: OrderType::FULL,
                paymentMethodSpecificInput: new PaymentMethodSpecificInput(
                    financingPaymentMethodSpecificInput: new FinancingPaymentMethodSpecificInput(
                        paymentProductId: 3391,
                    ),
                    paymentChannel: PaymentChannel::ECOMMERCE,
                ),
            ),
        );

        if (
            $orderResponse->getCreatePaymentResponse() !== null
            && $orderResponse->getCreatePaymentResponse()->getPaymentExecutionId() !== null
        ) {
            $oSession->setVariable(
                'bnplInstallmentPaymentExecutionId',
                $orderResponse->getCreatePaymentResponse()->getPaymentExecutionId()
            );
        }

        return $orderResponse;
    }

    public function deleteTemporaryInstallmentCheckout(): void
    {
        $oSession = Registry::getSession();
        $sCommerceCaseId = $oSession->getVariable('bnplInstallmentCommerceCaseId');
        $sCheckoutId = $oSession->getVariable('bnplInstallmentCheckoutId');

        if (!$sCommerceCaseId || !$sCheckoutId) {
            return;
        }

        $this->checkoutClient->deleteCheckout($this->merchantId, $sCommerceCaseId, $sCheckoutId);
    }

    public function getCheckoutDetails(string $sCommerceCaseId, string $sCheckoutId)
    {
        return $this->checkoutClient->getCheckout($this->merchantId, $sCommerceCaseId, $sCheckoutId);
    }

    public function captureOrder(string $sCommerceCaseId, string $sCheckoutId)
    {
        return $this->orderManagementClient->deliverOrder(
            $this->merchantId,
            $sCommerceCaseId,
            $sCheckoutId,
            new DeliverRequest(deliverType: DeliverType::FULL, isFinal: true),
        );
    }

    protected function completeInstallmentPayment($oUser)
    {
        $oSession = Registry::getSession();

        return $this->paymentExecutionClient->completePayment(
            $this->merchantId,
            $oSession->getVariable('bnplInstallmentCommerceCaseId'),
            $oSession->getVariable('bnplInstallmentCheckoutId'),
            $oSession->getVariable('bnplInstallmentPaymentExecutionId'),
            new CompletePaymentRequest(
                financingPaymentMethodSpecificInput: new CompleteFinancingPaymentMethodSpecificInput(
                    paymentProductId: 3391,
                    requiresApproval: true,
                    paymentProduct3391SpecificInput: new PaymentProduct3391SpecificInput(
                        installmentOptionId: $oSession->getVariable('pcp_secinstallment_plan'),
                        bankAccountInformation: new BankAccountInformation(
                            iban: 'DE52940594210000082271',
                            bic: null,
                            accountHolder: $oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value,
                        ),
                    ),
                ),
            ),
        );
    }

    protected function buildCustomerFromOrder($oOrder, $oUser): Customer
    {
        $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $oCountry->load($oOrder->oxorder__oxbillcountryid->value);

        return new Customer(
            billingAddress: new Address(
                city: $oOrder->oxorder__oxbillcity->value ?: 'Düsseldorf',
                countryCode: $oCountry->oxcountry__oxisoalpha2->value,
                houseNumber: $oOrder->oxorder__oxbillstreetnr->value ?: '9',
                street: $oOrder->oxorder__oxbillstreet->value ?: 'Am Staad',
                zip: $oOrder->oxorder__oxbillzip->value ?: '40474',
            ),
            contactDetails: new ContactDetails(
                emailAddress: $oUser->oxuser__oxusername->value,
            ),
            businessRelation: BusinessRelation::B2C,
            locale: Registry::getLang()->getLanguageAbbr(),
            personalInformation: new PersonalInformation(
                dateOfBirth: '19851104',
                name: new PersonalName(
                    firstName: $oOrder->oxorder__oxbillfname->value,
                    surname: $oOrder->oxorder__oxbilllname->value,
                ),
            ),
        );
    }

    protected function buildCustomerFromUser($oUser): Customer
    {
        $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $oCountry->load($oUser->oxuser__oxcountryid->value);

        return new Customer(
            billingAddress: new Address(
                city: $oUser->oxuser__oxcity->value ?: 'Düsseldorf',
                countryCode: $oCountry->oxcountry__oxisoalpha2->value,
                houseNumber: $oUser->oxuser__oxstreetnr->value ?: '9',
                street: $oUser->oxuser__oxstreet->value ?: 'Am Staad',
                zip: $oUser->oxuser__oxzip->value ?: '40474',
            ),
            contactDetails: new ContactDetails(
                emailAddress: $oUser->oxuser__oxusername->value,
            ),
            businessRelation: BusinessRelation::B2C,
            locale: Registry::getLang()->getLanguageAbbr(),
            personalInformation: new PersonalInformation(
                dateOfBirth: '19851104',
                name: new PersonalName(
                    firstName: $oUser->oxuser__oxfname->value,
                    surname: $oUser->oxuser__oxlname->value,
                ),
            ),
        );
    }

    protected function buildShippingFromOrder($oOrder): Shipping
    {
        return new Shipping(
            address: new AddressPersonal(
                city: $oOrder->oxorder__oxdelcity->value ?: $oOrder->oxorder__oxbillcity->value,
                countryCode: 'DE',
                houseNumber: $oOrder->oxorder__oxdelstreetnr->value ?: $oOrder->oxorder__oxbillstreetnr->value,
                street: $oOrder->oxorder__oxdelstreet->value ?: $oOrder->oxorder__oxbillstreet->value,
                zip: $oOrder->oxorder__oxdelzip->value ?: $oOrder->oxorder__oxbillzip->value,
            ),
        );
    }

    protected function buildShippingFromUser($oUser): Shipping
    {
        $oSession = Registry::getSession();
        $sDelAddressId = $oSession->getVariable('deladrid');

        if ($sDelAddressId) {
            $oAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $oAddress->load($sDelAddressId);
            $sCity = $oAddress->oxaddress__oxcity->value;
            $sStreetNr = $oAddress->oxaddress__oxstreetnr->value;
            $sStreet = $oAddress->oxaddress__oxstreet->value;
            $sZip = $oAddress->oxaddress__oxzip->value;
        } else {
            $sCity = $oUser->oxuser__oxcity->value;
            $sStreetNr = $oUser->oxuser__oxstreetnr->value;
            $sStreet = $oUser->oxuser__oxstreet->value;
            $sZip = $oUser->oxuser__oxzip->value;
        }

        return new Shipping(
            address: new AddressPersonal(
                city: $sCity ?: 'Düsseldorf',
                countryCode: 'DE',
                houseNumber: $sStreetNr ?: '9',
                street: $sStreet ?: 'Am Staad',
                zip: $sZip ?: '40474',
            ),
        );
    }

    protected function buildShoppingCartFromOrder($oOrder): ShoppingCartInput
    {
        $oBasket = Registry::getSession()->getBasket();
        $aItems = [];

        foreach ($oOrder->getOrderArticles()->getArray() as $oOrderArticle) {
            $dItemPrice = (float) $oOrderArticle->oxorderarticles__oxbprice->value;
            $iVat = (int) number_format((float) $oOrderArticle->oxorderarticles__oxvat->value, 0, '.', '');
            $dVatPrice = $dItemPrice * $iVat / 100;

            $aItems[] = new CartItemInput(
                invoiceData: new CartItemInvoiceData(
                    description: $oOrderArticle->oxorderarticles__oxtitle->value,
                ),
                orderLineDetails: new OrderLineDetailsInput(
                    productPrice: $this->toCentAmount($dItemPrice),
                    quantity: (int) $oOrderArticle->oxorderarticles__oxamount->value,
                    productCode: $oOrderArticle->oxorderarticles__oxartnum->value,
                    productType: ProductType::GOODS,
                    taxAmount: $this->toCentAmount($dVatPrice),
                ),
            );
        }

        $this->addDeliveryCostItem($oBasket, $aItems);

        return new ShoppingCartInput(items: $aItems);
    }

    protected function buildShoppingCartFromBasket($oBasket): ShoppingCartInput
    {
        $aItems = [];

        foreach ($oBasket->getContents() as $oBasketItem) {
            $oArticle = $oBasketItem->getArticle();
            $sProductCode = $oArticle->oxarticles__oxean->value ?: $oArticle->oxarticles__oxartnum->value;

            $oPrice = $oBasketItem->getPrice();
            $dAmount = $oBasketItem->getAmount();
            $dUnitPrice = round($oPrice->getBruttoPrice() / $dAmount, 2);

            $aItems[] = new CartItemInput(
                invoiceData: new CartItemInvoiceData(
                    description: $oBasketItem->getTitle(),
                ),
                orderLineDetails: new OrderLineDetailsInput(
                    productPrice: $this->toCentAmount($dUnitPrice),
                    quantity: (int) $dAmount,
                    productCode: $sProductCode,
                    productType: ProductType::GOODS,
                    taxAmount: $this->toCentAmount($oPrice->getVat()),
                ),
            );
        }

        $this->addDeliveryCostItem($oBasket, $aItems);

        return new ShoppingCartInput(items: $aItems);
    }

    protected function addDeliveryCostItem($oBasket, array &$aItems): void
    {
        $oCosts = $oBasket->getCosts('oxdelivery');
        if ($oCosts === null) {
            return;
        }

        $dDeliveryCosts = (float) $oCosts->getBruttoPrice();
        if ($dDeliveryCosts <= 0) {
            return;
        }

        $aItems[] = new CartItemInput(
            invoiceData: new CartItemInvoiceData(
                description: 'Delivery Charge',
            ),
            orderLineDetails: new OrderLineDetailsInput(
                productPrice: $this->toCentAmount($dDeliveryCosts),
                quantity: 1,
                productCode: 'delivery',
                productType: ProductType::GOODS,
                taxAmount: $this->toCentAmount($dDeliveryCosts * 19 / 100),
            ),
        );
    }

    protected function buildPaymentMethodSpecificInput($oUser, string $sPaymentId): PaymentMethodSpecificInput
    {
        $sAccountHolder = $oUser->oxuser__oxfname->value . ' ' . $oUser->oxuser__oxlname->value;

        if ($sPaymentId === 'pcpsecuredebit') {
            return new PaymentMethodSpecificInput(
                financingPaymentMethodSpecificInput: new FinancingPaymentMethodSpecificInput(
                    paymentProductId: 3392,
                    requiresApproval: true,
                    paymentProduct3392SpecificInput: new PaymentProduct3392SpecificInput(
                        bankAccountInformation: new BankAccountInformation(
                            iban: 'DE52940594210000082271',
                            bic: null,
                            accountHolder: $sAccountHolder,
                        ),
                    ),
                ),
                paymentChannel: PaymentChannel::ECOMMERCE,
            );
        }

        // pcpcreditcard und pcppaypal: siehe offene Fragen unten
        return new PaymentMethodSpecificInput(
            paymentChannel: PaymentChannel::ECOMMERCE,
        );
    }

    protected function buildAmountOfMoney(): AmountOfMoney
    {
        $oBasket = Registry::getSession()->getBasket();
        $oCur = Registry::getConfig()->getActShopCurrencyObject();

        return new AmountOfMoney(
            amount: $this->toCentAmount($oBasket->getPrice()->getBruttoPrice()),
            currencyCode: $oCur->name,
        );
    }

    protected function toCentAmount(float $dPrice): int
    {
        $oCur = Registry::getConfig()->getActShopCurrencyObject();
        return (int) round($dPrice * pow(10, $oCur->decimal));
    }

    public function generateReference(string $sPrefix = 'dm'): string
    {
        return $sPrefix . date('YmdHis');
    }
}