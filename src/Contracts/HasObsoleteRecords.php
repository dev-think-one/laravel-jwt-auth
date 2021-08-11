<?php

namespace JWTAuth\Contracts;

interface HasObsoleteRecords
{
    /**
     * Remove outdated black list records
     *
     * @return bool
     */
    public function removeObsoleteRecords(): bool;

    /**
     * Minutes before list expires
     *
     * @return int
     */
    public function minutesToObsolescence(): int;
}
