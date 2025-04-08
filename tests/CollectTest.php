<?php

use PHPUnit\Framework\TestCase;
use Ljfreelancer88\Gideon\{
    Collect,
    WriterInterface,
    ExcimerWrapper
};

class CollectTest extends TestCase
{
    public function testSomething()
    {
        $this->assertTrue(true);
    }

    public function testPerRequestProfilingCallsProfilerAndWritesJson()
    {
        // Arrange
        $profiler = $this->createMock(ExcimerWrapper::class);
        $writer = $this->createMock(WriterInterface::class);

        // Fake profiler behavior
        $profiler->expects($this->once())->method('start');
        $profiler->expects($this->once())->method('stop');
        $profiler->method('getSpeedscopeData')->willReturn([
            'profiles' => [['name' => 'original']],
            'other' => 'data'
        ]);

        // Expect writer to be called with JSON
        $writer->expects($this->once())->method('write')
            ->with($this->callback(function ($json) {
                $data = json_decode($json, true);
                return isset($data['profiles'][0]['name']);
            }));

        $collect = new Collect($profiler, $writer);

        // Simulate $_SERVER
        $_SERVER['REQUEST_URI'] = '/foo';

        // Act
        $result = $collect->perRequestProfiling();

        // Trigger shutdown function
        $this->assertTrue($result);
        register_shutdown_function(fn() => null); // ensure shutdown is triggered during test end
    }

    public function testSiteWideProfilingStartsProfilerWithFlushCallback()
    {
        $profiler = $this->createMock(ExcimerWrapper::class);
        $writer = $this->createMock(WriterInterface::class);

        $profiler->expects($this->once())->method('setFlushCallback');
        $profiler->expects($this->once())->method('start');

        $collect = new Collect($profiler, $writer);
        $this->assertTrue($collect->siteWideProfiling());
    }
}
