<?
class AudioMaxRoomIO extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
		
		//$this->CreateVariableProfile("ESERA.Luftguete", 1, " ppm", 400, 1800, 1, 2, "");
		
        $this->CreateVariableProfileVolume("ESERA:Volume",1," dB",40,0,1,2,"%");
		$this->CreateVariableProfileGain("ESERA:Gain",1," dB",0,15,1,2,"%");
	    $this->CreateVariableProfileTone("ESERA:Tone",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfileBalance("ESERA:Balance",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfileMute(("ESERA:Mute",3,"",0,1,0,0,"");

		//for($i = 1; $i <= 2; $i++){
    			$this->RegisterVariableinteger("volume".$i, "Volume".$i, "ESERA.Volume");			
    			$this->EnableAction("ampOut".$i);
				
				$this->RegisterVariableinteger("gain".$i, "Gain".$i, "ESERA.Gain");
    			$this->EnableAction("gain".$i);
				
				$this->RegisterVariableinteger("bass".$i, "Bass".$i, "ESERA.Tone");
    			$this->EnableAction("bass".$i);
				
				$this->RegisterVariableinteger("mid".$i, "Middle".$i, "ESERA.Tone");
    			$this->EnableAction("mid".$i);
				
				$this->RegisterVariableinteger("treble".$i, "Treble".$i, "ESERA.Tone");
    			$this->EnableAction("treble".$i);

				$this->RegisterVariableinteger("balance".$i, "Balance".$i, "Balance");
    			$this->EnableAction("balance".$i);		
							
    			$this->RegisterVariableBoolean("ampOut".$i, "Amplifier on-off".$i, "~Switch");
    			$this->EnableAction("ampOut".$i);

    			$this->RegisterVariableBoolean("mute".$i, "Mute Output".$i, "Mute");
    			$this->EnableAction("mute".$i);				
    	//	}
	
        $this->RegisterVariableBoolean("pwr".$i, "Power ".$i, "~Switch");
        $this->EnableAction("pwr".$i);

        $this->ConnectParent("{C73DD44F-BF0D-4180-A0F1-D296F68024B2}"); //AudioMax Server
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

	
	public function ApplyChanges(){
     //Never delete this line!
     parent::ApplyChanges();

     $this->SetReceiveDataFilter(".*\"SYS\".*");
	 $this->SetReceiveDataFilter(".*\"AUDIO\".*");
	 $this->SetReceiveDataFilter(".*\"KAL\".*");
	}
	
	//-------------------------------------------------------------------------------
	//Funktionen zum Detenempfang und Zuweisen zu Variablen
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("AudioMaxRoomIO", "DataSource:" . $data->DataSource . " | DataRoom:" . $data->DataRoom . " | DataType:" . $data->DataType . " | Value: " . $data->Value, 0);

		/*
				if ($data->DeviceNumber == 0){
					$value = $data->Value;
					SetValue($this->GetIDForIdent("SYS0"), $value);
				}
        */
		
        if ($data->DataSource == "SYS"){

            if ($data->DataType == "PWR"){
				$value = intval($data->Value, 10);
                SetValue($this->GetIDForIdent("Power"), $value);
            }			          
        }

		if ($data->DataSource == "AUDIO"){
           if ($data->DataType == "VOL"){
               $value = intval($data->Value, 10);
               
			   for ($i = 1; $i <= 2; $i++){
        	    SetValue($this->GetIDForIdent("Output".$i), ($value >> ($i-1)) & 0x01);
        	 }
           }
		}
		
    }
    //-------------------------------------------------------------------------------
	//Funktionen zum Senden
	
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
