<?php


namespace Kernel\Components\Log;

class IPaddressProcessor
{
    public function __invoke(array $record): array
    {
        $record['extra']['ip'] = swoole_get_local_ip();
        return $record;
    }
}
