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
        $this->RegisterVariableFloat("Wind_kmh", "Wind km/h", "", 2);
        $this->RegisterVariableFloat("Wind_ms", "Wind m/s", "", 3);

        $this->RegisterTimer("Refresh", 0, 'ESERA_RefreshCounter($_IPS[\'TARGET\']);');

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
    private function Calculate(){
        $CounterOld = GetValue($this->GetIDForIdent("Counter"));
        $CounterNew = GetValue($this->ReadPropertyInteger("CounterID"));
        $delta = $CounterNew - $CounterOld;
        $Factor = $this->GetFactor($this->ReadPropertyInteger("Impulses"));
        $delta_Wind = ((($delta / $Factor) * 3600) / 1000);
        $delta_Wind_ms = $delta / $Factor;

        SetValue($this->GetIDForIdent("Counter"), $CounterNew);
        SetValue($this->GetIDForIdent("Wind_kmh"), $delta_Wind);
        SetValue($this->GetIDForIdent("Wind_ms"), $delta_Wind_ms);

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
