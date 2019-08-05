<?
class EseraBinaerEingangDual extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("OWDID", 1);

        //$this->RegisterVariableInteger("EingangA", "Eingang A", "", 1);
        //$this->RegisterVariableInteger("EingangB", "Eingang B", "", 1);
		$this->RegisterVariableBoolean("InputA", "Input A", "~Switch");
		$this->RegisterVariableBoolean("InputB", "Input B", "~Switch");

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
        $this->SendDebug("EseraBinaerEingangDual", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 1) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("InputA"), $value);
            }
            if ($data->DataPoint == 2) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("InputB"), $value);
            }
        }
    }
}
?>
