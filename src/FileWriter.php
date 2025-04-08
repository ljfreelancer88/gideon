<?php declare(strict_types=1);

namespace Ljfreelancer88\Gideon;

final class FileWriter implements WriterInterface
{
    private $fileHandle;

    public function __construct(private string $path)
    {
        $this->fileHandle = fopen($path, 'a');
        if ($this->fileHandle === false) {
            throw new \RuntimeException("Unable to open file: {$path}");
        }
    }

    public function write(string $content): void
    {
        if (fwrite($this->fileHandle, $content) === false) {
            error_log("Failed to write profiler data to file");
        }
        fflush($this->fileHandle);
    }

    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }
}
