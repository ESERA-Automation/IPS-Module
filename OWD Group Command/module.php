<?
class OWDGroupCommand extends IPSModule {

    public function Create(){
        //Never delete this line!
        parent::Create();

        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        //$this->RegisterPropertyInteger("SYS", 0);
   
        $this->ConnectParent("{FCABCDA7-3A57-657D-95FD-9324738A77B9}"); //1Wire Controller
		
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

	
	public function ApplyChanges(){
     //Never delete this line!
     parent::ApplyChanges();

     $this->SetReceiveDataFilter(".*\"DeviceType\":\"GRP\".*");
	}
	
	
    public function ReceiveData($JSONString) {

        $data = json_decode($JSONString);
        $this->SendDebug("OWDGroupCommand", "Number:" . $data->GrpNumber . " | DataPoint:" . $data->DataPoint . " | Value: " . $data->Value, 0);

		/*


		if ($data->DeviceNumber == 3){
		   $value = $data->Value / 100;
		   SetValue($this->GetIDForIdent("AnalogOut"), $value);
		}
		*/
    }

	
    public function RequestAction($Ident, $Value) {

  			default:
  				throw new Exception("Invalid ident");
  		}
  	}

/*
    public function SetSysOutput(int $OutputNumber, int $Value) {
  		$OutputNumber = $OutputNumber;
  		$this->Send("SET,SYS,OUT,". $OutputNumber .",". $Value ."");
  	}
*/
	//Gruppenbefehle
	public function SetGroupOut(int $GRPNumber, int $Value) {
		$this->Send("SET,OWD,GRP,". $GRPNumber .",". $Value ."");
		//$this->Send("SET,OWD,GRP,". $GroupNumber .","."SHT".",". $Value ."");
		//$this->SendDebug("OWDGroupCommand", "GruppenNumber:" . $data->$GRPNumber . " | Function: SHT| Value: " . $data->Value, 0);
	}


    private function Send($Command) {

      //Zur 1Wire Controller Instanz senden
    	return $this->SendDataToParent(json_encode(Array("DataID" => "{EA53E045-B4EF-4035-B0CD-699B8731F193}", "Command" => $Command . chr(13))));

    }
}
?>
