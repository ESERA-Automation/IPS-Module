<?
class EseraShutterPro extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->CreateVariableProfileShutterV4();
		$this->RegisterPropertyInteger("OWDID", 1);
    $this->RegisterVariableInteger("Input", "Input", "ESERA.ShutterV4"); // ESERA-Shutter
		$this->RegisterVariableInteger("Output", "Output", "ESERA.ShutterV4");
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
		$this->SetReceiveDataFilter(".*\"DeviceNumber\":". $this->ReadPropertyInteger("OWDID") .".*");
	}
	public function ReceiveData($JSONString) {
		$data = json_decode($JSONString);
		$this->SendDebug("ESERA-SHTV4", $data->DataPoint . " | " . $data->Value, 0);
		if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
			if ($data->DataPoint == 1) {
				$value = intval($data->Value, 10);
				SetValue($this->GetIDForIdent("Input"), $value);
				if ($value != 0){
					$this->SetBuffer("ManualMove", time());
					$this->SendDebug("ESERA-SHTV4", "SetBuffer ManualMove");
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
	private function CreateVariableProfileShutterV4() {
		if (!IPS_VariableProfileExists("ESERA.ShutterV4")) {
			IPS_CreateVariableProfile("ESERA.ShutterV4", 1);
			IPS_SetVariableProfileValues("ESERA.ShutterV4", 1, 3, 0);
			IPS_SetVariableProfileDigits("ESERA.ShutterV4", 0);
			IPS_SetVariableProfileIcon("ESERA.ShutterV4", "Shutter");
			IPS_SetVariableProfileAssociation("ESERA.ShutterV4", 0, "StandBy", "", -1);
			IPS_SetVariableProfileAssociation("ESERA.ShutterV4", 1, "Down", "HollowLargeArrowDown", -1);
			IPS_SetVariableProfileAssociation("ESERA.ShutterV4", 2, "Up", "HollowLargeArrowUp", -1);
			IPS_SetVariableProfileAssociation("ESERA.ShutterV4", 3, "Stop", "", 0x0000FF);
		}
	}
}
?>
