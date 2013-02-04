<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Thomas Hornik (thorn), LEPTON Project, Black Cat Development
 *   @copyright       2008-2011, Thomas Hornik (thorn)
 *   @copyright       2011-2012, LEPTON Project
 *   @copyright       2013, Black Cat Development
 *   @link            http://www.blackcat-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

/*
 -----------------------------------------------------------------------------------------
  DEUTSCHE SPRACHDATEI FUER DAS CAPTCHA-CONTROL ADMINISTRATIONS TOOL
 -----------------------------------------------------------------------------------------
*/

// Deutsche Modulbeschreibung
$module_description 	= 'Admin-Tool um das Verhalten von CAPTCHA und ASP zu kontrollieren';

$LANG = array(
    'Captcha and ASP control' => 'Captcha- und ASP Steuerung',
    'CAPTCHA Configuration' => 'CAPTCHA-Einstellungen',
    'Type of CAPTCHA' => 'CAPTCHA-Typ',
    'Calculation as text' => 'Rechnung als Text',
    'Calculation as image' => 'Rechnung als Bild',
    'Calculation as image with varying fonts and backgrounds' => 'Rechnung als Bild mit wechselnden Schriften und Hintergr&uuml;nden',
    'Image with varying fonts and backgrounds' => 'Bild mit wechselnden Schriften und Hintergr&uuml;nden',
    'Old style (not recommended)' => 'Alter Stil (nicht empfohlen)',
    'Text-CAPTCHA' => 'Text-CAPTCHA',
    'Here you can control the behavior of "CAPTCHA" and "Advanced Spam Protection" (ASP). To get ASP work with a given module, this special module has to be adapted to make use of ASP.'
        => 'Hiermit kann das Verhalten von "CAPTCHA" und "Advanced Spam Protection" (ASP) gesteuert werden. Damit ASP in einem Modul wirken kann, muss das verwendete Modul entsprechend angepasst sein.',
    'Delete this all to add your own entries'."\n".'or your changes won\'t be saved!'."\n".'### example ###'."\n".'Here you can enter Questions and Answers.'."\n".'Use:'."\n".'?What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?Question 2'."\n".'!Answer 2'."\n".''."\n".'if language doesn\'t matter.'."\n".' ... '."\n".'Or, if language do matter, use:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### example ###'."\n".''
        => 'Bitte hier alles l&ouml;schen'."\n".'sonst werden Ihre &Auml;nderungen nicht gespeichert!'."\n".'### Beispiel ###'."\n".'Hier k&ouml;nnen sie Fragen und Antworten eingeben.'."\n".'Entweder:'."\n".'?Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".'?Frage 2'."\n".'!Antwort 2'."\n".' ... '."\n".'wenn nur eine Sprache benutzt wird.'."\n".''."\n".'Oder, wenn die Sprache relevant ist:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### Beispiel ###'."\n".'',
    'Questions and Answers' => 'Fragen und Antworten',
    'Enabled' => 'Aktiviert',
    'Disabled' => 'Ausgeschaltet',
    'CAPTCHA settings for modules are located in the respective module settings'
        => 'Die CAPTCHA-Einstellungen f&uuml;r die Module befinden sich in den jeweiligen Modul-Optionen',
    'Advanced Spam Protection Configuration' => 'Erweiterter-Spam-Schutz (ASP) Einstellungen',
    'ASP tries to determine if a form-input was originated from a human or a spam-bot.'
        => 'ASP versucht anhand der verschiedenen Verhaltensweisen zu erkennen, ob eine Formular-Eingabe von einem Menschen oder einem Spam-Bot kommt.',
    'Activate CAPTCHA for signup' => 'CAPTCHA f&uuml;r Registrierung aktivieren',
    'Activate ASP (if available)' => 'ASP benutzen (wenn im Modul vorhanden)',
);

$MOD_CAPTCHA['VERIFICATION']           = 'Pr&uuml;fziffer';
$MOD_CAPTCHA['ADDITION']               = 'plus';
$MOD_CAPTCHA['SUBTRAKTION']            = 'minus';
$MOD_CAPTCHA['MULTIPLIKATION']         = 'mal';
$MOD_CAPTCHA['VERIFICATION_INFO_RES']  = 'Bitte Ergebnis eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_TEXT'] = 'Bitte Text eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_QUEST'] = 'Bitte Frage beantworten';
$MOD_CAPTCHA['INCORRECT_VERIFICATION'] = 'Das Ergebnis ist falsch. Bitte tragen Sie es erneut ein';

?>