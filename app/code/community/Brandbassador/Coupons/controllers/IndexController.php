<?php
/**
 * Magento
 *
 *
 * @category    Brandbassador
 * @package     Brandbassador_Package
 */
class Brandbassador_Coupons_IndexController extends Mage_Core_Controller_Front_Action
{   
    private $response = array();

    public function __destruct()
    {   
        if (is_array($this->response)) {
            echo json_encode($this->response);
        } else if (is_string($this->response)) {
            echo $this->response;
        }
    }

    /**
     * Index Action
     */
    public function indexAction()
    {
        $coupon  = Mage::getModel('coupons/coupon');
        $helper  = Mage::helper('coupons/coupon');
        $request = $this->getRequest()->getParams();

        if (empty($request)) {
            $this->response = "You have successfully installed Brandbassador Extension v" . $helper->getVersion() . ".";
            exit();
        }

        // validate auth key
        if (!$helper->validateAuthKey($request)) {
            $this->response['status']  = "error";
            $this->response['details'] = "invalid auth key";
            exit();
        }

        // validate request
        if ($coupon->validate($request)) {
            
            $coupon->create();
            $this->response['status'] = "success";
        } else {

            $this->response['status']  = "error";
            $this->response['details'] = $coupon->getErrors();
        }
    }
}
