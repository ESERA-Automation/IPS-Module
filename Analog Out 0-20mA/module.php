<?
class EseraAnalogOut020mA extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->CreateVariableProfile("ESERA.Strom20mA", 2, " mA", 0, 20, 0.1, 2, "Intensity");

        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableFloat("AnalogOut", "Analog Out", "ESERA.Strom20mA", 2);
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
        $this->SendDebug("ESERA-Analog-Out-0-20mA", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 0) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("AnalogOut"), $value);
            }
        }
    }
    public function RequestAction($Ident, $Value) {
      $Value = $Value * 100;
      $this->SetAnalogOutputmA($Value);
    }
    public function SetAnalogOutputmA(int $Value) {
  		$this->Send("SET,OWD,OUTAMA,". $this->ReadPropertyInteger("OWDID"). "," . $Value ."");
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
