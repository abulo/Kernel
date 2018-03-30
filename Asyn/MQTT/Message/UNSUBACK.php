<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message;
use Kernel\Asyn\MQTT\Message;


/**
 * Message UNSUBACK
 * Client <- Server
 *
 * 3.11 UNSUBACK – Unsubscribe acknowledgement
 */
class UNSUBACK extends Base
{
    protected $message_type = Message::UNSUBACK;
    protected $protocol_type = self::FIXED_ONLY;
    protected $read_bytes = 4;

}

# EOF
