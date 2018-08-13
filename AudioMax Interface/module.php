<?
class AudioMaxServer extends IPSModule {
//class EseraOneWireController extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("ConnectionType", 10);
		$this->RegisterPropertyString("DataOutputType", "AUDIO");
		$this->RegisterPropertyInteger("AudioMaxID", 1);
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
				$this->ForceParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}"); //SerialPort				
				break;			
			
			case 20:
				$this->ForceParent("{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}"); //ClientSocket
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
			$this->Send("SET,KAL,1");										//Befehlsstring des AudioMax Server
			//$this->Send("SET,SYS,KALSENDTIME,$KeepAliveInterval");
		} else {
			$this->Send("SET,KAL,0");										//Befehlsstring des AudioMax Server
		}
/*
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
			$this->Send("SET;SYS;KALREC;1");
			$this->Send("SET;SYS;KALRECTIME;$KeepAliveInterval");
		} else {
			$this->Send("SET;SYS;KALREC;0");
			$this->SetTimerInterval("KeepAliveHeartbeatTimer", 0);
		}
*/
		
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
	
	//Daten von Schnittstelle auswerten und auf interne IPS Variablen schreiben
	private function AnalyseData($DataString) {

		$dataArray = explode("|", $DataString);

		$head = $dataArray[0]; //Name der übergebenen Variable
		$value = $dataArray[1]; //Daten der übergebenen Variable
		$type = SubStr($head, 2, 3);

		switch ($type) {
			case "KAL":
			case "DEBUG":
			case "ECHO":
			case "PUSHBUTTON":
			case "AUTOSTART":
				$type = SubStr($head, 2, 5);
				break;

			case "FW":
			case "HW":
				$type = SubStr($head, 2, 2);
				break;

			case "AUDIO":
				$headArray = explode("_", $head);
				$deviceNumber = intval(substr($headArray[1], 3));
				if (sizeof($headArray) >= 3){
					$dataPoint = intval($headArray[2]);
				}
				else{
					$dataPoint = 0;
				}

				$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{4DF6D73D-8592-40DD-87FD-54D14F36692A}", "DeviceType" => "AUDIO", "DataSource:" . $data->DataSource . " | DataRoom:" . $data->DataRoom . " | DataType:" . $data->DataType . " | Value: " . $data->Value););
				$this->SendDataToChildren(json_encode(Array("DataID" => "{4DF6D73D-8592-40DD-87FD-54D14F36692A}", "DeviceType" => "AUDIO", "DataSource:" . $data->DataSource . " | DataRoom:" . $data->DataRoom . " | DataType:" . $data->DataType . " | Value: " . $data->Value);)));
				
				return;

			default:
			/*
				if(SubStr($head, 18, 1) == '_' || StrLen($head) == 18) {
					$type = "OWDID";
				}
			*/
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
	
	//ab hier Kommunikation zwischen IPS und AudioMax Server
	//------------------------------------------------------
	
	//System Herzschlag senden
	public function SendKeepAliveHeartbeat() {
		$this->Send("".$this->ReadPropertyInteger("AudioMaxID")."_KAL|1");
	}
	
	//System Debug Mode abfragen
	public function GetSysDebug() {
		$this->Send("GET,SYS,DEBUG");
	}
	//System Echo Mode abfragen
	public function GetSysEcho() {
		$this->Send("GET,SYS,ECHO");
	}
	//System Pushbuttom Mode abfragen
	public function GetSysPushbutton() {
		$this->Send("SET,SYS,PUSHBUTTON");
	}
	//System Autostart Mode abfragen
	public function GetSysAutostart() {
		$this->Send("GET,SYS,AUTOSTART");
	}
	//System Power Status abfragen
	public function GetSysKal() {
		$this->Send("GET,SYS,PWR");
	}
	//System KAL Mode abfragen
	public function GetSysKal() {
		$this->Send("GET,KAL");
	}	
	

	//Liefert den Typ der Variable abhängig von der empfangenen Daten
	private function GetVariableType($Type) {

		switch($Type){
			//Integer
			case "KAL":
			case "DEBUG":
			case "ECHO":
			case "PUSHBUTTON":
			case "AUTOSTART":
			case "PWR":
				return 1;

			//String
			case "ARTIKELNUMMER":
			case "HW":
			case "FW":
				return 3;

			//Unbestimmt
			default:
				$this->SendDebug("VariableType", "Datensatz ist: " . $Type, 0);
				return false;
		}
	}
	
	public function GetConfigurationForParent() {

		//Vordefiniertes Setup der seriellen Schnittstelle
		if ($this->ReadPropertyInteger("ConnectionType") == 10) {
			return "{\"BaudRate\": \"19200\", \"StopBits\": \"1\", \"DataBits\": \"8\", \"Parity\": \"None\"}";
		} else if ($this->ReadPropertyInteger("ConnectionType") == 20) {
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
