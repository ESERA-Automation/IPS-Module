# ESERA DualIO
Das Modul bindet den DualIO von ESERA-Automation ein. Es werden automatisch Variablen angelegt und eingelesen.

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
- ESERA-Automation 1-Wire Controller / 1-Wire Gateway

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen:
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das DualIO'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name | Beschreibung
---- | ---------------------------------
OWD  | Auswahl der eingerichteten OWDID.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Für alle übermittelten Werte werden passende Variablen angelegt.
Es werden automatisch Variablen für die Ein- und Ausgänge eingerichtet.
- Eingang 0
- Eingang 1
- Status 0
- Status 1

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
OWD       | Integer

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz
Es gibt keine Befehle.
