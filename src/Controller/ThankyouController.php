<?php

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;

const PCP_MODULE_PATH = "modules/Payone/PcpPrototype/";

class ThankyouController extends FrontendController
{
    protected $pcpOrderResponse = null;

    protected $txid = null;
    protected $commerceCaseId = null;
    protected $checkoutId = null;
    protected $merchantReference = null;
    protected $checkoutReference = null;


    /**
     * Renders the thank you page.
     * It retrieves the last order ID from the session and assigns it to the view data.
     *
     * @return string
     */
    public function render()
    {
        $orderResponse = Registry::getSession()->getVariable('pcpOrderResponse');
        if ($orderResponse) {
            $this->pcpOrderResponse = $orderResponse;
            Registry::getSession()->deleteVariable('pcpOrderResponse');
            Registry::getSession()->deleteVariable('usr');
        }
        $merchantReferenceSession = Registry::getSession()->getVariable('pcp_merchant_reference');
        if ($merchantReferenceSession) {
            $this->merchantReference = $merchantReferenceSession;
            Registry::getSession()->deleteVariable('pcp_merchant_reference');
        }
        return parent::render();
    }

    /**
     * Return previously save txid
     * @return mixed
     */
    public function pcpGetTxid() {
        if ($this->txid === null) {
            $this->txid = Registry::getSession()->getVariable('txid');
            if ($this->txid === '__txid__') {
                $this->txid = false;
            }
        }

        return $this->txid;
    }

    public function pcpGetCustNr($sPrefix='')
    {
        $oUser = $this->getUser();

        return $sPrefix.$oUser->oxuser__oxcustnr->value;
    }

    /**
     * Returns commercecase id if available
     * @return false|string
     */
    public function pcpGetCommerceCaseId() {
        if ($this->commerceCaseId === null) {
            if ($this->pcpOrderResponse['commerceCaseId']) {
                $this->commerceCaseId = $this->pcpOrderResponse['commerceCaseId'];
            }
        }

        if ($this->commerceCaseId === null) {
            $commerceCaseId = Registry::getSession()->getVariable('bnplInstallmentCommerceCaseId');
            if ($commerceCaseId) {
                $this->commerceCaseId = $commerceCaseId;
                Registry::getSession()->deleteVariable('bnplInstallmentCommerceCaseId');
            }
        }

        return $this->commerceCaseId;
    }

    /**
     * Returns merchantReference if available
     * @return false|string
     */
    public function pcpGetMerchantReference() {
        if ($this->merchantReference === null) {
            if ($this->pcpOrderResponse['merchantReference']) {
                $this->merchantReference = $this->pcpOrderResponse['merchantReference'];
            }

        }

        if ($this->merchantReference === null) {
            $sMerchantReference = Registry::getSession()->getVariable('pcp_merchant_reference');
            if ($sMerchantReference) {
                $this->merchantReference = $sMerchantReference;
            }
        }

        if ($this->merchantReference) {
            Registry::getSession()->setVariable('pcp_merchant_reference', $this->merchantReference);
        }

        return $this->merchantReference;
    }

    /**
     * Returns checkout reference if available
     * @return false|string
     */
    public function pcpGetCheckoutReference() {
        if ($this->checkoutReference === null) {
            if ($this->pcpOrderResponse['checkout']['references']['merchantReference']) {
                $this->checkoutReference = $this->pcpOrderResponse['checkout']['references']['merchantReference'];
            }

        }

        if ($this->checkoutReference === null) {
            $merchantReference = Registry::getSession()->getVariable('pcp_checkout_reference');
            if ($merchantReference) {
                $this->checkoutReference = $merchantReference;
                Registry::getSession()->deleteVariable('pcp_checkout_reference');
            }
        }

        return $this->checkoutReference;
    }

