<?
class AudioMaxSystem extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        //$this->RegisterPropertyInteger("SYS", 0);
		$this->CreateVariableProfile("ESERA.AMVolume",1,"%",40,0,1,2,"Intensity");
		$this->CreateVariableProfile("ESERA.AMGain",1,"%",0,15,1,2,"Intensity");
	    $this->CreateVariableProfile("ESERA.AMTone",1,"%",0,15,1,2,"Intensity");
		$this->CreateVariableProfile("ESERA.AMBalance",1,"%",0,15,1,2,"Intensity");
		//$this->CreateVariableProfile("ESERA.AMMute",3,"",0,1,0,0,"Power");

		
		for($i = 1; $i <= 2; $i++){
    			$this->RegisterVariableinteger("volume".$i, "Volume ".$i, "ESERA.AMVolume");			
    			$this->EnableAction("volume".$i);
				
				$this->RegisterVariableinteger("gain".$i, "Gain ".$i, "ESERA.AMGain");
    			$this->EnableAction("gain".$i);
				
				$this->RegisterVariableinteger("bass".$i, "Bass ".$i, "ESERA.AMTone");
    			$this->EnableAction("bass".$i);
				
				$this->RegisterVariableinteger("mid".$i, "Middle ".$i, "ESERA.AMTone");
    			$this->EnableAction("mid".$i);
				
				$this->RegisterVariableinteger("treble".$i, "Treble ".$i, "ESERA.AMTone");
    			$this->EnableAction("treble".$i);

				$this->RegisterVariableinteger("balance".$i, "Balance ".$i, "ESERA.AMBalance");
    			$this->EnableAction("balance".$i);		
							
    			$this->RegisterVariableBoolean("ampout".$i, "Amplifier on/off ".$i, "~Switch");
    			$this->EnableAction("ampout".$i);

    			$this->RegisterVariableBoolean("mute".$i, "Mute Output ".$i, "~Switch");			
    			$this->EnableAction("mute".$i);	

    			$this->RegisterVariableInteger("input".$i, "Input ".$i, "");
    			$this->EnableAction("input".$i);				
    		}

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
        $this->SendDebug("AudioMaxSystem", "| Room Number:" . $data->RoomNumber . "| Audio Type:" . $data->AudioType . "| Audio Value:" . $data->AudioValue, 0);
        
		$Number = intval($data->RoomNumber, 10); 		//Daten der übergebenen Variable
		//$Type = strval($data->AudioType,10); 		//Daten der übergebenen Variable
		$Type = $data->AudioType;
		$Value = intval($data->AudioValue,10);
				
	    $this->SendDebug("Room Number", $Number, 0);
		$this->SendDebug("Audio Type", $Type, 0);
		$this->SendDebug("Audio Value", $Value, 0);
		
  		
		if ($Type == "VOL"){
			SetValue($this->GetIDForIdent("volume".$Number), $Value);
			$this->SendDebug(("volume".$Number), $Value,0);
			}	
		if ($Type == "BAS"){
			SetValue($this->GetIDForIdent("bass".$Number), $Value);
			$this->SendDebug(("bass".$Number), $Value,0);
			}
		if ($Type == "MID"){
			SetValue($this->GetIDForIdent("mid".$Number), $Value);
			$this->SendDebug(("mid".$Number), $Value,0);
			}
		if ($Type == "TRE"){
			SetValue($this->GetIDForIdent("treble".$Number), $Value);
			$this->SendDebug(("treble".$Number), $Value,0);
			}
		if ($Type == "BAL"){
			SetValue($this->GetIDForIdent("balance".$Number), $Value);
			$this->SendDebug(("balance".$Number), $Value,0);
			}
		if ($Type == "GAI"){
			SetValue($this->GetIDForIdent("gain".$Number), $Value);
			$this->SendDebug(("gain".$Number), $Value,0);
			}
		if ($Type == "AMP"){
			SetValue($this->GetIDForIdent("ampout".$Number), $Value);
			$this->SendDebug(("ampout".$Number), $Value,0);
			}
		if ($Type == "AMP"){
			SetValue($this->GetIDForIdent("mute".$Number), $Value);
			$this->SendDebug(("mute".$Number), $Value,0);
			}
		if ($Type == "INP"){
			SetValue($this->GetIDForIdent("input".$Number), $Value);
			$this->SendDebug(("input".$Number), $Value,0);
			}
	/*
				if ($Number == 1){
				  SetValue($this->GetIDForIdent("volume2"), $Value);
				  SetValue($this->GetIDForIdent("volume".$Number), $Value);
				  $this->SendDebug(("volume".$Number), $Value,0);
				}
	*/
				
        
/*
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

	public function RequestAction($Ident, $Value) {
		$Number = 1;
		
		switch($Ident) {
			case "volume1":
				$this->SetAudioSettingAM($Number, "VOL", $Value);
			case "gain1":
			case "bass1":
				$this->SetAudioSettingAM($Number, "BAS", $Value);
			case "mid1":
			case "treble1":
			case "balance1":
			case "ampout1":
				$this->SetAudioSettingAM($Number, "AMP", $Value);
			case "mute1":
				$this->SetAudioSettingAM($Number, "mute", $Value);
				
				break;
			default:
				throw new Exception("Invalid ident");
		}
	}



    public function SetAudioSettingAM(int $Number, int $Type , int $Value) {
  		$this->Send("SET,AUDIO,". $Number .",". $Type . ",". $Value ."");
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
