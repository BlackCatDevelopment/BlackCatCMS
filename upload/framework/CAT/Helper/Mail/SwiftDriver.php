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
        private static $debug = false;

        public function __construct() {}

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
        public function sendMail($fromaddress, $toaddress, $subject, $message, $fromname='', $html='')
        {

            $use_smtp = false;

			if ( $fromname != '' )
			{
				$fromaddress	= array(
					$fromaddress => $fromname
				);
			}
            // Create the message
            try
            {
                $message = Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($fromaddress)
                    ->setTo($toaddress)
                    ->setBody($message);
					if ( $html != '') $message->addPart($html, 'text/html');
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
                    $port = '25';
                    $tp   = NULL;
                    // check for SSL
                    // port 587 is for STARTTLS
                    // port 465 is for TLS
                    if ( isset(self::$settings['smtp_ssl']) && self::$settings['smtp_ssl'] == true )
                    {
                        if(
                               isset(self::$settings['smtp_ssl_port'])
                            && self::$settings['smtp_ssl_port'] != ''
                            && self::$settings['smtp_ssl_port'] != '25'
                            && self::$settings['smtp_ssl_port'] != '587'
                        ) {
                            $transports = stream_get_transports();
                            if(in_array('tls',$transports))
                            {
                                $tp   = 'tls';
                                $port = ( isset(self::$settings['smtp_ssl_port']) && self::$settings['smtp_ssl_port'] != '' )
                                      ? self::$settings['smtp_ssl_port']
                                      : '465'
                                      ;
                            }
                            else {
                                if(in_array('ssl',$transports))
                                {
                                    $tp   = 'ssl';
                                    $port = ( isset(self::$settings['smtp_ssl_port']) && self::$settings['smtp_ssl_port'] != '' )
                                          ? self::$settings['smtp_ssl_port']
                                          : '465'
                                          ;
                                }
                            }
                        }
                    }
                    self::$transport = Swift_SmtpTransport::newInstance(self::$settings['smtp_host'],$port,$tp);

                    if(self::$debug)
                    {
                        $logger = new Swift_Plugins_Loggers_EchoLogger();
                        self::$transport->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
                    }

                    $use_smtp = true;
                }
                else
                {
                    self::$transport = Swift_MailTransport::newInstance();
                }
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

                // timeout
                if ( $use_smtp && isset(self::$settings['smtp_timeout']) && self::$settings['smtp_timeout'] != '' )
                    self::$transport->setTimeout(self::$settings['smtp_timeout']);
            }

            if(!self::$mailer)
            {
                // Create the Mailer using your created Transport
                self::$mailer = Swift_Mailer::newInstance(self::$transport);
            }

            try
            {
                self::$mailer->send($message);
            }
            catch (Swift_TransportException $e)
            {
                CAT_Helper_Mail::setError('Transport exception: '.$e->getMessage().' (Port: '.$port.', Transport: '.$tp.')');
                return false;
            }
            catch (Exception $e)
            {
                CAT_Helper_Mail::setError($e->getMessage());
                return false;
            }

            return true;
        }   // end function sendMail()
    }
}