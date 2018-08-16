<?
class AudioMaxServer extends IPSModule {
//class EseraOneWireController extends IPSModule {

	public function Create(){
		//Never delete this line!
		parent::Create();
 		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		
		$this->CreateVariableProfileVolume("ESERA:Volume",1," dB",40,0,1,2,"%");
		$this->CreateVariableProfileGain("ESERA:Gain",1," dB",0,15,1,2,"%");
	    $this->CreateVariableProfileTone("ESERA:Tone",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfileBalance("ESERA:Balance",1," dB",0,15,1,2,"%");
		$this->CreateVariableProfileMute(("ESERA:Mute",3,"",0,1,0,0,"");
		/*
		for($i = 1; $i <= 2; $i++){
    			$this->RegisterVariableinteger("volume".$i, "Volume".$i, "ESERA.Volume");			
    			$this->EnableAction("ampOut".$i);
				
				$this->RegisterVariableinteger("gain".$i, "Gain".$i, "ESERA.Gain");
    			$this->EnableAction("gain".$i);
				
				$this->RegisterVariableinteger("bass".$i, "Bass".$i, "ESERA.Tone");
    			$this->EnableAction("bass".$i);
				
				$this->RegisterVariableinteger("mid".$i, "Middle".$i, "ESERA.Tone");
    			$this->EnableAction("mid".$i);
				
				$this->RegisterVariableinteger("treble".$i, "Treble".$i, "ESERA.Tone");
    			$this->EnableAction("treble".$i);

				$this->RegisterVariableinteger("balance".$i, "Balance".$i, "Balance");
    			$this->EnableAction("balance".$i);		
							
    			$this->RegisterVariableBoolean("ampOut".$i, "Amplifier on-off".$i, "~Switch");
    			$this->EnableAction("ampOut".$i);

    			$this->RegisterVariableBoolean("mute".$i, "Mute Output".$i, "Mute");
    			$this->EnableAction("mute".$i);				
    		}
*/
		$this->RegisterVariableboolean($type,"AudioMax Power","~Switch",1);
        $this->EnableAction("pwr".$i);
		

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
		} 
		else {
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
		} 
		else {
			$this->Send("SET,SYS,KALREC,0");
			$this->SetTimerInterval("KeepAliveHeartbeatTimer", 0);
		}
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
				    
		switch ($head) {
			case "EVT":	
		       $this->SendDebug("type", $type, 0);			   	
			   $this->SendDebug("data", $data, 0);
			   
				switch ($type) {
					case "KAL":		
					break;			
				}
				$variableType = $this->GetVariableType($type);
				$variablenID = 0;

				//Erstellen der Variablen
				switch ($variableType){
					case 1:
					    if ($type == "KAL"){
						$variablenID = $this->RegisterVariableboolean($head,"Heartbeat AudioMax-Server","",100);
						}
						break;
					case 2:
						//$variablenID = $this->RegisterVariableboolean($type, $type);
						if ($type == "PWR"){
							$variablenID = $this->RegisterVariableboolean($type,"AudioMax Power","~Switch",1);
						}
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

				
			case "SYS": 	   
		       $this->SendDebug("type", $type, 0);			   	
			   $this->SendDebug("data", $data, 0);
			   
				switch ($type) {
					case "KAL":
					case "PWR":
					case "DEBUG":
					case "ECHO":
					case "PUSHBUTTON":
					case "AUTOSTART":			
					break;
					
					case "SW":
					case "HW":
					case "FW":
					break;
				}
				
				$variableType = $this->GetVariableType($type);
				$variablenID = 0;

				//Erstellen der Variablen
				switch ($variableType){
					case 1:
						$variablenID = $this->RegisterVariableInteger($type, $type);
						break;
					case 2:
						//$variablenID = $this->RegisterVariableboolean($type, $type);
						if ($type == "PWR"){
							$variablenID = $this->RegisterVariableboolean($type,"AudioMax Power","~Switch",1);
						}
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
				//$this->SendDebug("SendToDevice", json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "AUDIO", "RoomNumber" => $RoomNumber, "DataType" => $dataType, "Value" => $value)), 0);
				//$this->SendDataToChildren(json_encode(Array("DataID" => "{6B6E9D9E-4541-48CD-9F01-EFE52ACB2530}", "DeviceType" => "AUDIO", "RoomNumber" => $RoomNumber, "DataType" => $dataType, "Value" => $value)));
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
	//System Firmware abfragen
	public function GetSysAMFW() {
		$this->Send("GET,SYS,FW");
	}
	//System Hardware abfragen
	public function GetSysAMHW() {
		$this->Send("GET,SYS,HW");
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
			case "DEBUG":
			case "ECHO":
			case "PUSHBUTTON":
			case "AUTOSTART":
			case "HW":
			case "SW":
			case "FW":
				return 1;

			//Boolean
			case "PWR":
			    return 2;
				
			//String
			case "SERNO":
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
