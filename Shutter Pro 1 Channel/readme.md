# ESERA Shutter Pro 1-Fach
Das Modul bindet das Shutter Pro 1-Fach von ESERA-Automation ein. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Das Modul stellt eine Datenverbindung zwischen ESERA 1-Wire Controller / 1-Wire Gateway und dem 1-Wire Gerät her.
* Die OWDID ist einstellbar
* Die Variablenwerte werden automatische Aktualisiert
* Steuern der Ausgänge / des Ausgangs des 1-Wire Gerät

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- ESERA-Automation 1-Wire Controller / 1-Wire Gateway / ESERA-Station 200 ab Firmware 1.18_72


### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen:
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das Shutter Modul 1-Fach'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name | Beschreibung
---- | ---------------------------------
OWD  | Auswahl der eingerichteten OWDID.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch für alle übermittelten Werte passende Variablen angelegt.
Hier konkret: 
- Down
- Up
- Stopp
- Standby

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
OWD       | Integer

##### Profile:

Es werden zusätzliche Variablenprofile für Webfront hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz 
Fährt einen Rollladenmotor eines 1-Wire Shutter Pro Modules nach oben, unten oder stoppt.
Die Funktion liefert keinerlei Rückgabewert.  

Befehl: 
 `ESERA_SetShutter(integer $InstanzID, integer fix 1, integer $Value);`
 
Beispiele:  
 `ESERA_SetShutter(12345,1,1);`  => OWD 1 fährt den Rolladen nach unten

 `ESERA_SetShutter(12345,1,2);`  => OWD 1 fährt den Rolladen nach oben

 `ESERA_SetShutter(12345,1,3);`  => OWD 1 stoppt den Motor

 
Neu ab 1-Wire Controller/1-Wire Gateway Firmware 1.20_25 (5/2019)
Es kann nun zu dem Shutter Befehl zusätzlich eine Laufzeit für die 1-Wire Shutter Pro übergeben werden 
Wichtig, dieser Befehl funktioniert nur bei 1-Wire Shutter Pro, Art. Nr. 11231 ab Kaufdatum 5/2019.
 
Es muss nur die OWD Nummer ($Gruppe), der Steuerbefehl ($Value) und die Dauer ($Duration) gesendet werden.
Value für Shutter: 1=Down, 2=Up, 3=Stopp
Duration: 250ms, 500ms, 750ms oder 1-60 Sekunden
 
Die Funktion liefert keinerlei Rückgabewert.  

Befehl: 
 `ESERA_SetShutterDuration(integer $InstanzID, integer $Value, integer $Duration);`
 
Beispiel:  
 `ESERA_SetGroupShtOutDuration(12345,10,1,20);`		=> Gruppe 10, Laufrichtung Down (1) und 20 Sekunden Dauer 
 

Um mehrere Shutter Module mit einem Befehl steuern zu können, gibt es das Modul "Zubehör OWD Group Command". Über diese Modul kann ein Gruppenbefehl an einen 1-Wire Controller geschickt werden. 
Der 1-Wire Controller steuert dann die in der Gruppe hinterlegten Shutter Module mit dem Befehl an.
Mehr unter dem Modul "Zubehör OWD Group Command"
