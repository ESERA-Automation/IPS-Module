<?
class EseraStation200IO extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        //$this->RegisterPropertyInteger("SYS", 0);
        for($i = 1; $i <= 2; $i++){
          $this->RegisterVariableBoolean("Input".$i, "Input ".$i, "~Switch");
        }

        for($i = 1; $i <= 2; $i++){
    			$this->RegisterVariableBoolean("Output".$i, "Output ".$i, "~Switch");
    			$this->EnableAction("Output".$i);
    		}

        $this->ConnectParent("{EDE0EBAA-4197-4D62-9037-3B1C88E40A5C}"); //1Wire Controller
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

	
	public function ApplyChanges(){
     //Never delete this line!
     parent::ApplyChanges();

     $this->SetReceiveDataFilter(".*\"DeviceType\":\"SYS\".*");
	}
	
	
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("EseraStation200IO", "DeviceNumber:" . $data->DeviceNumber . " | DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

		/*
				if ($data->DeviceNumber == 0){
					$value = $data->Value;
					SetValue($this->GetIDForIdent("SYS0"), $value);
				}
        */
		
        if ($data->DeviceNumber == 1){
          if ($data->DataPoint == 1){
            $value = intval($data->Value, 10);
            for ($i = 1; $i <= 2; $i++){
              SetValue($this->GetIDForIdent("Input".$i), ($value >> ($i-1)) & 0x01);
            }
          }
        }

		if ($data->DeviceNumber == 2){
           if ($data->DataPoint == 1){
             $value = intval($data->Value, 10);
              for ($i = 1; $i <= 2; $i++){
        	    SetValue($this->GetIDForIdent("Output".$i), ($value >> ($i-1)) & 0x01);
        	 }
           }
		}
    }

    public function RequestAction($Ident, $Value) {
  		switch($Ident) {
  			case "Output1":
  			case "Output2":


  				$this->SetSysOutput(SubStr($Ident, 6, 1), $Value);
  				break;
  			default:
  				throw new Exception("Invalid ident");
  		}
  	}

    public function SetSysOutput(int $OutputNumber, int $Value) {
  		$OutputNumber = $OutputNumber;
  		$this->Send("SET,SYS,OUT,". $OutputNumber .",". $Value ."");
  	}


    private function CreateVariableProfile($ProfileName, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
		    if (!IPS_VariableProfileExists($ProfileName)) {
			       IPS_CreateVariableProfile($ProfileName, $ProfileType);
			       IPS_SetVariableProfileText($ProfileName, "", $Suffix);
			       IPS_SetVariableProfileValues($ProfileName, $MinValue, $MaxValue, $StepSize);
			       IPS_SetVariableProfileDigits($ProfileName, $Digits);
			       IPS_SetVariableProfileIcon($ProfileName, $Icon);
		    }
	  }

    private function Send($Command) {

      //Zur 1Wire Controller Instanz senden
    	return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

    }
}
?>
