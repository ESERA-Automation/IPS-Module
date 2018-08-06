<?
class EseraShutterPro extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->CreateVariableProfileShutterPro();
		$this->RegisterPropertyInteger("OWDID", 1);
        $this->RegisterVariableInteger("Input", "Input", "ESERA.ShutterPro"); // ESERA-Shutter
		$this->RegisterVariableInteger("Output", "Output", "ESERA.ShutterPro");
		$this->EnableAction("Output");
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
		//$this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .".*");
		$this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .",.*");
	}
	
	public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
		//$this->SendDebug("ESERA-SHTPro", $data->DeviceNumber . " | " . $data->DataPoint . " | " . $data->Value, 0);
		$this->SendDebug("ESERA-SHTPro", $data->DataPoint . " | " . $data->Value, 0);
		if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
			if ($data->DataPoint == 1) {
				$value = intval($data->Value, 10);
				SetValue($this->GetIDForIdent("Input"), $value);
				if ($value != 0){
					$this->SetBuffer("ManualMove", time());
					$this->SendDebug("ESERA-SHTPro", "SetBuffer ManualMove", 0);
				}
			}
			else if ($data->DataPoint == 3) {
				$value = intval($data->Value, 10);
				SetValue($this->GetIDForIdent("Output"), $value);
			}
		}
	}
	public function RequestAction($Ident, $Value) {
		switch($Ident) {
			case "Output":
			  if ($Value == 0) $Value = 3;
				$this->SetShutter(SubStr($Ident, 6, 1), $Value);
				break;
			default:
				throw new Exception("Invalid ident");
		}
	}
	public function SetShutter(int $OutputNumber, int $Value) {
		$OutputNumber = $OutputNumber - 1;
		$this->Send("SET,OWD,SHT,". $this->ReadPropertyInteger("OWDID") .",". $Value ."");
	}
	
	private function Send($Command) {
		//Zur 1Wire Coontroller Instanz senden
		return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));
	}
	private function CreateVariableProfileShutterPro() {
		if (!IPS_VariableProfileExists("ESERA.ShutterPro")) {
			IPS_CreateVariableProfile("ESERA.ShutterPro", 1);
			IPS_SetVariableProfileValues("ESERA.ShutterPro", 1, 3, 0);
			IPS_SetVariableProfileDigits("ESERA.ShutterPro", 0);
			IPS_SetVariableProfileIcon("ESERA.ShutterPro", "Shutter");
			IPS_SetVariableProfileAssociation("ESERA.ShutterPro", 0, "StandBy", "", -1);
			IPS_SetVariableProfileAssociation("ESERA.ShutterPro", 1, "Down", "HollowLargeArrowDown", 0x10BA00);
			IPS_SetVariableProfileAssociation("ESERA.ShutterPro", 2, "Up", "HollowLargeArrowUp", 0x10BA00);
			IPS_SetVariableProfileAssociation("ESERA.ShutterPro", 3, "Stop", "", 0x0000FF);
		}
	}
}
?>
