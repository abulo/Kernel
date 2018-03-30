<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message;
use Kernel\Asyn\MQTT\Message;

/**
 * Message PINGREQ
 * Client -> Server
 *
 * 3.12 PINGREQ – PING request
 */
class PINGREQ extends Base
{
    protected $message_type = Message::PINGREQ;
    protected $protocol_type = self::FIXED_ONLY;
}

# EOF
