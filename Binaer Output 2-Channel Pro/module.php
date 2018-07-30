<?
class EseraDigitalOutIn2Channel extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("OWDID", 1);
		
		for($i = 1; $i <= 2; $i++){
			$this->RegisterVariableBoolean("Input".$i, "Input ".$i, "~Switch",1);
		}

		for($i = 1; $i <= 2; $i++){
			$this->RegisterVariableBoolean("Output".$i, "Output ".$i, "~Switch",1);
			$this->EnableAction("Output".$i);
		}

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
		$this->SendDebug("ESERA-DualDoutDI", $data->Value, 0);

		if ($this->ReadPropertyInteger("OWDID") == $data->DeviceNumber) {
			if ($data->DataPoint == 1) {
				$value = intval($data->Value, 10);
				for ($i = 1; $i <= 2; $i++){
					SetValue($this->GetIDForIdent("Input".$i), ($value >> ($i-1)) & 0x01);
				}
			} else if ($data->DataPoint == 3) {
				$value = intval($data->Value, 10);
				for ($i = 1; $i <= 2; $i++){
					SetValue($this->GetIDForIdent("Output".$i), ($value >> ($i-1)) & 0x1);
				}
			}
		}
	}
	
	public function RequestAction($Ident, $Value) {
		switch($Ident) {
			case "Output1":
			case "Output2":
				$this->SetDigitalOutput(SubStr($Ident, 6, 1), $Value);
				break;
			default:
				throw new Exception("Invalid ident");
		}
	}
	public function SetDigitalOutput(int $OutputNumber, int $Value) {

		$OutputNumber = $OutputNumber - 1;
		$this->Send("SET,OWD,OUT,". $this->ReadPropertyInteger("OWDID") .",". $OutputNumber .",". $Value ."");
	}

	public function SetDigitalOutputPort(int $Value) {
		$OutputNumber = $OutputNumber - 1;
		$this->Send("SET,OWD,OUTH,". $this->ReadPropertyInteger("OWDID") .",". $Value ."");
	}
	
	private function Send($Command) {

		//Zur 1Wire Coontroller Instanz senden
		return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

	}
}
?>
