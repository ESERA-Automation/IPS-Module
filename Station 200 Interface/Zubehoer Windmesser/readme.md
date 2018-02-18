# ESERA Windmesser
Das Modul stellt auf Basis der Daten des Dual 32 Bit Counters von ESERA-Automation einen Windmesser bereit. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt via ESERA-Automation 1-Wire Controller / 1-Wire Gateway und Dual 32 Bit Counter einen Windmesser bereit.
* Automatische Aktualisierung der Werte

### 2. Voraussetzungen

- IP-Symcon ab Version 4.2
- ESERA-Automation 1-Wire Controller / 1-Wire Gateway
- Dual 32 Bit Counter Modul

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen:
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Windmesser'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name | Beschreibung
---- | ---------------------------------
Counter | Auswahl der Variable des Zählers vom Dual 32 Bit Counter Modul
Impulse | Impulse pro Umdrehung

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Es werden automatisch folgende Variablen angelegt.
- Counter
- Wind km/h
- Wind m/s

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
Counter | Integer
Wind km/h | Float
Wind m/s | Float

##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz
```php
ESERA_RefreshCounter(integer $InstanceID)
```
Die Funktion dienen nur dem internen Gebrauch und sollten nicht manuell ausgeführt werden.
