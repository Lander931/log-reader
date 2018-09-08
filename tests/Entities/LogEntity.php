<?php

namespace Lander931\LogReader\Tests\Entities;


class LogEntity
{
    private $date;
    private $time;
    private $message;
    private $stack;

    /**
     * LogEntity constructor.
     * @param $date
     * @param $time
     * @param $message
     * @param $stack
     */
    public function __construct($date, $time, $message, $stack)
    {
        $this->date = $date;
        $this->time = $time;
        $this->message = $message;
        $this->stack = $stack;
    }


    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getStack()
    {
        return $this->stack;
    }


}