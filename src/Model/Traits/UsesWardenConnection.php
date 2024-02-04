<?php

namespace Ananiaslitz\Matrix\Model\Traits;

trait UsesWardenConnection
{
    protected string $connection;

    /**
     * Inicializa a conexão do model para a conexão "warden".
     */
    public function initializeUsesWardenConnection(): void
    {
        $this->connection = 'warden';
    }
}
