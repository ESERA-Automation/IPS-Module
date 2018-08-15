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
			$this->Send("SET,SYS,KALSEND,1");
			//$this->Send("SET,SYS,KALSENDTIME,$KeepAliveInterval");
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
			//$this->Send("SET,SYS,KALRECTIME,$KeepAliveInterval");
		} else {
			$this->Send("SET,SYS,KALREC,0");
			$this->SetTimerInterval("KeepAliveHeartbeatTimer", 0);
		}

		//Datenausgabe konfigurieren
		/*switch($this->ReadPropertyString("DataOutputType")) {
			case 'OWD':
				$this->Send("SET,OWB,OWDID,0");
				break;

			case 'ID':
				$this->Send("SET,OWB,OWDID,1");
				break;
		}

		$this->SaveToSRAM();
*/
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
		$dataArray = explode(",", $DataString);

		$head = $dataArray[0]; 			//Name der übergebenen Variable
		   $this->SendDebug("head", $head, 0);
		
		//SYS Variablen
		$type = $dataArray[1]; 		//Daten der übergebenen Variable		   
		$data = $dataArray[2]; 		//Daten der übergebenen Variable	
		
		//AUDIO Variablen
		$RoomNumber = $dataArray[1]; 		//Daten der übergebenen Variable
		$dataType = $dataArray[2]; 		//Daten der übergebenen Variable
		$value = $dataArray[3]; 		//Daten der übergebenen Variable

				   
		/*
		$type = SubStr($head, 1, 3);			//vorher 2,3
           $this->SendDebug("type", $type, 0);
		*/   
		   
		switch ($head) {
			case "KAL":	
			break;
			
			case "SYS": 	   
		       $this->SendDebug("type", $type, 0);			   	
			   $this->SendDebug("data", $data, 0);
			   
				switch ($value1) {
					case "KAL":
					case "PWR":
					case "DEBUG":
					case "ECHO":
					case "PUSHBUTTON":
					case "AUTOSTART":			
					//$variableType = 1;
					break;
					
					case "SW":
					case "HW":
					case "FW":
					//$variableType = 3;
					break;
				}
				
				$variableType = $this->GetVariableType($type);
				$variablenID = 0;

				//Erstellen der Variablen
				switch ($variableType){
					case 1:
						$variablenID = $this->RegisterVariableInteger($type, $type);
						break;

					case 3:
						$variablenID = $this->RegisterVariableString($type, $type);
						break;

					default:
						$this->SendDebug("RegisterVariable", "Unbekannter Variablentyp", 0);
				}			

				//Wert setzen
				if ($variablenID !== 0) {
					SetValue($variablenID, $data);
				}	
				
				
			    return;	
				
			
			case "AUDIO":						
		           $this->SendDebug("roomnumber", $RoomNumber, 0);
		           $this->SendDebug("datatype", $dataType, 0);
		           $this->SendDebug("value", $value, 0);				
				
				//geändert 10.08.2017 andrge (hinweis von ch. schrader)		
				$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "AUDIO", "RoomNumber" => $RoomNumber, "DataType" => $dataType, "Value" => $value)), 0);
				$this->SendDataToChildren(json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "AUDIO", "RoomNumber" => $RoomNumber, "DataType" => $dataType, "Value" => $value)));
				return;

		}

	}
	
	
	
	//KAL Senden
	public function SendKeepAliveHeartbeat() {
		$this->Send("SET,KAL|1");
	}
	
	//GET Funktionen
	//--------------------------------------------	
	//System Debug Mode abfragen
	public function GetSysAMDebug() {
		$this->Send("GET,SYS,DEBUG");
	}
	//System Echo Mode abfragen
	public function GetSysAMEcho() {
		$this->Send("GET,SYS,ECHO");
	}
	//System Pushbuttom Mode abfragen
	public function GetSysAMPushbutton() {
		$this->Send("GET,SYS,PUSHBUTTON");
	}
	//System Autostart Mode abfragen
	public function GetSysAMAutostart() {
		$this->Send("GET,SYS,AUTOSTART");
	}
	//System Power Status abfragen
	public function GetSysAMPwr() {
		$this->Send("GET,SYS,PWR");
	}
		
	//SET Funktionen
	//--------------------------------------------
	//System Debug Mode setzen
	public function SetSysAMDebug() {
		$this->Send("SET,SYS,DEBUG");
	}
	//System Echo Mode setzen
	public function SetSysAMEcho() {
		$this->Send("SET,SYS,ECHO");
	}
	//System Pushbuttom Mode setzen
	public function SetSysAMPushbutton() {
		$this->Send("SET,SYS,PUSHBUTTON");
	}
	//System Autostart Mode setzen
	public function SetSysAMAutostart() {
		$this->Send("SET,SYS,AUTOSTART");
	}
	//System Power Status setzen
	public function SetSysAMPwr() {
		$this->Send("SET,SYS,PWR");
	}
	//System KAL Mode setzen
	public function SetSysAMKal() {
		$this->Send("SET,SYS,KAL");

	}

	
	//Liefert den Typ der Variable abhängig von der empfangenen Daten
	private function GetVariableType($Type) {

		switch($Type){
			//Integer
			case "KAL":
			case "PWR":
			case "DEBUG":
			case "ECHO":
			case "PUSHBUTTON":
			case "AUTOSTART":
				return 1;

			//String
			case "HW":
			case "SERNO":
			case "SW":
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
