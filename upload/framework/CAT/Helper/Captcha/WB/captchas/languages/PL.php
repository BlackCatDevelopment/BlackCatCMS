<?php

/**
 * This file is part of an ADDON for use with LEPTON Core.
 * This ADDON is released under the GNU GPL.
 * Additional license terms can be seen in the info.php of this module.
 *
 *
 * @author          Krzysztof Winnicki,LEPTON Project
 * @copyright       2008-2011, Thomas Hornik (thorn),LEPTON Project
 * @link            http://www.LEPTON-cms.org
 * @license         http://www.gnu.org/licenses/gpl.html
 * @license_terms   please see info.php of this module
 * @version         $Id: PL.php 1825 2012-03-23 16:20:14Z webbird $
 *
 */

/*
 -----------------------------------------------------------------------------------------
  POLISH LANGUAGE FILE FOR THE CAPTCHA-CONTROL ADMIN TOOL Krzysztof Winnicki
 -----------------------------------------------------------------------------------------
*/

global $MOD_CAPTCHA_CONTROL;

// Headings and text outputs
$MOD_CAPTCHA_CONTROL['HEADING']           = 'Captcha i Kontrola ASP';
$MOD_CAPTCHA_CONTROL['HOWTO']             = 'Tutaj możesz zmieć tryb wyświetlania "CAPTCHA" i ustawić zaawansowaną ochronę przed spamem (ASP).';

// Text and captions of form elements
$MOD_CAPTCHA_CONTROL['CAPTCHA_CONF']      = 'Konfiguracja CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TYPE']      = 'Rodzaj CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_EXP']       = 'Ustawienia CAPTCHA dla modułów znajdują się w opcjach modułu';
$MOD_CAPTCHA_CONTROL['USE_SIGNUP_CAPTCHA']= 'Aktywacja CAPTCHA przy rejestracji';
$MOD_CAPTCHA_CONTROL['ENABLED']           = 'Włączone';
$MOD_CAPTCHA_CONTROL['DISABLED']          = 'Wyłączone';
$MOD_CAPTCHA_CONTROL['ASP_CONF']          = 'Ustawienia - zaawansowana ochrona przed spamem';
$MOD_CAPTCHA_CONTROL['ASP_TEXT']          = 'Aktywuj ASP (jeśli dostępne)';
$MOD_CAPTCHA_CONTROL['ASP_EXP']           = 'ASP będzie weryfikować czy dodana treść pochodzi od człowieka czy spam-bota.';
$MOD_CAPTCHA_CONTROL['CALC_TEXT']         = 'Weryfikacja tekstu';
$MOD_CAPTCHA_CONTROL['CALC_IMAGE']        = 'Weryfikacja obrazu';
$MOD_CAPTCHA_CONTROL['CALC_TTF_IMAGE']    = 'Weryfikacja obrazu z różnymi czcionkami i tłem'; 
$MOD_CAPTCHA_CONTROL['TTF_IMAGE']         = 'Obraz z różnych czcionek i tła';
$MOD_CAPTCHA_CONTROL['OLD_IMAGE']         = 'Stary styl (nie polecane)';
$MOD_CAPTCHA_CONTROL['TEXT']              = 'Treść-CAPTCHA';
$MOD_CAPTCHA_CONTROL['CAPTCHA_ENTER_TEXT']= 'Pytania i odpowiedzi';
$MOD_CAPTCHA_CONTROL['CAPTCHA_TEXT_DESC'] = 'Usuń wszystko aby dodać własną treść.'."\n".'W przeciwnym razie twoje zmiany nie zostaną zapisane!'."\n".'### przykład ###'."\n".'Wpisuj tylko pytania i odpowiedzi.'."\n".'Ułóż treści w następujący sposób:'."\n".'?Jak Adam Małysz ma na imię?'."\n".'!Adam'."\n".'?Pytanie 2'."\n".'!Odpowiedź 2'."\n".''."\n".'Jeśli język treści jest nieistotny.'."\n".' ... '."\n".'bądź jest ułóż treść w pastępujący sposób:'."\n".'?PL:Jak Adam Małysz ma na imię?'."\n".'!Adam'."\n".'?PL:Pytanie 2'."\n".'!Odpowiedź 2'."\n".'?DE:Wie ist der Vorname von Claudia Schiffer?'."\n".'!Claudia'."\n".' ... '."\n".'### przykład ###'."\n".'';

$MOD_CAPTCHA['VERIFICATION']           = 'Weryfikacja';
$MOD_CAPTCHA['ADDITION']               = 'plus';
$MOD_CAPTCHA['SUBTRAKTION']            = 'minus';
$MOD_CAPTCHA['MULTIPLIKATION']         = 'razy';
$MOD_CAPTCHA['VERIFICATION_INFO_RES']  = 'wpisz wynik';
$MOD_CAPTCHA['VERIFICATION_INFO_TEXT'] = 'wpisz tekst';
$MOD_CAPTCHA['VERIFICATION_INFO_QUEST'] = 'odpowiedz na pytanie';
$MOD_CAPTCHA['INCORRECT_VERIFICATION'] = 'weryfikacja nie powiodła się';

?>