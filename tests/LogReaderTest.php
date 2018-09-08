<?php

namespace Lander931\LogReader\Tests;

use Lander931\LogReader\LogReader;
use Lander931\LogReader\Tests\Entities\LogEntity;

class LogReaderTest extends \PHPUnit_Framework_TestCase
{
    private $log_file;
    private $log_text;

    /**
     * @var LogReader
     */
    private $reader;

    protected function setUp()
    {
        $this->log_file = __DIR__ . '/fixtures/example1.log';
        $this->log_text = file_get_contents($this->log_file);
        $this->reader = LogReader::read($this->log_text, function ($text) {
            return self::splitLog($text);
        });
    }

    /**
     * Tests
     */

    public function testRead()
    {
        $this->assertEquals('Lander931\LogReader\LogReader', get_class($this->reader));
    }

    public function testRead2()
    {
        $this->setExpectedException('Lander931\LogReader\Exceptions\RuntimeException');

        LogReader::read($this->log_text, function () {
            return null;
        });
    }

    public function testBuildEntries()
    {
        $reader = clone $this->reader;

        $reader->buildEntries(function ($log) {
            $pattern = "/\[(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})\] (?>(.+)\sStack trace:\s(.+)|(.+))/sm";
            preg_match_all($pattern, $log, $matches, PREG_SET_ORDER);

            $date = $matches[0][1];
            $time = $matches[0][2];
            $message = ($matches[0][3]) ? $matches[0][3] : ((isset($matches[0][5]) && $matches[0][5]) ? $matches[0][5] : null);
            $stack = ($matches[0][4]) ? $matches[0][4] : null;

            if ($message) $message = trim($message);
            if ($stack) $stack = trim($stack);

            return new LogEntity($date, $time, $message, $stack);
        });

        $entries = $reader->getEntries();

        $this->assertInternalType('array', $entries);
        $this->assertTrue(count($entries) > 0);
        foreach ($entries as $entry) {
            $this->assertEquals('Lander931\LogReader\Tests\Entities\LogEntity', get_class($entry));
        }
    }

    public function testGetEntries()
    {
        $reader = clone $this->reader;
        $entries = $reader
            ->getEntries();

        $this->assertInternalType('array', $entries);
        $this->assertTrue(count($entries) > 0);
        foreach ($entries as $entry) {
            $this->assertNotInternalType('array', $entry);
        }
    }

    /**
     * Methods
     */

    /**
     * @param $text
     * @return array
     */
    private function splitLog($text)
    {
        $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/';
        preg_match_all($pattern, $text, $headings);
        $bodies = preg_split($pattern, $text);
        unset($bodies[0]);
        $bodies = array_values($bodies);
        $data = [];
        foreach ($bodies as $key => $body) {
            $data[] = $headings[0][$key] . $body;
        }
        return $data;
    }

}
