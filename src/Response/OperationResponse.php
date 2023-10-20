<?php

class OperationResponse
{
    public bool $notificationEmployeeByEmail = false;
    public bool $notificationClientByEmail   = false;
    public array $notificationClientBySms    = [
        'isSent'  => false,
        'message' => '',
    ];

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}