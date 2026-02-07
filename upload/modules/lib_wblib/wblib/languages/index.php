<?php

// include class.secure.php to protect this file and the whole CMS!
            if (defined("CAT_PATH")) {
                include CAT_PATH . "/framework/class.secure.php";
            } else {
                 = "../";
                 = ;
                 = 1;
                while ( < 10 && !file_exists( . "framework/class.secure.php")) {
                     .= ;
                     += 1;
                }
                if (file_exists( . "framework/class.secure.php")) {
                    include $root . "framework/class.secure.php";
                } else {
                    throw new \RuntimeException(
                        sprintf(
                        "Cannot include class.secure.php at %s",
                      $_SERVER['SCRIPT_NAME']
                        )
                    );
                }
            }
            // end include class.secure.php
