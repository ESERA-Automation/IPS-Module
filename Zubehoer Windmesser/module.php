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
        $this->RegisterVariableFloat("Wind_kmh", "Windspeed km/h", "~WindSpeed.kmh", 2);
        $this->RegisterVariableFloat("Wind_ms", "Windspeed m/s", "~WindSpeed.ms", 3);

        $this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounter($_IPS[\'TARGET\']);');
		
        $this->RegisterVariableFloat("Wind_kmh_max", "Windspeed km/h max Day", "~WindSpeed.kmh", 5);
        $this->RegisterVariableInteger("Wind_kmh_max_Zeit", "Windspeed km/h max, Time", "~UnixTimestamp", 6);
		
		$this->RegisterTimer("DailyReset", 0, 'ResetWindspeedmaxDaily($_IPS[\'TARGET\']);');
		
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
		
        // Only for debugging
        $this->DebugMessage("Counter", "CounterOld: " . $CounterOld);
        $this->DebugMessage("Counter", "CounterNew: " . $CounterNew);
        $this->DebugMessage("Counter", "Delta: " . $delta);
        $this->DebugMessage("Counter", "Delta Wind: " . $delta_Wind);
        $this->DebugMessage("Counter", "Delta Wind ms: " . $delta_Wind_ms);
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
