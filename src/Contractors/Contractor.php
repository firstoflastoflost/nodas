<?php

namespace NW\WebService\References\Operations\Notification;

class Contractor
{
    public const TYPE_CUSTOMER = 0;
    public int $id;
    public int $type;
    public string $name;
    public string $email;
    public string $mobile;
    public Seller $seller;

    public static function getById(int $resellerId): ?self
    {
        return new self($resellerId); // fakes the getById method
    }

    public function getFullName(): string
    {
        return ($this->name.' '.$this->id) ?? "Неизвестно";
    }
}