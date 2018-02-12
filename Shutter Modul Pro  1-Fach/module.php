<?
class EseraShutterPro1Fach extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->CreateVariableProfileShutter();

        $this->RegisterPropertyInteger("OWDID", 1);
        $this->RegisterVariableInteger("Shutter", "Shutter", "ESERA.Shutter", 1);
        $this->EnableAction("Shutter");

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
        $this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .".*");

    }
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("ESERA-Shutter1Fach", "DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

        // Only for Debug
        if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
            if ($data->DataPoint == 3) {
				$value = intval($data->Value, 10);
                if ($value = 1){
					SetValue($this->GetIDForIdent("Shutter"), 1);
                }
				if ($value = 2){
					SetValue($this->GetIDForIdent("Shutter"), 2);
				}
				if (Value = 0 or Value = 3){
					SetValue($this->GetIDForIdent("Shutter"), 3);
				}
            }
        }
    }


	
	
    public function RequestAction($Ident, $Value) {
      $this->MoveShutter($Value);
    }

    public function MoveShutter(int $Value) {

      $this->Send("SET,OWD,SHT,". $this->ReadPropertyInteger("OWDID") .",". $Value ."");

    }

    private function Send($Command) {

      //Zur 1Wire Controller Instanz senden
    	return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

    }

    private function CreateVariableProfileShutter() {
		    if (!IPS_VariableProfileExists("ESERA.Shutter")) {
			       IPS_CreateVariableProfile("ESERA.Shutter", 1);
			       IPS_SetVariableProfileValues("ESERA.Shutter", 1, 3, 0);
			       IPS_SetVariableProfileDigits("ESERA.Shutter", 0);
			       IPS_SetVariableProfileIcon("ESERA.Shutter", "Shutter");
             IPS_SetVariableProfileAssociation("ESERA.Shutter", 1, "Down", "HollowLargeArrowDown", -1);
             IPS_SetVariableProfileAssociation("ESERA.Shutter", 2, "Up", "HollowLargeArrowUp", -1);
             IPS_SetVariableProfileAssociation("ESERA.Shutter", 3, "Stop", "", 0x0000FF);
		    }
	  }
}
?>
