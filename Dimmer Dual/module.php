<?
class EseraDualDimmer extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->CreateVariableProfileDimmer1();
		$this->RegisterPropertyInteger("OWDID", 1);
		$this->RegisterVariableBoolean("Channel1PushButton", "Channel 1 Input", "~Switch");
		$this->RegisterVariableBoolean("Channel2PushButton", "Channel 2 Input", "~Switch");
		$this->RegisterVariableBoolean("Channel1ModuleButton", "Channel 1 Module Button", "~Switch");
		$this->RegisterVariableBoolean("Channel2ModuleButton", "Channel 2 Module Button", "~Switch");
        $this->RegisterVariableInteger("Channel1Value", "Channel 1 Output", "ESERA.Dimmer1");
		$this->RegisterVariableInteger("Channel2Value", "Channel 2 Output", "ESERA.Dimmer1");
		$this->EnableAction("Channel1Value");
		$this->EnableAction("Channel2Value");
		$this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); // 1Wire Controller
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
		$this->SendDebug("ESERA-DualDim", $data->DataPoint . " | " . $data->Value, 0);
		if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
			if ($data->DataPoint == 1) {
				$value = intval($data->Value, 10);
			}
			else if ($data->DataPoint == 2) {
				$value = intval($data->Value, 10);
				$Channel1PushButton = ($value >> 0) & 0x01;
				$Channel2PushButton = ($value >> 1) & 0x01;
				$Channel1ModuleButton = ($value >> 2) & 0x01;
				$Channel2ModuleButton = ($value >> 3) & 0x01;

				SetValue($this->GetIDForIdent("Channel1PushButton"), $Channel1PushButton);
				SetValue($this->GetIDForIdent("Channel2PushButton"), $Channel2PushButton);
				SetValue($this->GetIDForIdent("Channel1ModuleButton"), $Channel1ModuleButton);
				SetValue($this->GetIDForIdent("Channel2ModuleButton"), $Channel2ModuleButton);
			}
			else if ($data->DataPoint == 3) {
				$value = intval($data->Value, 10);
				SetValue($this->GetIDForIdent("Channel1Value"), $value);
			}
			else if ($data->DataPoint == 4) {
				$value = intval($data->Value, 10);
				SetValue($this->GetIDForIdent("Channel2Value"), $value);
			}
		}
	}
	public function RequestAction($Ident, $Value) {
		switch($Ident) {
			case "Channel1Value":
				$this->SetDimmer(1, $Value);
			break;

			case "Channel2Value":
				$this->SetDimmer(2, $Value);
			break;

			default:
				throw new Exception("Invalid ident");
		}
	}
	public function SetDimmer(int $OutputNumber, int $Value) {
		$this->SendDebug("ESERA-DualDim", "TRANSMIT OUTPUT " . $OutputNumber . " | VALUE " . $Value, 0);
		$this->Send("SET,OWD,DIM,". $this->ReadPropertyInteger("OWDID") .",". $OutputNumber . "," . $Value ."");
	}
	private function Send($Command) {
		//Zur 1Wire Coontroller Instanz senden
		return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));
	}
	private function CreateVariableProfileDimmer1() {
		if (!IPS_VariableProfileExists("ESERA.Dimmer1")) {
			IPS_CreateVariableProfile("ESERA.Dimmer1", 1);
			IPS_SetVariableProfileValues("ESERA.Dimmer1", 0, 31, 1);
			IPS_SetVariableProfileDigits("ESERA.Dimmer1", 0);
			IPS_SetVariableProfileIcon("ESERA.Dimmer1", "Intensity");
		}
	}
}
?>
