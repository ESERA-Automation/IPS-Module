<?
class OWDGroupCommand extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
      
		//$this->RegisterVariableString("Grp", "Gruppen Nr.");
		//$this->RegisterPropertyString("Grp", "Gruppen Nr."); 
        
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
		
		// Datenverarbeitung und schreiben der Werte in die Statusvariablen
        //SetValue($this->GetIDForIdent("Grp"), $data->Buffer);
        //$this->SendDebug("OWDGroupCommand", "GRPNumber:" . $data->Number . " | Function:" . $data->DataPoint . " | Value: " . $data->Value, 0);
        $this->SendDebug("OWDGroupCommand", "GRPNumber:" . $data);
		
    }

    public function RequestAction($Ident, $Value) {
  	    switch($Ident) {
        case "grp":
            //Hier würde normalerweise eine Aktion z.B. das Schalten ausgeführt werden
            //Ausgaben über 'echo' werden an die Visualisierung zurückgeleitet
 
            //Neuen Wert in die Statusvariable schreiben
            SetValue($this->GetIDForIdent($Ident), $Value);
            break;
        default:
            throw new Exception("Invalid Ident");
    }	
  	}

	//Gruppenbefehle
	public function SetGroupShtOut(int $Number, int $Value) {
		$this->Send("SET,OWD,GRP,SHT,". $Number .",". $Value ."" );
		//$this->SendDebug("GruppenNumber:" . $Number . "|SHT |Value: ". $Value);

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
