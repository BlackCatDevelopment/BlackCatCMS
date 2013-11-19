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
require_once dirname(__FILE__).'/../../../../modules/lib_swift/swift/swift_required.php';

if(!class_exists('CAT_Helper_Mail_SwiftDriver',false)) {

    class CAT_Helper_Mail_SwiftDriver extends Swift {

        private static $instance;
        private static $settings;
        private static $transport;
        private static $mailer;

        public function __construct()
        {
            //parent::__construct();
        }

        /**
         * singleton pattern
         **/
        public static function getInstance($settings=NULL)
        {
            if (!self::$instance)
            {
                self::$instance = new self();
            }
            if ( $settings )
            {
                self::$settings = $settings;
            }
            return self::$instance;
        }   // end function getInstance()

        /**
         *
         **/
        public function sendMail($fromaddress, $toaddress, $subject, $message, $fromname='')
        {

            $use_smtp = false;

            // Create the message
            try
            {
                $message = Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($fromaddress)
                    ->setTo($toaddress)
                    ->setBody($message);
            }
            catch(Exception $e)
            {
                return false;
            }

            if(!self::$transport)
            {
                // create the transport
                if(    isset(self::$settings['routine'])
                    && self::$settings['routine'] == "smtp"
                    && isset(self::$settings['smtp_host'])
                    && strlen(self::$settings['smtp_host']) > 5
                ) {
                    self::$transport = Swift_SmtpTransport::newInstance(self::$settings['smtp_host'], 25);
                    $use_smtp = true;
                }
                else
                {
                    self::$transport = Swift_MailTransport::newInstance();
                }
                // if SMTP...
                if (   $use_smtp
                    && isset(self::$settings['smtp_auth'])
                    && isset(self::$settings['smtp_username'])
                    && isset(self::$settings['smtp_password'])
                    && self::$settings['smtp_auth'] == "true"
                    && strlen(self::$settings['smtp_username']) > 1
                    && strlen(self::$settings['smtp_password']) > 1
                ) {
    				// use SMTP authentification
    				self::$transport->setUsername(self::$settings['smtp_username']);
    				self::$transport->setPassword(self::$settings['smtp_password']);
    			}

                // check for SSL
                if ( $use_smtp && isset(self::$settings['smtp_ssl']) && self::$settings['smtp_ssl'] == true )
                {
                    $transports = stream_get_transports();
                    if(in_array('ssl',$transports))
                    {
                        self::$transport->setEncryption('ssl');
                        if(isset(self::$settings['smtp_ssl_port']) && self::$settings['smtp_ssl_port'] != '')
                            self::$transport->setPort(self::$settings['smtp_ssl_port']);
                        else
                            self::$transport->setPort(587); // default port
                    }
                }
                // timeout
                if ( $use_smtp && isset(self::$settings['smtp_timeout']) && self::$settings['smtp_timeout'] != '' )
                    self::$transport->setTimeout(self::$settings['smtp_timeout']);
            }

            if(!self::$mailer)
            {
                // Create the Mailer using your created Transport
                self::$mailer = Swift_Mailer::newInstance(self::$transport);
            }

            return self::$mailer->send($message);
        }   // end function sendMail()
    }
}