<?php

/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT\Message\Header;
use Kernel\Asyn\MQTT\Debug;
use Kernel\Asyn\MQTT\Message;
use Kernel\Asyn\MQTT\Utility;


/**
 * Fixed Header definition for PUBREL
 */
class PUBREL extends Base
{
    /**
     * Default Flags
     *
     * @var int
     */
    protected $reserved_flags = 0x02;

    /**
     * PUBREL requires Packet Identifier
     *
     * @var bool
     */
    protected $require_msgid = true;

    /**
     * Decode Variable Header
     *
     * Packet Identifier
     *
     * @param string & $packet_data
     * @param int    & $pos
     * @return bool
     */
    protected function decodeVariableHeader(& $packet_data, & $pos)
    {
        return $this->decodePacketIdentifier($packet_data, $pos);
    }
}

# EOF
