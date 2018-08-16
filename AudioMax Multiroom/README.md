# ESERA Audio Max Multiroom Audio System
Das Modul bindet den 1-Wire Gateway innerhalb der ESERA-Staion 200 von ESERA-Automation ein. Es werden automatisch Variablen angelegt und eingelesen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Stellt via TCP/Seriell eine Verbindung zum ESERA Audio Max Multiroom Audio System her.
* Konfigurierbarkeit von KeepAlive des Geräts.
* Abfrage von Systemeinstellungen/-informationen.
* Auslesen von gesendeten Daten und automatisches Anlegen/Aktualisieren von Variablen.
* Dient als Splitterinstanz für angeschlossenes ESERA AudioMax System Module.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/ESERA-Automation/IPS-Module.git`  

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'AudioMax Gateway des Audio Max Multiroom Audio System'-Modul unter dem Hersteller 'ESERA-Automation' aufgeführt.  

__Konfigurationsseite__:

Name                | Beschreibung
------------------- | ---------------------------------
Verbindungstyp      | Auswahl ob TCP/IP oder Seriell verwendet werden soll.
Datenausgabetyp     | Auswahl ob als Ausgabetyp OWD oder ID genutzt werden soll. Muss durch "Gerät konfigurieren" angestoßen werden.
Send KeepAlive      | De-/Aktiviert Send KeepAlive auf dem Audio Max Multiroom Audio System. Muss durch "Gerät konfigurieren" angestoßen werden.
Intervall (Send)    | Intervall von Send KeepAlive in Sekunden (60..240). Muss durch "Gerät konfigurieren" angestoßen werden.
Receive KeepAlive   | De-/Aktiviert Receive KeepAlive auf dem Audio Max Multiroom Audio System. Muss durch "Gerät konfigurieren" angestoßen werden.
Intervall (Receive) | Intervall von Receive KeepAlive in Sekunden (60..240). Muss durch "Gerät konfigurieren" angestoßen werden.
Gerät konfigurieren | Ruft ESERA_ConfigureDevice() und ESERA_SaveToSRAM() auf und konfiguriert KeepAlive und Datenausgabetyp.


### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Die Statusvariablen werden automatisch vom Controller gesendet und von IP-Symcon bei Empfang erstellt und aktualisiert.
Dabei wird der Variablenname automatisch als Name und Ident genutzt.

__Unterstützte Datenpakete__

Typ       | Variablentyp
--------- | -------------
KAL       | Integer
ARTNO     | Integer
AUDIO      | Integer
DEBUG     | Integer
FW        | String
HW        | String


##### Profile:

Es werden keine zusätzlichen Profile hinzugefügt

### 6. WebFront

Über das WebFront und die mobilen Apps werden die Variablen angezeigt. Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

=====================================================
`boolean ESERA_GetSysInfo(integer $InstanzID);`  
Fragt die System Informationen an und passt ggf. die Zeit des Controllers an.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_GetSysInfo(12345);`  

`boolean ESERA_GetSysSetting(integer $InstanzID);`  
Fragt die System Settings an.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_GetSysSetting(12345);`  

`boolean ESERA_SaveOneWireParts(integer $InstanzID);`  
Speichert gefundene 1-Wire Bauteile.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_SaveOneWireParts(12345);`  

`boolean ESERA_SaveToSRAM(integer $InstanzID);`  
Speichert die Einstellungen des 1-Wire Controller / 1-Wire Gateway.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_SaveToSRAM(12345);`  

`boolean ESERA_ConfigureDevice(integer $InstanzID);`  
Konfiguriert den Datentyp der Ausgabe und den KeepAlive.  
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`ESERA_ConfigureDevice(12345);`