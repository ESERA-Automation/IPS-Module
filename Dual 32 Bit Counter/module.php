<?php
class EseraDual32BitCounter extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableInteger("ZaehlerA", "Counter 1", "", 1);
        $this->RegisterVariableInteger("ZaehlerB", "Counter 2", "", 1);
        
        $this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();

		// Variables
		if (!@$this->GetIDForIdent("ZaehlerA")) $this->RegisterVariableInteger("ZaehlerA", "Counter 1", "", 1);
		if (!@$this->GetIDForIdent("ZaehlerB")) $this->RegisterVariableInteger("ZaehlerB", "Counter 2", "", 1);

        //Apply filter
        $this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .",.*");

    }
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("EseraDual32BitCounter", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 1) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("ZaehlerA"), $value);
            }
            if ($data->DataPoint == 2) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("ZaehlerB"), $value);
            }
        }
    }
}
?>
