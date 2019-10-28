<?php
/**
 * MQTT Client
 */

namespace Kernel\Asyn\MQTT;

/**
 * Debug class
 */
class Debug
{

    const NONE   = 0;
    const ERR    = 1;
    const WARN   = 2;
    const INFO   = 3;
    const NOTICE = 4;
    const DEBUG  = 5;
    const ALL    = 15;

    /**
     * Debug flag
     *
     * Disabled by default.
     *
     * @var bool
     */
    protected static $enabled = false;

    /**
     * Enable Debug
     */
    public static function Enable()
    {
        self::$enabled = true;
    }

    /**
     * Disable Debug
     */
    public static function Disable()
    {
        self::$enabled = false;
    }

    /**
     * Current Log Priority
     *
     * @var int
     */
    protected static $priority = self::WARN;

    /**
     * Log Priority
     *
     * @param int $priority
     */
    public static function SetLogPriority($priority)
    {
        self::$priority = (int) $priority;
    }

    /**
     * Log Message
     *
     * Message will be logged using error_log(), configure it with ini_set('error_log', ??)
     * If debug is enabled, Message will also be sent to stdout.
     *
     * @param int     $priority
     * @param string  $message
     * @param string  $bin_dump         If $bin_dump is not empty, hex/ascii char will be dumped
     */
    public static function Log($priority, $message, $bin_dump = '')
    {
        static $DEBUG_NAME = array(
            self::DEBUG  => 'DEBUG',
            self::NOTICE => 'NOTICE',
            self::INFO   => 'INFO',
            self::WARN   => 'WARN',
            self::ERR    => 'ERROR',
        );

        $log_msg = sprintf(
            "%-6s %s",
            $DEBUG_NAME[$priority],
            trim($message)
        );

        if ($bin_dump) {
            $bin_dump = Utility::PrintHex($bin_dump, true, 16, true);
            $log_msg .= "\n" . $bin_dump;
        }

        if (self::$enabled) {
            list($usec, $sec) = explode(" ", microtime());
            $datetime = date('Y-m-d H:i:s', $sec);

            printf("[%s.%06d] ", $datetime, $usec * 1000000);
            echo $log_msg, "\n";
        }

        if ($priority <= self::$priority) {
            error_log($log_msg);
        }
    }
}

# EOF
