<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message\Header;

use Kernel\Asyn\MQTT\Exception;
use Kernel\Asyn\MQTT\Message;

/**
 * Fixed Header definition for PINGRESP
 */
class PINGRESP extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reserved_flags = 0x00;

    /**
     * PINGRESP does not have Packet Identifier
     *
     * @var bool
     */
    protected $require_msgid = false;

    /**
     * Decode Variable Header
     *
     * @param string & $packet_data
     * @param int    & $pos
     * @return bool
     * @throws Exception
     */
    protected function decodeVariableHeader(&$packet_data, &$pos)
    {
        # DO NOTHING
        return true;
    }
}

# EOF
