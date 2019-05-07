<?php

namespace Kernel\Asyn\Http;


use SimpleXMLElement;
use DOMDocument;

/**
 * @method static array toJsonArray(string $var)
 * @method static object toJsonObject(string $var)
 * @method static string toJsonString(array | array $var)
 * @method static array toQueryArray(string $var)
 * @method static string toQueryString(array $var)
 * @method static SimpleXMLElement toXmlObject(string | array $var, SimpleXMLElement $xml)
 * @method static string toXmlString(array $var)
 * @method static DOMDocument toDomObject(string $var)
 * @method static string toMultipartString(array $var, string $boundary)
 */
class DataParser
{

    public static function __callStatic($name, $arguments)
    {
        $var = $arguments[0] ?? null;
        if ($var === null) {
            throw new \InvalidArgumentException("Argument can't be null!");
        } else {
            $callMap = self::getCallableMap();
            if (TypeDetector::canBeArray($var) && isset($callMap[$name]['supports']['array'])) {
                return call_user_func($callMap[$name]['supports']['array'], $var);
            } elseif (TypeDetector::canBeString($var) && isset($callMap[$name]['supports']['string'])) {
                return call_user_func($callMap[$name]['supports']['array'], $var);
            } elseif (is_object($var) && isset($callMap[$name]['supports']['object'])) {
                return call_user_func($callMap[$name]['supports']['object'], $var);
            }
        }

        throw new \InvalidArgumentException(
            'Not implement for ' . (is_object($var) ? get_class($var) : gettype($var)) . " $name"
        );
    }

    public static function stringToJsonArray(string $var): array
    {
        return ($var = json_decode($var, true)) === false ? [] : $var;
    }

    public static function stringToJsonObject(string $var): object
    {
        return ($var = json_decode($var)) === false ? (object)[] : (object)$var;
    }

    public static function arrayToJsonString(array $var): string
    {
        return ($var = json_encode($var)) === false ? '{}' : $var;
    }

    public static function objectToJsonString(array $var): string
    {
        return ($var = json_encode($var)) === false ? '{}' : $var;
    }

    public static function stringToQueryArray(string $var): array
    {
        parse_str($var, $ret);

        return $ret;
    }

    public static function arrayToQueryString(array $var): string
    {
        return http_build_query($var);
    }

    public static function stringToXmlObject(string $var): SimpleXMLElement
    {
        return new SimpleXMLElement($var);
    }

    public static function arrayToXmlString(array $var): string
    {
        return self::arrayToXmlObject($var)->asXML();
    }

    public static function arrayToXmlObject(array $var, ?SimpleXMLElement &$xml = null): SimpleXMLElement
    {
        if ($xml === null) {
            $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
        }
        foreach ($var as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $sub_node = $xml->addChild($key);
                self::arrayToXmlObject($value, $sub_node);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xml;
    }

    public static function stringToDomObject(string $var): DOMDocument
    {
        libxml_use_internal_errors(true);
        $html = new DOMDocument($var);
        $html->loadHTML($var);

        return $html;
    }

    public static function arrayToMultipartString(array $var, string $boundary): string
    {
        $ret = '';
        foreach ($var as $name => $value) {
            $value = (string)$value;
            $ret .= "--{$boundary}\r\nContent-Disposition: form-data; name=\"{$name}\"\r\n\r\n{$value}\r\n";
        }
        $ret .= "--{$boundary}--\r\n";

        return $ret;
    }
}
