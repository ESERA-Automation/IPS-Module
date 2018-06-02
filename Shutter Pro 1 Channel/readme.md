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

* Stellt via ESERA-Automation 1-Wire Controller / 1-Wire Gateway / ESERA-Station Verbindung zum Shutter Pro 1-Fach her.
* OWDID einstellbar
* Automatische Aktualisierung der Werte für Output- und Input Status

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

Es werden automatisch alle übermittelten Werte für Output- und Input Status angelegt.
- Down
- Up
- Stop
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
`boolean ESERA_SetShutter(integer $InstanzID, integer fix 1, integer $Value);`  
Fährt einen Rollladen hoch/runter oder stoppt.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_SetShutter(12345,1,1);`  
Fährt den Rolladen runter.

`ESERA_SetShutter(12345,1,2);`  
Fährt den Rolladen hoch.

`ESERA_SetShutter(12345,1,3);`  
Stoppt den Rolladen.
