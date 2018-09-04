# ESERA Dual Dimmer
Das Modul bindet die Dual Dimmer Phasenanschnitt und Phasenabschnitt von ESERA-Automation ein. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt via ESERA-Automation 1-Wire Controller / 1-Wire Gateway Verbindung zum Gerät her.
* Einstellbarkeit der OWDID
* Schaltbarkeit von Ausgängen
* Automatische Aktualisierung der Ausgänge

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Digital Out 8 Channel'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name | Beschreibung
---- | ---------------------------------
OWD  | Auswahl der eingerichteten OWDID.

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch alle 8 Ausgänge eingerichtet.

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
OWD       | Integer

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz
`boolean ESERA_SetDimmer(integer $InstanzID, integer $OutputNumber, integer $Value);`  
Steuert jeweils einen Ausgang An/Aus/Dimmen. 
Beispiel:  
`ESERA_SetDimmer(12345, 1, 31);`
