<?php

namespace Lander931\LogReader;


use Lander931\LogReader\Exceptions\RuntimeException;

class LogReader
{
    private $text_log;
    private $split_logs;
    private $data = [];

    /**
     * LogReader constructor.
     * @param string $text_log
     */
    private function __construct($text_log)
    {
        $this->text_log = $text_log;
    }

    /**
     * @param string $text_log
     * @param callable $callable_split_log
     * @return LogReader
     */
    public static function read($text_log, callable $callable_split_log)
    {
        $reader = new self($text_log);
        $reader->splitLog($callable_split_log);
        return $reader;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function buildEntries(callable $callable)
    {
        foreach ($this->split_logs as $key => $split_log) {
            $this->data[$key] = $callable($split_log);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        if (count($this->data) == 0 && count($this->split_logs) > 0) return $this->split_logs;
        return $this->data;
    }

    /**
     * @param callable $split_log
     */
    private function splitLog(callable $split_log)
    {
        $logs = $split_log($this->text_log);
        if (!is_array($logs)) throw new RuntimeException('You must return an array');
        $this->split_logs = $logs;
    }
}