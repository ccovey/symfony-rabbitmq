<?php

namespace Ccovey\SymfonyRabbitMQBundle;

class MemoryManager
{
    /**
     * @throws OutOfMemoryException When the memory limit is exceeded.
     */
    public function checkMemoryUsage(int $memoryLimit)
    {
        $memoryLimitBytes = $this->convertMegabytesToBytes($memoryLimit);
        $usedBytes = $this->getCurrentMemory();
        $usedMegabytes = $this->convertBytesToMegabytes($usedBytes);

        // don't let daemon memory exceed runtime config controlled limit
        if ($usedBytes >= $memoryLimitBytes) {
            throw new OutOfMemoryException(sprintf(
                'Reached memory limit of %s. Used %s. Stopping daemon.',
                $memoryLimit,
                $usedMegabytes
            ));
        }
    }

    private function convertBytesToMegabytes(int $bytes) : float
    {
        return round($bytes / 1000000, 2);
    }

    private function convertMegabytesToBytes(int $megabytes) : int
    {
        return $megabytes * 1024 * 1024;
    }

    protected function getCurrentMemory() : int
    {
        return memory_get_usage(true);
    }
}
