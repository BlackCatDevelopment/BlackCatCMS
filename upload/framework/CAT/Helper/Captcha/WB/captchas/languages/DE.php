<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          Thomas Hornik (thorn),LEPTON Project
 * @copyright       2008-2011, Thomas Hornik (thorn),LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id: DE.php 1825 2012-03-23 16:20:14Z webbird $
 *
 */

/*
 -----------------------------------------------------------------------------------------
  DEUTSCHE SPRACHDATEI FUER DAS CAPTCHA-CONTROL ADMINISTRATIONS TOOL
 -----------------------------------------------------------------------------------------
*/

global $MOD_CAPTCHA_CONTROL;

// Deutsche Modulbeschreibung
$module_description 	= 'Admin-Tool um das Verhalten von CAPTCHA und ASP zu kontrollieren';

// Ueberschriften und Textausgaben
$MOD_CAPTCHA_CONTROL['HEADING']           = 'Captcha- und ASP Steuerung';
$MOD_CAPTCHA_CONTROL['HOWTO']             = 'Hiermit kann das Verhalten von "CAPTCHA" und "Advanced Spam Protection" (ASP) gesteuert werden. Damit ASP in einem Modul wirken kann, muss das verwendete Modul entsprechend angepasst sein.';

// Text and captions of form elements
$MOD_CAPTCHA_CONTROL['CAPTCHA_CONF']      = 'CAPTCHA-Einstellungen';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TYPE']      = 'CAPTCHA-Typ';
$MOD_CAPTCHA_CONTROL['CAPTCHA_EXP']       = 'Die CAPTCHA-Einstellungen f&uuml;r die Module befinden sich in den jeweiligen Modul-Optionen';
$MOD_CAPTCHA_CONTROL['USE_SIGNUP_CAPTCHA']= 'CAPTCHA f&uuml;r Registrierung aktivieren';
$MOD_CAPTCHA_CONTROL['ENABLED']           = 'Aktiviert';
$MOD_CAPTCHA_CONTROL['DISABLED']          = 'Ausgeschaltet';
$MOD_CAPTCHA_CONTROL['ASP_CONF']          = 'Erweiterter-Spam-Schutz (ASP) Einstellungen';
$MOD_CAPTCHA_CONTROL['ASP_TEXT']          = 'ASP benutzen (wenn im Modul vorhanden)';
$MOD_CAPTCHA_CONTROL['ASP_EXP']           = 'ASP versucht anhand der verschiedenen Verhaltensweisen zu erkennen, ob eine Formular-Eingabe von einem Menschen oder einem Spam-Bot kommt.';
$MOD_CAPTCHA_CONTROL['CALC_TEXT']         = 'Rechnung als Text';
$MOD_CAPTCHA_CONTROL['CALC_IMAGE']        = 'Rechnung als Bild';
$MOD_CAPTCHA_CONTROL['CALC_TTF_IMAGE']    = 'Rechnung als Bild mit wechselnden Schriften und Hintergr&uuml;nden';
$MOD_CAPTCHA_CONTROL['TTF_IMAGE']         = 'Bild mit wechselnden Schriften und Hintergr&uuml;nden';
$MOD_CAPTCHA_CONTROL['OLD_IMAGE']         = 'Alter Stil (nicht empfohlen)';
$MOD_CAPTCHA_CONTROL['TEXT']              = 'Text-CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_ENTER_TEXT']= 'Fragen und Antworten';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TEXT_DESC'] = 'Bitte hier alles l&ouml;schen'."\n".'sonst werden Ihre &Auml;nderungen nicht gespeichert!'."\n".'### Beispiel ###'."\n".'Hier k&ouml;nnen sie Fragen und Antworten eingeben.'."\n".'Entweder:'."\n".'?Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".'?Frage 2'."\n".'!Antwort 2'."\n".' ... '."\n".'wenn nur eine Sprache benutzt wird.'."\n".''."\n".'Oder, wenn die Sprache relevant ist:'."\n".'?EN:What\'s Claudia Schiffer\'s first name?'."\n".'!Claudia'."\n".'?EN:Question 2'."\n".'!Answer 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### Beispiel ###'."\n".'';
$MOD_CAPTCHA['VERIFICATION']           = 'Pr&uuml;fziffer';
$MOD_CAPTCHA['ADDITION']               = 'plus';
$MOD_CAPTCHA['SUBTRAKTION']            = 'minus';
$MOD_CAPTCHA['MULTIPLIKATION']         = 'mal';
$MOD_CAPTCHA['VERIFICATION_INFO_RES']  = 'Bitte Ergebnis eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_TEXT'] = 'Bitte Text eintragen';
$MOD_CAPTCHA['VERIFICATION_INFO_QUEST'] = 'Bitte Frage beantworten';
$MOD_CAPTCHA['INCORRECT_VERIFICATION'] = 'Das Ergebnis ist falsch. Bitte tragen Sie es erneut ein';

?>