<?
class EseraAnalogOut010V extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->CreateVariableProfile("ESERA.Spannung10V", 2, " V", 0, 10, 0.1, 2, "");

        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableFloat("AnalogOut", "Analog Out", "ESERA.Spannung10V", 2);
        $this->EnableAction("AnalogOut");

        $this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();

        //Apply filter
        $this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .",.*");

    }
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("ESERA-Analog-Out-0-10V", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 0) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("AnalogOut"), $value);
            }
        }
    }
    public function RequestAction($Ident, $Value) {
      $Value = $Value * 100;
      $this->SetAnalogOutput($Value);
    }
    public function SetAnalogOutput(int $Value) {
  		$this->Send("SET,OWD,OUTA,". $this->ReadPropertyInteger("OWDID"). "," . $Value ."");
  	}
    private function Send($Command) {

      //Zur 1Wire Controller Instanz senden
    	return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

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
}
?>
