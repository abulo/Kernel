<?php


namespace Kernel\Components\Log;

use Monolog\Formatter\MongoDBFormatter;

class SDMongodbFormatter extends MongoDBFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // $context = $record['context'];
        // $RunStack = $context['RunStack']??[];
        // $count = count($RunStack);
        // if ($count) {
        //     for ($i = 0; $i<$count; $i++) {
        //         $newRunStack[] = $RunStack[$i];
        //     }
        //     $context['RunStack'] = $newRunStack;
        // }
        // foreach ($context as $key => $value) {
        //     $record['cxt_' . $key] = $value;
        // }
        // $extra = $record['extra'];
        // foreach ($extra as $key => $value) {
        //     $record['ex_' . $key] = $value;
        // }
        //
        // unset($record['datetime']);
        // unset($record['context']);
        // unset($record['extra']);

        return $this->formatArray($record);
    }
}
