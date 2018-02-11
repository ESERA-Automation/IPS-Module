<?
class EseraWindmesser extends IPSModule {
    public function Create(){
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("CounterID", 0);
        $this->RegisterPropertyInteger("Impulses", 4);

        $this->RegisterVariableInteger("Counter", "Counter", "", 1);
        $this->RegisterVariableFloat("Wind_kmh", "Windspeed km/h", "~WindSpeed.kmh", 10);
        $this->RegisterVariableFloat("Wind_ms", "Windspeed m/s", "~WindSpeed.ms", 20);
		
        $this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounter($_IPS[\'TARGET\']);');		//Modultimer
		
		//Windspeed max
        $this->RegisterVariableFloat("Wind_kmh_max", "Windspeed km/h max Day", "~WindSpeed.kmh", 30);
        $this->RegisterVariableInteger("Wind_kmh_max_Zeit", "Windspeed km/h max Time", "~UnixTimestamp", 40);
		$this->RegisterTimer("DailyReset", 0, 'ResetWindspeedmaxDaily($_IPS[\'TARGET\']);');
		
		//Mittelwertberechnung
        $this->RegisterVariableFloat("Wind_kmh_slow", "Windspeed km/h average", "~WindSpeed.kmh", 11);
		$this->RegisterVariableInteger("interncount", "interncount", "", 100);		
		$this->RegisterVariableFloat("intern1", "intern1", "", 101);
		$this->RegisterVariableFloat("intern2", "intern2", "", 102);
		$this->RegisterVariableFloat("intern3", "intern3", "", 103);
		
		
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
        $this->SetTimerInterval("Refresh", 30 * 1000);
    }
    public function ReceiveData($JSONString) {
        // not implemented
    }
    public function RefreshCounter(){
       $this->calculate();
    }
	
	public function ResetPowerMeterDaily(){
        $this->SetDailyTimerInterval();
        SetValue($this->GetIDForIdent("Wind_kmh_max"), 0);
        SetValue($this->GetIDForIdent("Wind_kmh_max_Zeit"), 0);
    }
	
    private function Calculate(){
		//Windspeed berechnung
        $CounterOld = GetValue($this->GetIDForIdent("Counter"));
        $CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
        $delta = $CounterNew - $CounterOld;
        $Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
        $delta_Wind = ((($delta / $Factor) * 3600) / 1000);
        $delta_Wind_ms = $delta / $Factor;

        SetValue($this->GetIDForIdent("Counter"), $CounterNew);
        SetValue($this->GetIDForIdent("Wind_kmh"), $delta_Wind);
        SetValue($this->GetIDForIdent("Wind_ms"), $delta_Wind_ms);

		// Windspeed max
        $windspeedmax = GetValue($this->GetIDForIdent("Wind_kmh_max"));
        if ($delta_Wind > $windspeedmax){
            SetValue($this->GetIDForIdent("Wind_kmh_max"), $delta_Wind);
            SetValue($this->GetIDForIdent("Wind_kmh_max_Zeit"), time());
        }
		
		//Mittelwertberechnung
		//$windspeedslow = GetValue($this->GetIDForIdent("Wind_kmh_slow"));
		$intern_0 = $delta_Wind;
		$intern_1 = GetValue($this->GetIDForIdent("intern1"));
		$intern_2 = GetValue($this->GetIDForIdent("intern2"));
		$intern_3 = GetValue($this->GetIDForIdent("intern3"));
		$interncount = GetValue($this->GetIDForIdent("interncount"));
		$windspeedslow = $intern_0+$intern_1+$intern_2+$intern_3;			//Mittelwert berechnen
		
		$interncount = $interncount +1;
		if ($interncount >= 4 ){
			$interncount =4;
			SetValue($this->GetIDForIdent("Wind_kmh_slow"), $windspeedslow);	//Mittelwert in Variable schreiben	
		}
		SetValue($this->GetIDForIdent("interncount"), $interncount);
		SetValue($this->GetIDForIdent("intern1"), $intern_0);				//Wert 0 nach Wert 1 schieben
		SetValue($this->GetIDForIdent("intern2"), $intern_1);				//Wert 1 nach Wert 2 schieben
		SetValue($this->GetIDForIdent("intern3"), $intern_2);				//Wert 2 nach Wert 3 schieben
		
		
        // Only for debugging
        //$this->DebugMessage("Counter", "CounterOld: " . $CounterOld);
        //$this->DebugMessage("Counter", "CounterNew: " . $CounterNew);
        //$this->DebugMessage("Counter", "Delta: " . $delta);
        //$this->DebugMessage("Counter", "Delta Wind: " . $delta_Wind);
        //$this->DebugMessage("Counter", "Delta Wind ms: " . $delta_Wind_ms);
    }
    private function GetFactor($Impulses){
        switch ($Impulses){
            case 2:
              return (35);
            break;

            case 4:
              return (70);
            break;
        }
    }
    private function DebugMessage($Sender, $Message){
        $this->SendDebug($Sender, $Message, 0);
    }
}
?>
