<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message;

use Kernel\Asyn\MQTT\Debug;
use Kernel\Asyn\MQTT\Utility;
use Kernel\Asyn\MQTT\Message;

/**
 * Message PINGRESP
 * Client <- Server
 *
 * 3.13 PINGRESP – PING response
 */
class PINGRESP extends Base
{
    protected $message_type = Message::PINGRESP;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 2;
}

# EOF
