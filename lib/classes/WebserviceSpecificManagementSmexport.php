<?php




class WebserviceSpecificManagementSmexport implements WebserviceSpecificManagementInterface
{
    /** @var WebserviceOutputBuilder */
    protected $objOutput;
    protected $output;

    /** @var WebserviceRequest */
    protected $wsObject;

    /* ------------------------------------------------
     * GETTERS & SETTERS
     * ------------------------------------------------ */

    /**
     * @param WebserviceOutputBuilderCore $obj
     *
     * @return WebserviceSpecificManagementInterface
     */
    public function setObjectOutput(WebserviceOutputBuilderCore $obj)
    {
        $this->objOutput = $obj;

        return $this;
    }

    public function setWsObject(WebserviceRequestCore $obj)
    {
        $this->wsObject = $obj;

        return $this;
    }

    public function getWsObject()
    {
        return $this->wsObject;
    }

    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    public function setUrlSegment($segments)
    {
        $this->urlSegment = $segments;

        return $this;
    }

    public function getUrlSegment()
    {
        return $this->urlSegment;
    }

    /**
     * Management of search.
     */
    public function manage()
    {
    
		$data = Db::getInstance()->executeS('SELECT d.data, d.`data_carrier`, d.`cart_id`, o.id_order FROM `'._DB_PREFIX_ . 'smanager_data` d JOIN `'._DB_PREFIX_ . 'orders` o ON d.cart_id = o.id_cart ');
		
		$xml = '<prestashop xmlns:xlink="http://www.w3.org/1999/xlink"><orders>';
		
		
		foreach($data as $i=>$row){
			
			$xml .= '<order id="'.$row['id_order']. '" sm_carrier_id="'.$row['data_carrier'].'" sm_point_id="'.$row['data'].'" />';
		}
		
		
		$xml .= '</orders></prestashop>';
		
        $this->output = $xml;
    }

    /**
     * This must be return a string with specific values as WebserviceRequest expects.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->output;
    }
}
