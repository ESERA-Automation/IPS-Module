<?
class EseraStromzaehler extends IPSModule {
    public function Create(){
        //Never delete this line!
        parent::Create();
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("CounterID", 0);
        $this->RegisterPropertyInteger("Impulses", 1000);
        $this->RegisterPropertyInteger("AnnualLimit", 1000);
        $this->RegisterPropertyInteger("LimitActive", 100);
        $this->RegisterVariableInteger("Counter", "Counter", "", 1);
        $this->RegisterVariableFloat("Leistung", "Leistung", "~Electricity", 2);
        $this->RegisterVariableInteger("TagCounter", "Counter Tag", "", 3);
        $this->RegisterVariableFloat("LeistungTag", "Leistung Tag", "~Electricity", 4);
        $this->RegisterVariableFloat("LeistungVortag", "Leistung Vortag", "~Electricity", 5);
        $this->RegisterVariableInteger("MonatCounter", "Counter Monat", "", 6);
        $this->RegisterVariableFloat("LeistungMonat", "Leistung Monat", "~Electricity", 7);
        $this->RegisterVariableFloat("LeistungVormonat", "Leistung Vormonat", "~Electricity", 8);
        $this->RegisterVariableInteger("JahrCounter", "Counter Jahr", "", 9);
        $this->RegisterVariableFloat("LeistungJahr", "Leistung Jahr", "~Electricity", 10);
        $this->RegisterVariableFloat("LeistungVorjahr", "Leistung Vorjahr", "~Electricity", 11);
        $this->RegisterVariableFloat("MaxLeistung", "Maximal Tag", "~Electricity", 12);
        $this->RegisterVariableInteger("MaxLeistungZeit", "Maximal Tag Zeit", "~UnixTimestamp", 13);
        $this->RegisterVariableBoolean("AnnualLimit", "Jahreslimit", "", 14);
        $this->RegisterVariableBoolean("Betrieb", "Betrieb", "", 15);
        $this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounter($_IPS[\'TARGET\']);');   
        $this->RegisterTimer("DailyReset", 0, 'ESERA_ResetPowerMeterDaily($_IPS[\'TARGET\']);');
        $this->RegisterTimer("MonthlyReset", 0, 'ESERA_ResetPowerMeterMonthly($_IPS[\'TARGET\']);');
        $this->RegisterTimer("YearlyReset", 0, 'ESERA_ResetPowerMeterYearly($_IPS[\'TARGET\']);');
    }
    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
    }
    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
        $this->SetTimerInterval("Refresh", 180 * 1000);
        $this->SetDailyTimerInterval();
        $this->SetMonthlyTimerInterval();
        $this->SetYearlyTimerInterval();    
    }
    public function ReceiveData($JSONString) {
        // not implemented   
    }
    public function RefreshCounter(){
       $this->calculate();   
    }
    public function ResetPowerMeterDaily(){
        $this->SetDailyTimerInterval();
	$this->SetMonthlyTimerInterval();
	$this->SetYearlyTimerInterval();
        SetValue($this->GetIDForIdent("MaxLeistung"), 0);
        SetValue($this->GetIDForIdent("MaxLeistungZeit"), 0);
        SetValue($this->GetIDForIdent("TagCounter"), 0);
        SetValue($this->GetIDForIdent("LeistungVortag"), GetValue($this->GetIDForIdent("LeistungTag")));
        SetValue($this->GetIDForIdent("LeistungTag"), 0);
    }
    public function ResetPowerMeterMonthly(){       
        SetValue($this->GetIDForIdent("MonatCounter"), 0);
        SetValue($this->GetIDForIdent("LeistungVormonat"), GetValue($this->GetIDForIdent("LeistungMonat")));
        SetValue($this->GetIDForIdent("LeistungMonat"), 0);
    }
    public function ResetPowerMeterYearly(){
        SetValue($this->GetIDForIdent("JahrCounter"), 0);
        SetValue($this->GetIDForIdent("LeistungVorjahr"), GetValue($this->GetIDForIdent("LeistungJahr")));
        SetValue($this->GetIDForIdent("LeistungJahr"), 0);
	SetValue($this->GetIDForIdent("AnnualLimit"), 0);
    }
    private function Calculate(){
        $CounterOld = GetValue($this->GetIDForIdent("Counter"));
        $CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
        $delta = $CounterNew - $CounterOld;
        $Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
        $delta_kWh = ($delta * $Factor) * 20;
        
        SetValue($this->GetIDForIdent("Counter"), $CounterNew);
        SetValue($this->GetIDForIdent("Leistung"), $delta_kWh);
        
        // Maximale Leistung
        $Max = GetValue($this->GetIDForIdent("MaxLeistung"));
        if ($delta_kWh > $Max){
            SetValue($this->GetIDForIdent("MaxLeistung"), $delta_kWh);
            SetValue($this->GetIDForIdent("MaxLeistungZeit"), time());
        }
      
        // Counter Tag
        $CounterTag = GetValue($this->GetIDForIdent("TagCounter")) + $delta;
        SetValue($this->GetIDForIdent("TagCounter"), $CounterTag);
        SetValue($this->GetIDForIdent("LeistungTag"), $CounterTag * $Factor);
        
        // Counter Monat  
        $CounterMonat = GetValue($this->GetIDForIdent("MonatCounter")) + $delta;
        SetValue($this->GetIDForIdent("MonatCounter"), $CounterMonat);
        SetValue($this->GetIDForIdent("LeistungMonat"), $CounterMonat * $Factor);
      
        // Counter Jahr
        $CounterJahr = GetValue($this->GetIDForIdent("JahrCounter")) + $delta;
        SetValue($this->GetIDForIdent("JahrCounter"), $CounterJahr);
        SetValue($this->GetIDForIdent("LeistungJahr"), $CounterJahr * $Factor);
        
        // Jahresgrenzwert
        $AnnualLimit = $this->ReadPropertyInteger("AnnualLimit");
      
        if ($delta_kWh >= $AnnualLimit){
          SetValue($this->GetIDForIdent("AnnualLimit"), TRUE);
        }
        else{
          SetValue($this->GetIDForIdent("AnnualLimit"), FALSE);
        }
      
        // Betrieb
        $Active = $this->ReadPropertyInteger("LimitActive");
      
        if ($delta_kWh >= $Active){
          SetValue($this->GetIDForIdent("Betrieb"), TRUE);
        }
        else{
          SetValue($this->GetIDForIdent("Betrieb"), FALSE);
        }
      
        // Only for debugging
        $this->DebugMessage("Counter", "CounterOld: " . $CounterOld);
        $this->DebugMessage("Counter", "CounterNew: " . $CounterNew);
        $this->DebugMessage("Counter", "Delta: " . $delta);
        $this->DebugMessage("Counter", "Factor: " . $Factor);
        $this->DebugMessage("Counter", "Delta kWh: " . $delta_kWh);
    }
    private function GetFactor($Impulses){
        switch ($Impulses){
            case 250:
              return (0.004);
            break;
              
            case 500:
              return (0.002);
            break;
              
            case 800:
              return (0.00125);
            break;
              
            case 1000:
              return (0.001);
            break;
              
            case 2000:
              return (0.0005);
            break;
        }    
    }
    private function DebugMessage($Sender, $Message){
        $this->SendDebug($Sender, $Message, 0);
    }
    protected function SetDailyTimerInterval(){
        $Now = new DateTime(); 
	$Target = new DateTime(); 
	$Target->modify('+1 day'); 
	$Target->setTime(0,0,1); 
	$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
	$Interval = $Diff * 1000;  
        $this->SetTimerInterval("DailyReset", $Interval);
    }
    protected function SetMonthlyTimerInterval(){
        $Now = new DateTime(); 
	$Target = new DateTime(); 
	$Target->modify('first day of next month');
	$Target->setTime(0,0,5); 
	$Diff =  $Target->getTimestamp() - $Now->getTimestamp(); 
	$Interval = $Diff * 1000;  
	$this->SetTimerInterval("MonthlyReset", $Interval);
    }
    protected function SetYearlyTimerInterval(){
        $Now = new DateTime(); 
	$Target = new DateTime(); 
	$Target->modify('1st January Next Year');
	$Target->setTime(0,0,10); 
	$Diff = $Target->getTimestamp() - $Now->getTimestamp(); 
	$Interval = $Diff * 1000;  
	$this->SetTimerInterval("YearlyReset", $Interval);
    }
}
?>
