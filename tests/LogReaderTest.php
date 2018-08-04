<?php

namespace Lander931\LogReader\Tests;

use Lander931\LogReader\LogReader;
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

    public function testAddField()
    {
        $reader = clone $this->reader;

        $reader
            ->addField('test_field', 'test_value')
            ->addField('date', function ($log) {
                return self::parseDate($log);
            })
            ->addField('stack', function ($log) {
                return self::parseStack($log);
            })
            ->addField('text', function ($log) {
                return $log;
            });

        $entries = $reader->getEntries();

        $this->assertInternalType('array', $entries);
        $this->assertTrue(count($entries) > 0);
        foreach ($entries as $entry) {
            $this->assertInternalType('array', $entry);
            $this->assertArrayHasKey('date', $entry);
            $this->assertArrayHasKey('stack', $entry);
            $this->assertArrayHasKey('text', $entry);
            if (strpos($entry['text'], 'Stack trace') === false) $this->assertEmpty($entry['stack']);
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

    public function testGetEntries2()
    {
        $reader = clone $this->reader;
        $entries = $reader
            ->addField('foo', 'bar')
            ->getEntries();

        $this->assertInternalType('array', $entries);
        $this->assertTrue(count($entries) > 0);
        foreach ($entries as $entry) {
            $this->assertInternalType('array', $entry);
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

    private static function parseDate($text)
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2}) \d{2}:\d{2}:\d{2}\].*/';
        preg_match_all($pattern, $text, $matches);
        return $matches[1][0];
    }

    private static function parseStack($text)
    {
        preg_match_all('/Stack trace:(.*)/s', $text, $matches);
        return (isset($matches[1][0])) ? $matches[1][0] : null;
    }
}
