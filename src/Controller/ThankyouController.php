<?php

namespace Payone\PcpPrototype\Controller;

use OxidEsales\Eshop\Core\Registry;
use kreativekorp\barcode\barcode_generator;

const PCP_MODULE_PATH = "modules/Payone/PcpPrototype/";

class ThankyouController extends ThankyouController_parent
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
        Registry::getLogger()->error('Render thankyou controller');

        $orderResponse = Registry::getSession()->getVariable('pcpOrderResponse');
        if ($orderResponse) {
            Registry::getLogger()->error('Order response: '. $orderResponse);
            $this->pcpOrderResponse = $orderResponse;
            Registry::getSession()->deleteVariable('pcpOrderResponse');
            Registry::getSession()->deleteVariable('usr');
        }
        $merchantReferenceSession = Registry::getSession()->getVariable('pcp_merchant_reference');
        if ($merchantReferenceSession) {
            Registry::getLogger()->error('Merchant reference from session: '. $merchantReferenceSession);
            $this->merchantReference = $merchantReferenceSession;
            Registry::getSession()->deleteVariable('pcp_merchant_reference');
        }

        Registry::getLogger()->error('Call parent render in thankyou controller');
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
        return $this->pcpGetScanCode($sIdent);
    }

    public function pcpGetBarcodeTxid()
    {
        $sIdent = $this->pcpGetTxid();
        return $this->pcpGetScanCode($sIdent);
    }

    public function pcpGetBarcodeCheckout()
    {
        $sIdent = $this->pcpGetCheckoutId();
        return $this->pcpGetScanCode($sIdent);
    }

    public function pcpGetBarcodeReference()
    {
        $sIdent = $this->pcpGetMerchantReference();
        return $this->pcpGetScanCode($sIdent);
    }

    public function pcpGetQrCodeReference()
    {
        $sIdent = $this->pcpGetMerchantReference();
        return $this->pcpGetScanCode($sIdent, 'qr');
    }

    public function pcpGetBarcodeCheckoutReference()
    {
        $sIdent = $this->pcpGetCheckoutReference();
        return $this->pcpGetScanCode($sIdent);
    }

    public function pcpGetQrCodeCheckoutReference()
    {
        $sIdent = $this->pcpGetCheckoutReference();
        return $this->pcpGetScanCode($sIdent, 'qr');
    }


    public function pcpGetQrCodeCommerceCase()
    {
        $sIdent = $this->pcpGetCommerceCaseId();
        return $this->pcpGetScanCode($sIdent, 'qr');
    }

    public function pcpGetQrCodeTxid()
    {
        $sIdent = $this->pcpGetTxid();
        return $this->pcpGetScanCode($sIdent, 'qr');
    }

    public function pcpGetQrCodeCheckout()
    {
        $sIdent = $this->pcpGetCheckoutId();
        return $this->pcpGetScanCode($sIdent, 'qr');
    }

    public function pcpGetScanCode($ident, $type = 'code-128')
    {
        $barcodeLibPath = getShopBasePath() . PCP_MODULE_PATH . "lib/barcode.php";
        $scanCodeFolder = getShopBasePath() . "out/pictures/scancodes";
        if (!file_exists($scanCodeFolder)) {
            mkdir($scanCodeFolder, 0777, true);
        }
        $file = $scanCodeFolder . sprintf("/%s_%s.png", $ident, $type);
        $imageSource = sprintf("out/pictures/scancodes/%s_%s.png", $ident, $type);

        $paths = [
            'libPath' => $barcodeLibPath,
            'qrCodeFolder' => $scanCodeFolder,
            'file' => $file,
            'imageSource' => $imageSource
        ];
        Registry::getLogger()->error('Paths for scancodes:' . print_r($paths, true));

        // create barcode
        include_once $barcodeLibPath;
        $generator = new barcode_generator();
        $options = [
            'f' => 'png',
            's' => $type,
        ];
        $image = $generator->render_image($type, sprintf('%s_%s', $ident, $type), $options);
        imagepng($image, $file);

        return $imageSource;
    }
    
}