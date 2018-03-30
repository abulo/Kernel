<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message;

use Kernel\Asyn\MQTT\Message;

/**
 * Message PUBREC
 * Client <-> Server
 *
 * 3.5 PUBREC – Publish received (QoS 2 publish received, part 1)
 */
class PUBREC extends Base
{
    protected $message_type = Message::PUBREC;
    protected $protocol_type = self::WITH_VARIABLE;
    protected $read_bytes = 4;
}

# EOF
