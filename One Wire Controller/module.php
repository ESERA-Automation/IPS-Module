<?
class EseraOneWireController extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ConnectionType", 10);
		$this->RegisterPropertyString("DataOutputType", "OWD");
		$this->RegisterPropertyInteger("ControllerID", 1);
		$this->RegisterPropertyBoolean("SendKeepAlive", false);
		$this->RegisterPropertyInteger("SendKeepAliveInterval", 60);
		$this->RegisterPropertyBoolean("ReceiveKeepAlive", false);
		$this->RegisterPropertyInteger("ReceiveKeepAliveInterval", 0);

		$this->RegisterTimer("KeepAliveHeartbeatTimer", 0, 'ESERA_SendKeepAliveHeartbeat($_IPS[\'TARGET\']);');
		$this->RegisterTimer("SysInfoRequestTimer", 86400 * 1000, 'ESERA_GetSysInfo($_IPS[\'TARGET\']);');

	}
	public function Destroy(){
		//Never delete this line!
		parent::Destroy();

	}
	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();

		//Set Parent
		switch($this->ReadPropertyInteger("ConnectionType")) {
			case 10:
				$this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}"); //ClientSocket
				break;

			case 20:
				$this->ForceParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}"); //SerialPort
				break;

			default:
				throw new Exception("Invalid ConnectionType for Parent");
				break;
		}

	}
	public function ForwardData($JSONString){

		$data = json_decode($JSONString);

		$this->SendDebug("FWD", $data->Command, 0);

		$this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data->Command .chr(13))));

	}

	public function ConfigureDevice(){

		//SendKeepAlive ein-/ausschalten
		if ($this->ReadPropertyBoolean("SendKeepAlive")) {
			$KeepAliveInterval = $this->ReadPropertyInteger("SendKeepAliveInterval");
			//Checken ob der Intervallwert zwischen 60-240 liegt
			if ($KeepAliveInterval < 60) {
				$KeepAliveInterval = 60;
			} else if ($KeepAliveInterval > 240) {
				$KeepAliveInterval = 240;
			}
			$this->Send("SET,SYS,KALSEND,1");
			$this->Send("SET,SYS,KALSENDTIME,$KeepAliveInterval");
		} else {
			$this->Send("SET,SYS,KALSEND,0");
		}

		//ReceiveKeepAlive ein-/ausschalten
		if ($this->ReadPropertyBoolean("ReceiveKeepAlive")) {
			$KeepAliveInterval = $this->ReadPropertyInteger("ReceiveKeepAliveInterval");
			//Checken ob der Intervallwert zwischen 60-240 liegt
			if ($KeepAliveInterval < 60) {
				$KeepAliveInterval = 60;
			} else if ($KeepAliveInterval > 240) {
				$KeepAliveInterval = 240;
			}
			$this->SetTimerInterval("KeepAliveHeartbeatTimer", $KeepAliveInterval * 1000);
			$this->Send("SET,SYS,KALREC,1");
			$this->Send("SET,SYS,KALRECTIME,$KeepAliveInterval");
		} else {
			$this->Send("SET,SYS,KALREC,0");
			$this->SetTimerInterval("KeepAliveHeartbeatTimer", 0);
		}

		//Datenausgabe konfigurieren
		switch($this->ReadPropertyString("DataOutputType")) {
			case 'OWD':
				$this->Send("SET,OWB,OWDID,0");
				break;

			case 'ID':
				$this->Send("SET,OWB,OWDID,1");
				break;
		}

		$this->SaveToSRAM();

	}
	public function ReceiveData($JSONString) {

		$data = json_decode($JSONString);

		//Kontrollieren ob Buffer leer ist.
		$bufferData = $this->GetBuffer("DataBuffer");
		$bufferData .= $data->Buffer;

		$this->SendDebug("BufferIn", $bufferData, 0);

		$bufferParts = explode("\r\n", $bufferData);

		//Letzten Eintrag nicht auswerten, da dieser nicht vollständig ist.
		if(sizeof($bufferParts) > 1) {
			for($i=0; $i<sizeof($bufferParts)-1; $i++) {
				$this->SendDebug("Data", $bufferParts[$i], 0);
				$this->AnalyseData($bufferParts[$i]);
			}
		}

		$bufferData = $bufferParts[sizeof($bufferParts)-1];

		//Übriggebliebene Daten auf den Buffer schreiben
		$this->SetBuffer("DataBuffer", $bufferData);

		$this->SendDebug("BufferOut", $bufferData, 0);

	}
	private function AnalyseData($DataString) {

		$dataArray = explode("|", $DataString);

		$head = $dataArray[0]; //Name der übergebenen Variable
		$value = $dataArray[1]; //Daten der übergebenen Variable
		$type = SubStr($head, 2, 3);

		switch ($type) {
			case "ART":
			case "SER":
			case "INF":
			case "DEB":
			case "COU":
				$type = SubStr($head, 2, 5);
				break;

			case "CON":
				$type = SubStr($head, 2, 6);
				break;

			case "FW":
			case "HW":
				$type = SubStr($head, 2, 2);
				break;

			case "DAT":
			case "TIM":
				$type = SubStr($head, 2, 4);
				break;

			case "DS2":
				$type = SubStr($head, 2, 9);
				break;

			case "OWD":
				$headArray = explode("_", $head);
				$deviceNumber = intval(substr($headArray[1], 3));
      		
				if (sizeof($headArray) >= 3){
					$dataPoint = intval($headArray[2]);
				}
				else{
					$dataPoint = 0;
				}
				
				$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{E3BB8703-6388-48DA-AA85-8852CDEE152D}", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)), 0);
				$this->SendDataToChildren(json_encode(Array("DataID" => "{E3BB8703-6388-48DA-AA85-8852CDEE152D}", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)));
				return;

			case "SYS":
				$headArray = explode("_", $head);
				$deviceNumber = intval(substr($headArray[1], 3));
				if (sizeof($headArray) >= 3){
					$dataPoint = intval($headArray[2]);
				}
				else{
					$dataPoint = 0;
				}
				
				//geändert 10.08.2017 andrge (hinweis von ch. schrader)
				$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "SYS", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)), 0);
				$this->SendDataToChildren(json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "SYS", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)));
				
				//$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)), 0);
				//$this->SendDataToChildren(json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceNumber" => $deviceNumber, "DataPoint" => $dataPoint, "Value" => $value)));
				return;

			default:
				if(SubStr($head, 18, 1) == '_' || StrLen($head) == 18) {
					$type = "OWDID";
				}
		}

		$variableType = $this->GetVariableType($type);
		$variablenID = 0;

		//Erstellen der Variablen
		switch ($variableType){
			case 1:
				$variablenID = $this->RegisterVariableInteger($head, $head);
				break;

			case 3:
				$variablenID = $this->RegisterVariableString($head, $head);
				break;

			default:
				$this->SendDebug("RegisterVariable", "Unbekannter Variablentyp", 0);
		}

		//Wert setzen
		if ($variablenID !== 0) {
			SetValue($variablenID, $value);
		}

		//Wenn Time dann automatisch Zeit korrigieren
		if ($type == "TIME") {
			$this->FixTime($value);
		}

	}
	//Systeminfo abfragen
	public function SendKeepAliveHeartbeat() {
		$this->Send("".$this->ReadPropertyInteger("ControllerID")."_KAL|1");
	}
	//Systeminfo abfragen
	public function GetSysInfo() {
		$this->Send("GET,SYS,INFO");
	}
	//Systemsettings abfragen
	public function GetSysSetting() {
		$this->Send("GET,SYS,SETTING");
	}
	 //Gefundene 1-Wire Bauteile speichern
	public function SaveOneWireParts() {
		$this->Send("SET,OWB,SAVE");
	}
	 //Gefundene 1-Wire Bauteile speichern
	public function SaveToSRAM() {
		$this->Send("SET,SYS,SAVE");
	}
	//Zeit fixen sofern Abweichung zu stark
	private function FixTime($TimeString){

		$timeArray = explode(":", $TimeString);
		$difference = mktime($timeArray[0], $timeArray[1], $timeArray[2]) - time();
		if ($difference > 300 || $difference < 0) {
			$this->Send("SET,SYS,TIME,".date('H.i.s'));
			$this->SendDebug("FixTime", "Uhrzeit 1-Wire Controller wurde angepasst. Differenz was ".$difference." Sekunden", 0);
		} else {
			$this->SendDebug("FixTime", "Uhrzeit 1-Wire Controller stimmt. Keine Änderungen vorgenommen.", 0);
		}

	}
	//Liefert den Typ der Variable abhängig von der empfangenen Daten
	private function GetVariableType($Type) {

		switch($Type){
			//Integer
			case "KAL":
			case "ARTNO":
			case "CONTNO":
			case "DATA":
			case "DEBUG":
			case "COUNT":
			case "DS2408INV":
			case "ERR":
			case "OWDID":
				return 1;

			//String
			case "EVT":
			case "HW":
			case "SERNO":
			case "FW":
			case "DATE":
			case "TIME":
				return 3;

			//Unbestimmt
			default:
				$this->SendDebug("VariableType", "Datensatz ist: " . $Type, 0);
				return false;
		}
	}
	public function GetConfigurationForParent() {

		//Vordefiniertes Setup der seriellen Schnittstelle
		if ($this->ReadPropertyInteger("ConnectionType") == 20) {
			return "{\"BaudRate\": \"19200\", \"StopBits\": \"1\", \"DataBits\": \"8\", \"Parity\": \"None\"}";
		} else if ($this->ReadPropertyInteger("ConnectionType") == 10) {
			return "{\"Port\": \"5000\"}";
		} else {
			return "";
		}

	}
	private function Send($Command) {

		//Zur I/O Instanz senden
		return $this->ForwardData(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Command" => $Command)));

	}
}
?>
