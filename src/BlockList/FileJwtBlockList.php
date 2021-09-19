<?php

namespace JWTAuth\BlockList;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use JWTAuth\Contracts\HasObsoleteRecords;
use JWTAuth\Contracts\JwtBlockListContract;
use JWTAuth\Exceptions\JWTConfigurationException;
use JWTAuth\JWTManager;

/**
 * Class FileJwtBlockList
 * @package JWTAuth\BlockList
 */
class FileJwtBlockList implements JwtBlockListContract, HasObsoleteRecords
{

    /**
     * Filesystem disk name
     * @var string
     */
    protected string $disk;

    /**
     * Filesystem directory name
     * @var string
     */
    protected string $directory;

    /**
     * Minutes count before blocklist removing
     * @var int
     */
    protected int $minutesToObsolescence;

    /**
     * Seconds for remove obsoleted files
     * @var int
     */
    protected int $periodRemoveObsolete;

    public function __construct(array $configs = [])
    {
        $this->disk = $configs['disk'] ?? '';
        throw_if(empty($this->disk), JWTConfigurationException::class, 'FileJwtBlockList: Disk is empty');

        $this->directory = rtrim($configs['directory'] ?? '', '/');
        throw_if(empty($this->directory), JWTConfigurationException::class, 'FileJwtBlockList: Directory is empty');

        $this->minutesToObsolescence = (int) ($configs['minutes_to_obsolescence'] ?? 0);
        $this->periodRemoveObsolete  = (int) ($configs['remove_obsoleted_each_x_seconds'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function add(JWTManager $token): static
    {
        $this->maybeRemoveObsoleteRecords();
        if ($token->payload()->isValid()) {
            $data                       = $this->getFileData($token->payload()->exp());
            $data[ $token->getToken() ] = Carbon::now()->toString();

            Storage::disk($this->disk)->makeDirectory($this->directory);
            Storage::disk($this->disk)->put("{$this->directory}/{$token->payload()->exp()}{$this->fileExtension()}", json_encode($data));
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isBlockListed(JWTManager $token): bool
    {
        if (!$token->payload()->isValid()) {
            return true;
        }
        $data = $this->getFileData($token->payload()->exp());

        return isset($data[ $token->getToken() ]);
    }

    /**
     * Get content of blocklist file
     *
     * @param string $filePrefix
     *
     * @return array
     */
    protected function getFileData(string $filePrefix): array
    {
        try {
            $data = Storage::disk($this->disk)->get("{$this->directory}/{$filePrefix}{$this->fileExtension()}");
            $data = json_decode($data, true);
            if (!$data) {
                $data = [];
            }
        } catch (\Exception $e) {
            // File not found
            return [];
        }

        return $data;
    }

    /**
     * Remove obsolete records if not removed recently
     *
     * @return bool
     */
    protected function maybeRemoveObsoleteRecords(): bool
    {
        if (!Cache::has('__FileJwtBlackList_REMOVED')) {
            Cache::put('__FileJwtBlackList_REMOVED', true, $this->periodRemoveObsolete);

            return $this->removeObsoleteRecords();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeObsoleteRecords(): bool
    {
        collect(Storage::disk($this->disk)->listContents($this->directory, true))
            ->each(function ($file) {
                if (
                    $file['type'] == 'file' &&
                    $file['timestamp'] < Carbon::now()->subMinutes($this->minutesToObsolescence())->getTimestamp()
                ) {
                    Storage::disk($this->disk)->delete($file['path']);
                }
            });

        return true;
    }

    /**
     * @inheritDoc
     */
    public function minutesToObsolescence(): int
    {
        return $this->minutesToObsolescence;
    }

    /**
     * File extension.
     *
     * @return string
     */
    protected function fileExtension(): string
    {
        return '.json';
    }
}
