/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          Website Baker Project, LEPTON Project
 * @copyright       2004-2010, Website Baker Project
 * @copyright       2010-2011, LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see LICENSE and COPYING files in your package
 * @version         $Id$
 *
 */

Changelog 28.06.2010

Angepasste Rechteverwaltung für HomeVerezichnisse in der Mediaverwaltung

Sie haben mehreren User folgende Gruppen und ein Homefolder zugewiesen,

User0 ist der Suoeradmin mit der UserID 1 inf benötigt kein Homefolder

User1 hat die Gruppen 8,5,3,2,1 ist damit auch Administor aller ihm zugewiesenen Gruppen
User2 hat die Gruppen 5,3,2
User3 hat die Gruppen 7,3
User4 hat die Gruppe  3,1
User5 hat die Gruppe  8

User0 hat volle Rechte auf alle Gruppen

Es gibt folgende Gruppen/Kombinationen, die User1 al Adminsitartor verwaltet

Gruppe 2 mit User2,
Gruppe 3 mit User2, User3, User4
Gruppe 8 mit User5

In der Mediaverwaltung hat nur der Gruppenadminstrator volle Rechte in den
einzelnen Homefoldern der User der dieser Gruppen angehören

In alle anderen Ordnern im Mediaverzeichnis hat User1 nur Ansicht Rechte.
Er darf weder Ordner erstellen, Dateien hochspielen und löschen oder umzubennen,

Bedingt durch das im IFrame Explorerfenster ist ein Relaod der Seite manuell
durchzuführen, entweder über den Borwser oder über den neben Optionen befindlichen
Link "Neu laden"

Wir sind aber bempht so schnell wie möglich eine Lösung ohme IFrame zur VErfügung
zu stellen, welches aber bis zur nächsten Relase 2.8.2 nicht fertiggestellt ist.

Für Moduleauthoren:

Wie binde ich die Rechteveraltung für die Verzeichnisse in Module ein?
Die Rechte müssen im Sccript abgeprüft und gesetzt werden. Die Funktionen
übergeben auf einfache Art und Weise die Ordner bezogen auf die Homeverzeichnisse

Es gibt im script framework/functions.php gibt es 3 neue Funktionen:

1) function media_dirs_ro( &$wb ) zum Auslesen der Ordner die ReadOnly Rechte bekommen sollen
2) function media_dirs_rw( &$wb ) zum Auslesen der Ordner die ReadWrite Rechte bekommen sollen
3) function remove_path(&$path, $key, $vars = '')

Rückgabewert für 1 und 2 ist ein Array mit allen entsprechenden Ordnern und voller Pfadangabe,
sodass damit direkt weitergearbeitet werden kann.

Als Übergabewert benötigen beide Funktionen ein Objekt,
Für das Backend $admin und für das Frontend $wb

function remove_path(&$path, $key, $vars = '')
ist die Rückruffunktion funktion für das array_walk($dirs_rw,'remove_path',WB_PATH);

Die Funktion array_walk() übergibt jedes Element eines Arrays (arr) nacheinander
an die Rückruffunktion function. Innerhalb dieser Funktion erhalten Sie den Wert
des jeweiligen Array-Elements als ersten Parameter, den Schlüssel des Elements
als zweiten Parameter und den Wert von userdata als dritten Parameter.

in unserem Beispiel werden in dem Array alle Pfade entfernt.
aus D:\usr\www\wbbaker\htdocs\test/media/images  wird /media/images

Neu:
Die function directory_list gibt jetzt eine natürlich sortiertes Array mit
den Ordnern und Unterordner aus einem vorgegebenen Verzeinisch zurück





