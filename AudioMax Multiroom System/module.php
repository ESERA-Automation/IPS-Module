<?
class AudioMaxSystem extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        //$this->RegisterPropertyInteger("SYS", 0);
		$this->CreateVariableProfile("ESERA.AMVolume",1," dB",40,0,1,2,"%");
		$this->CreateVariableProfile("ESERA.AMGain",1," dB",0,15,1,2,"%");
	    $this->CreateVariableProfile("ESERA.AMTone",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfile("ESERA.AMBalance",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfile("ESERA.AMMute",3,"",0,1,0,0,"");

		/*
		for($i = 1; $i <= 2; $i++){
    			$this->RegisterVariableinteger("volume".$i, "Volume".$i, "ESERA.AMVolume");			
    			$this->EnableAction("ampOut".$i);
				
				$this->RegisterVariableinteger("gain".$i, "Gain".$i, "ESERA.AMGain");
    			$this->EnableAction("gain".$i);
				
				$this->RegisterVariableinteger("bass".$i, "Bass".$i, "ESERA.AMTone");
    			$this->EnableAction("bass".$i);
				
				$this->RegisterVariableinteger("mid".$i, "Middle".$i, "ESERA.AMTone");
    			$this->EnableAction("mid".$i);
				
				$this->RegisterVariableinteger("treble".$i, "Treble".$i, "ESERA.AMTone");
    			$this->EnableAction("treble".$i);

				$this->RegisterVariableinteger("balance".$i, "Balance".$i, "ESERA.AMBalance");
    			$this->EnableAction("balance".$i);		
							
    			$this->RegisterVariableBoolean("ampOut".$i, "Amplifier on-off".$i, "~Switch");
    			$this->EnableAction("ampOut".$i);

    			$this->RegisterVariableBoolean("mute".$i, "Mute Output".$i, "ESERA.AMMute");
    			$this->EnableAction("mute".$i);				
    		}
*/
        $this->ConnectParent("{C73DD44F-BF0D-4180-A0F1-D296F68024B2}"); 			//AudioMax Interface
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

	
	public function ApplyChanges(){
     //Never delete this line!
     parent::ApplyChanges();

     $this->SetReceiveDataFilter(".*\"DeviceType\":\"AUDIO\".*");
	}
	
	
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("AudioMaxSystem", "DeviceType" => "AUDIO", "RoomNumber" => $RoomNumber, "DataType" => $dataType, "Value" => $value)), 0);

	    $this->SendDebug("roomnumber", $RoomNumber, 0);
		$this->SendDebug("datatype", $dataType, 0);
		$this->SendDebug("value", $value, 0);
		
		/*
				if ($data->DeviceNumber == 0){
					$value = $data->Value;
					SetValue($this->GetIDForIdent("SYS0"), $value);
				}
        */
		
		/*
        
		
		
		if ($dataType == "Volume"){
            SetValue($this->GetIDForIdent("Volume".$RoomNumber), ($value >> ($i-1)) & 0x01);
            }

		if ($data->DeviceNumber == 2){
           if ($data->DataPoint == 1){
             $value = intval($data->Value, 10);
              for ($i = 1; $i <= 2; $i++){
        	    SetValue($this->GetIDForIdent("Output".$i), ($value >> ($i-1)) & 0x01);
        	 }
           }
		}
		*/
		
    }
/*
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
*/

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
