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
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Core
 *   @package         CAT_Core
 *
 */

require dirname(__FILE__) . '/../../../../modules/lib_phpmailer/phpmailer/PHPMailerAutoload.php';
//require_once dirname(__FILE__) . '/../../../../modules/lib_phpmailer/phpmailer/class.phpmailer.php';

if (!class_exists('CAT_Helper_Mail_PHPMailerDriver', false)) {

    class CAT_Helper_Mail_PHPMailerDriver extends PHPMailer
    {

        private static $instance;
        private static $settings;
        private static $transport;
        private static $mailer;
        private static $debug = false;

        public function __construct()
        {
            parent::__construct(true);
            $this->IsHTML(true);
            $this->WordWrap = 80;
            $this->Timeout  = 30;
        }

        /**
         * singleton pattern
         **/
        public static function getInstance($settings = NULL)
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            if ($settings) {
                self::$settings = $settings;
                $is_error       = false;
                try {
                    // create the transport
                    if (isset(self::$settings['routine']) && self::$settings['routine'] == "smtp" && isset(self::$settings['smtp_host']) && strlen(self::$settings['smtp_host']) > 5) {
                        self::$instance->SMTPDebug = 0;
                        if(self::$debug) self::$instance->SMTPDebug = 1;
                        self::$instance->isSMTP();
                        self::$instance->Host = self::$settings['smtp_host'];
                        if (isset(self::$settings['smtp_auth']) && isset(self::$settings['smtp_username']) && isset(self::$settings['smtp_password']) && self::$settings['smtp_auth'] == "true" && strlen(self::$settings['smtp_username']) > 1 && strlen(self::$settings['smtp_password']) > 1) {
                            self::$instance->SMTPAuth = true;
                            self::$instance->AuthType = 'gibsnich'; // had to do this as other auth types did not work (with Exchange)
                            self::$instance->Username = self::$settings['smtp_username'];
                            self::$instance->Password = self::$settings['smtp_password'];
                        }
                        // check for SSL
                        if (isset(self::$settings['smtp_ssl']) && self::$settings['smtp_ssl'] === true) {
                            $transports = stream_get_transports();
                            if (in_array('ssl', $transports)) {
                                self::$instance->SMTPSecure = 'ssl';
                                if (isset(self::$settings['smtp_ssl_port']) && self::$settings['smtp_ssl_port'] != '')
                                    self::$instance->Port = self::$settings['smtp_ssl_port'];
                                else
                                    self::$instance->Port = 587; // default port
                            }
                        }
                        // timeout
                        if (isset(self::$settings['smtp_timeout']) && self::$settings['smtp_timeout'] != '')
                            self::$instance->Timeout = self::$settings['smtp_timeout'];
                    } else {
                        // use PHP mail() function for outgoing mails send by Website Baker
                        self::$instance->IsMail();
                    }
                }
                catch (phpmailerException $e) {
                    CAT_Helper_Mail::setError(self::$instance->ErrorInfo);
                    $is_error = true;
                }
                catch (Exception $e) {
                    CAT_Helper_Mail::setError($e->getMessage());
                    $is_error = true;
                }

                if ($is_error) {
                    return false;
                } else {
                    try {
                        // set default sender name
                        if (self::$instance->FromName == 'Root User') {
                            if (isset($_SESSION['DISPLAY_NAME'])) {
                                self::$instance->FromName = $_SESSION['DISPLAY_NAME'];
                            } else {
                                self::$instance->FromName = self::$settings['default_sendername'];
                            }
                        }
                        self::$instance->From = self::$settings['server_email'];
                    }
                    catch (phpmailerException $e) {
                        CAT_Helper_Mail::setError(self::$instance->ErrorInfo);
                    }
                    catch (Exception $e) {
                        CAT_Helper_Mail::setError($e->getMessage());
                    }
                }

                // set language file for PHPMailer error messages
                if (defined("LANGUAGE")) {
                    self::$instance->SetLanguage(strtolower(LANGUAGE), "language/");
                }

                // set default charset
                if (defined('DEFAULT_CHARSET')) {
                    self::$instance->CharSet = DEFAULT_CHARSET;
                } else {
                    self::$instance->CharSet = 'utf-8';
                }
            }
            return self::$instance;
        } // end function getInstance()

        /**
         *
         **/
        public function sendMail($fromaddress, $toaddress, $subject, $message, $fromname = '')
        {
            // format
            $fromaddress = preg_replace('/[\r\n]/', '', $fromaddress);
            $toaddress   = preg_replace('/[\r\n]/', '', $toaddress);
            $subject     = preg_replace('/[\r\n]/', '', $subject);
            $message     = preg_replace('/\r\n?|\n/', '<br \>', $message);

            try {
                if ($fromaddress != '') {
                    if ($fromname != '')
                        self::$instance->FromName = $fromname;
                    self::$instance->From = $fromaddress;
                    self::$instance->AddReplyTo($fromaddress);
                }

                self::$instance->AddAddress($toaddress);
                self::$instance->Subject = $subject;
                self::$instance->Body    = $message;
                self::$instance->AltBody = strip_tags($message);
                self::$instance->Send();
                return true;
            }
            catch (phpmailerException $e) {
                CAT_Helper_Mail::setError(self::$instance->ErrorInfo);
                return false;
            }
            catch (Exception $e) {
                CAT_Helper_Mail::setError($e->getMessage());
                return false;
            }
            // never reached
            return true;
        } // end function sendMail()
    }
}