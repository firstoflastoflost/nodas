<?php

namespace NW\WebService\References\Operations\Notification;

class Seller extends Contractor{

    public function getResellerEmailFrom(): string
    {
        return 'contractor@example.com';
    }

    public function getEmailsByPermit(string $event): array
    {
        // fakes the method
        return ['someemeil@example.com', 'someemeil2@example.com'];
    }
}