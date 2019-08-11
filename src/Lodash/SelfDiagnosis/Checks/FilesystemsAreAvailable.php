<?php
/*
 * This file is part of the Laravel Lodash package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Longman\LaravelLodash\SelfDiagnosis\Checks;

use BeyondCode\SelfDiagnosis\Checks\Check;
use Illuminate\Filesystem\FilesystemManager;
use Throwable;

use function app;
use function implode;
use function now;
use function trans;

use const PHP_EOL;

class FilesystemsAreAvailable implements Check
{
    /** @var \Illuminate\Filesystem\FilesystemManager */
    private $filesystemManager;

    /** @var array */
    private $options = [];

    public function name(array $config): string
    {
        return trans('lodash::checks.filesystems_are_available.name');
    }

    public function check(array $config): bool
    {
        $this->filesystemManager = app(FilesystemManager::class);

        foreach ($config['disks'] as $disk) {
            try {
                $status = $this->checkDisk($disk);
            } catch (Throwable $e) {
                $this->options[$disk] = $e->getMessage();
                continue;
            }

            if (! $status) {
                $this->options[$disk] = false;
            }
        }

        return count($this->options) === 0;
    }

    private function checkDisk(string $disk): bool
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $diskInstance */
        $diskInstance = $this->filesystemManager->disk($disk);

        $file = 'test/test.txt';

        $status = $diskInstance->put($file, now()->toString());
        if (! $status) {
            return false;
        }

        $status = $diskInstance->delete($file);
        if (! $status) {
            return false;
        }

        return $status;
    }

    public function message(array $config): string
    {
        $options = [];
        foreach ($this->options as $option => $value) {
            $message = 'Disk "' . $option . '" is not available';
            if (! empty($value)) {
                $message .= '. Reason: ' . $value;
            }

            $options[] = $message;
        }

        return trans('lodash::checks.filesystems_are_available.message', [
            'options' => implode(PHP_EOL, $options),
        ]);
    }
}
