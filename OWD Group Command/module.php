<?
class OWDGroupCommand extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        //$this->RegisterPropertyInteger("SYS", 0);
        $this->CreateVariableProfile("ESERA.group", 2, " ", 1, 240, 1, 1, "");

        $this->RegisterVariableInteger("group", "Group Adress", "ESERA.group");



        $this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

	
	public function ApplyChanges(){
     //Never delete this line!
     parent::ApplyChanges();

     $this->SetReceiveDataFilter(".*\"DeviceType\":\"GRP\".*");
	}
	
	
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("OWDGroupCommand", "GRPNumber:" . $data->Number . " | Function:" . $data->DataPoint . " | Value: " . $data->Value, 0);

		/*
				if ($data->DeviceNumber == 0){
					$value = $data->Value;
					SetValue($this->GetIDForIdent("SYS0"), $value);
				}
        */
		
		SetValue($this->GetIDForIdent("group"), $value);
		
    }

    public function RequestAction($Ident, $Value) {
  		
  	}

	//Gruppenbefehle
	public function SetGroupOut(int $GRPNumber, int $Value) {
		$this->Send("SET,OWD,GRP,". $GRPNumber .",". $Value ."");
		//$this->Send("SET,OWD,GRP,". $GroupNumber .","."SHT".",". $Value ."");
		$this->SendDebug("OWDGroupCommand", "GruppenNumber:" . $data->$GRPNumber . " | Function: | Value: " . $data->Value, 0);
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
