# OWD Gruppen Befehle für 1-Wire Aktoren 
(Aktuell nur 1-Wire Shutter Module!)
Das Modul ermögicht das Senden von Gruppenbefehlen an ESERA 1-Wire Aktoren.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

Senden von Gruppen Befehlen für 1-Wire Shutter Module

Wie der Name Gruppenbefehle schon sagt, kann nun mit einem Befehl eine große Anzahl (=> Gruppe) von
1-Wire Aktoren mit der gleichen Funktion gesteuert werden. Vorteil ist, es ist nicht für jeden einzelnen 1-Wire
Aktor (OWD) ein einzelner Befehl notwendig.
Alle 1-Wire Aktoren (OWD´s) die in dieser Gruppe hinzugefügt wurden, reagieren auf den Befehl. Von den
Gruppenbefehlen bleiben die bisherigen einzelnen Befehle völlig unberührt. Sie können einzelne 1-Wire Aktoren,
z.B. 1-Wire Shutter direkt per Befehl und/oder über den Gruppenbefehl steuern.
Jedem 1-Wire Aktor (OWD) können bis zu 8 Gruppenadressen in einem Bereich von 1-240 zugeordnet werden.
Doppelte Gruppenadressen werden automatisch vom 1-Wire Controller / 1-Wire Gateway ausgefiltert.

Es können aktuell die Gruppenbefehle nur für 1-Wire Shutter Module verwendet werden. Weitere Aktoren in Planung


### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- ESERA-Automation per 1-Wire Controller / 1-Wire Gateway (alle Ausführungen mit aktueller Firmware)
- ESERA-Automation 1-Wire Shutter Module

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Zubehoer OWD Group Command aufgeführt.  

__Konfigurationsseite__:
Es müssen keine Einstellungen vorgenommen werden.

### 5. Statusvariablen und Profile

Es werden keine Variablen oder Profile angelegt 

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.


### 7. PHP-Befehlsreferenz
`boolean ESERA_SetGroupShtOut(integer $InstanzID, int $Gruppe, int $Value);`  
 Es muss nur die Gruppenadresse ($Gruppe) und Steuerbefehl ($Value) gesendet werden.
 Value für Shutter: 1=Down, 2=Up, 3=Stopp
 
Beispiel:  
`ESERA_SetGroupShtOut(12345,10,1);`



