<?php
/**
 * Magento
 *
 *
 * @category    Brandbassador
 * @package     Brandbassador_Coupons
 */
class Brandbassador_Coupons_Model_Coupon
{	
    private $errors, $request, $rule, $helper; 

    public function __construct()
    {
        $this->rule   = Mage::getModel('salesrule/rule');
        $this->helper = Mage::helper('coupons/coupon');
    }

	/**
	 * Create a custom coupon
	 */
    public function create()
    {
    	$code   = strtoupper($this->request['code']);

		$this->rule->setName("BrandBassador - ".$this->request['code'])
			->setDescription($code. "-BRANDBASSADOR-DATE_" . date("Y-m-d_H:i:s", time()))
			->setCouponCode($code)
			->setUsesPerCustomer(0)
			->setToDate($this->request['expire'])
			->setCustomerGroupIds($this->helper->getAllCustomerGroups())
			->setIsActive(1)
			->setConditionsSerialized('a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
			->setActionsSerialized('a:6:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}')
			->setStopRulesProcessing(1)
			->setIsAdvanced(1)
			->setProductIds('')
			->setSortOrder(0)
			->setDiscountQty(0)
			->setDiscountStep(0)
			->setSimpleFreeShipping(0)
			->setApplyToShipping(0)
			->setIsRss(0)
			->setWebsiteIds($this->helper->getAllWebsites())
			->setCouponType(2);

        // limit coupon usage if required
		if (isset($this->request['u_limit'])) {
			$this->rule->setUsesPerCoupon($this->request['u_limit']);
		}

        // check if discount is percentage or fixed amount
        if (isset($this->request['percentage'])) {
            $this->rule->setSimpleAction('by_percent');
            $this->rule->setDiscountAmount($this->request['percentage']);
        }
        else if (isset($this->request['amount'])) {
            $this->rule->setSimpleAction('cart_fixed');
            $this->rule->setDiscountAmount($this->request['amount']);
        }

		$this->rule->save();
    }

    /**
	 * Validate coupon details
	 */
    public function validate($request)
    {	
    	// check if code is provided
    	if (!isset($request['code']) || empty($request['code'])) {
    	    $this->catchError("code not provided");
    	}

    	// check if code is valid
    	elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $request['code']) || strlen($request['code']) > 15) {
    	    $this->catchError("invalid code");
    	}

    	// check if coupon already exists
    	elseif ($this->isDuplicate($request['code'])) {
    		$this->catchError("code already exists");
    	}

    	// check if percentage or amount was provided
        if (!isset($request['percentage']) && !isset($request['amount'])) {
            $this->catchError("discount value not provided");
        }

        // check percentage if provided
    	if (isset($request['percentage'])) {
            if (!is_numeric($request['percentage']) || $request['percentage'] < 0 || $request['percentage'] > 100) {
                $this->catchError("invalid percentage");
            }
    	}
        // check amount if provided
        else if (isset($request['amount'])) {
            if (!is_numeric($request['amount']) || $request['amount'] < 0) {
                $this->catchError("invalid amount");
            }

            // check currency conversion
            $currency = $this->helper->getBaseCurrencyCode();
            if (empty($currency)) {
                $this->catchError("could not retrieve shop base currency");
            } else {
                // try conversion
                $request['amount'] = $this->helper->convertFromUSD($request['amount'], $currency);

                if (empty($request['amount'])) {
                    $this->catchError("could not convert from USD to " . $currency);
                }
            }
        }

    	// check expiration date
    	if (!isset($request['expire'])) {
    		$this->catchError("invalid expiration date");
    	}

    	if (!$this->hasErrors()) {
            $this->request = $request;
    		return true; 
    	}

    	return false;
    }

    /**
	 * Check if code already exists
	 */
    public function isDuplicate($code)
    {
    	$rules = Mage::getResourceModel('salesrule/rule_collection')->load();

    	foreach ($rules as $rule) {
    		if(strtolower($rule->getCode()) == strtolower($code)) {
    			return true;
    		}
    	}

    	return false;
    }    

    /**
	 * Return validation errors
	 */
    public function getErrors()
    {
    	return $this->errors;
    }

    /**
	 * Add error to errors array
	 */
    public function catchError($error)
    {
    	$this->errors[] = $error;
    }

    /**
	 * Check if there are any errors
	 */
    public function hasErrors()
    {
    	if (!empty($this->errors)) {
    		return true;
    	}

    	return false;
    }
}
