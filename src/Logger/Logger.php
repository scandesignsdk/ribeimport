<?php
namespace Ribe\Import\Logger;

use Datapump\Logger\Logger as LoggerInterface;

class Logger extends \Monolog\Logger implements LoggerInterface
{

    /**
     * Log data
     *
     * @param string $data
     * @param string $type
     *
     * @param array $context
     * @return LoggerInterface
     */
    public function log($data, $type, array $context = array())
    {
        //$this->addDebug($data);
    }
}
 