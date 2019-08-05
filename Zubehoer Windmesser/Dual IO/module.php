<?
class EseraDualIO extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableInteger("Eingang0", "Eingang 0", "", 1);
        $this->RegisterVariableInteger("Eingang1", "Eingang 1", "", 2);
        $this->RegisterVariableInteger("Status0", "Status 0", "", 3);
        $this->RegisterVariableInteger("Status1", "Status 1", "", 4);

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
        $this->SendDebug("ESERA-DualIO", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 1) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Eingang0"), $value);
            }
            if ($data->DataPoint == 2) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Eingang1"), $value);
            }
            if ($data->DataPoint == 3) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Status0"), $value);
            }
            if ($data->DataPoint == 4) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Status1"), $value);
            }
         }
      }
}
?>