    /**
     * Returns checkout id if available
     * @return false|string
     */
    public function pcpGetCheckoutId() {
        if ($this->checkoutId === null) {
            if ($this->pcpOrderResponse['checkout']['checkoutId']) {
                $this->checkoutId = $this->pcpOrderResponse['checkout']['checkoutId'];
            }
        }

        if ($this->checkoutId === null) {
            $sCheckoutId = Registry::getSession()->getVariable('bnplInstallmentCheckoutId');
            if ($sCheckoutId) {
                $this->checkoutId = $sCheckoutId;
                Registry::getSession()->deleteVariable('bnplInstallmentCheckoutId');
            }
        }

        return $this->checkoutId;
    }

    /**
     * Returns if current order will be send to store
     * @return bool
     */
    public function pcpIsStoreShipping()
    {
        return (bool) Registry::getSession()->getVariable('pcp_is_store_shipping');
    }

    public function pcpGetBarcodeCommerceCase()
    {
        $sIdent = $this->pcpGetCommerceCaseId();
        return $this->pcpGetBarcode($sIdent);
    }

    public function pcpGetBarcodeTxid()
    {
        $sIdent = $this->pcpGetTxid();
        return $this->pcpGetBarcode($sIdent);
    }

    public function pcpGetBarcodeCheckout()
    {
        $sIdent = $this->pcpGetCheckoutId();
        return $this->pcpGetBarcode($sIdent);
    }

    public function pcpGetBarcodeReference()
    {
        $sIdent = $this->pcpGetMerchantReference();
        return $this->pcpGetBarcode($sIdent);
    }

    public function pcpGetQrCodeReference()
    {
        $sIdent = $this->pcpGetMerchantReference();
        return $this->pcpGetQrCode($sIdent);
    }

    public function pcpGetBarcodeCheckoutReference()
    {
        $sIdent = $this->pcpGetCheckoutReference();
        return $this->pcpGetBarcode($sIdent);
    }

    public function pcpGetQrCodeCheckoutReference()
    {
        $sIdent = $this->pcpGetCheckoutReference();
        return $this->pcpGetQrCode($sIdent);
    }


    public function pcpGetQrCodeCommerceCase()
    {
        $sIdent = $this->pcpGetCommerceCaseId();
        return $this->pcpGetQrCode($sIdent);
    }

    public function pcpGetQrCodeTxid()
    {
        $sIdent = $this->pcpGetTxid();
        return $this->pcpGetQrCode($sIdent);
    }

    public function pcpGetQrCodeCheckout()
    {
        $sIdent = $this->pcpGetCheckoutId();
        return $this->pcpGetQrCode($sIdent);
    }

    public function pcpGetQrCode($ident)
    {
        $qrLibPath = getShopBasePath() . PCP_MODULE_PATH . "lib/phpqrcode/qrlib.php";
        $qrCodeFolder = getShopBasePath() . "out/pictures/qrcodes";
        if (!file_exists($qrCodeFolder)) {
            mkdir($qrCodeFolder, 0777, true);
        }
        $file = $qrCodeFolder . sprintf("/%s.png", $ident);
        $imageSource = sprintf("out/pictures/qrcodes/%s.png", $ident);

        // create qrcode
        include_once $qrLibPath;
        QRcode::png($ident, $file);

        return $imageSource;
    }

    public function pcpGetBarcode($ident)
    {
        $barcodeLibPath = getShopBasePath() . PCP_MODULE_PATH . "lib/barcode.php";
        $barcodeFolder = getShopBasePath() . "out/pictures/barcodes";
        if (!file_exists($barcodeFolder)) {
            mkdir($barcodeFolder, 0777, true);
        }
        $file = $barcodeFolder . sprintf("/%s.png", $ident);
        $imageSource = sprintf("out/pictures/barcodes/%s.png", $ident);

        // create barcode
        include_once $barcodeLibPath;
        $generator = new barcode_generator();
        $options = [
            'f' => 'png',
            's' => 'code-128',
        ];
        $image = $generator->render_image('code-128', $ident, $options);
        imagepng($image, $file);

        return $imageSource;
    }
    
}