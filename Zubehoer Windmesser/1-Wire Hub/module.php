<?
class Esera1WireHub extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->CreateVariableProfile("ESERA.Spannung15V", 2, " V", 0, 15, 0.1, 2, "");		//Type, Beschriftung, Min-Wert, Max Wert, Schrittweite, Stellen
        $this->CreateVariableProfile("ESERA.Strom1500mA", 2, " mA", 0, 1500, 1, 0, "");

        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableFloat("Strom12V", "Strom 12V", "ESERA.Strom1500mA", 1);
        $this->RegisterVariableFloat("Spannung5V", "Spannung 5V", "ESERA.Spannung15V", 2);
        $this->RegisterVariableFloat("Strom5V", "Strom 5V", "ESERA.Strom1500mA", 3);
        $this->RegisterVariableFloat("Spannung12V", "Spannung 12V", "ESERA.Spannung15V", 4);

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
        $this->SendDebug("ESERA-1WireHub", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 1) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("Strom12V"), $value);
            }
            if ($data->DataPoint == 2) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("Spannung5V"), $value);
            }
            if ($data->DataPoint == 3) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("Strom5V"), $value);
            }
            if ($data->DataPoint == 4) {
                $value = $data->Value / 100;
                SetValue($this->GetIDForIdent("Spannung12V"), $value);
            }
        }
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
