# 1-Wire Controller 2 - Ein- und Ausgänge
Das Modul bindet die in den 1-Wire Controller 2 von ESERA-Automation integrierten Ein- und Ausgänge ein. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt eine Verbindung zu die in den  1-Wire Controller 2 integrierten Ein- und Ausgänge her.
* Automatische Aktualisierung der Werte

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- ESERA-Automation 1-Wire Controller 2

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das '1-Wire Controller 2 Ein- und Ausgaenge'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Es müssen keine Einstellungen vorgenommen werden.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Die Statusvariablen werden automatisch vom Controller gesendet und von IP-Symcon bei Empfang erstellt und aktualisiert.
Dabei wird der Variablenname automatisch als Name und Ident genutzt.

__Unterstützte Datenpakete__

-

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz
`boolean ESERA_SetSysOutput(integer $InstanzID, int $OutputNumber, int $Value);`  
Setzt die digitalen Ausgänge aktiv oder inaktiv.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_SetSysOutput(12345,1,1);`  

`boolean ESERA_SetSysAnalogOutput(integer $InstanzID, int $Value);`  
Setzt den analogen Ausgang. Spannung mit 100 multiplizieren, also 850 für 8,5 V.
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_SetSysAnalogOutput(12345,850);`  
