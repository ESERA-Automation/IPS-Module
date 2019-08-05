<?
class EseraBinaerAusgangDual extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("OWDID", 1);

        $this->RegisterVariableBoolean("Ausgang1", "Ausgang 1", "~Switch", 1);
        $this->EnableAction("Ausgang1");
        $this->RegisterVariableBoolean("Ausgang2", "Ausgang 2", "~Switch", 1);
        $this->EnableAction("Ausgang2");

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
        $this->SendDebug("EseraBinaerAusgangDual", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 1) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Ausgang1"), $value);
            }
            if ($data->DataPoint == 2) {
                $value = $data->Value;
                SetValue($this->GetIDForIdent("Ausgang2"), $value);
            }
        }
    }
    public function RequestAction($Ident, $Value) {
      $this->SetDualOutput(SubStr($Ident, 7, 1), $Value);
    }
    public function SetDualOutput(int $OutputNumber, int $Value) {
      $OutputNumber = $OutputNumber - 1;
      $this->Send("SET,OWD,OUT,". $this->ReadPropertyInteger("OWDID") .",". $OutputNumber .",". $Value ."");
    }
    private function Send($Command) {

      //Zur 1Wire Controller Instanz senden
    	return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

    }

}
?>
