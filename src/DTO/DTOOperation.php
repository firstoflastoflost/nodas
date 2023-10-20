<?php

use NW\WebService\References\Operations\Notification\Contractor;
use NW\WebService\References\Operations\Notification\Employee;
use NW\WebService\References\Operations\Notification\Seller;

class DTOOperation
{

    public int $notificationType;

    public int $resellerId;

    public int $clientId;

    public int $creatorId;

    public int $expertId;

    public int $complaintId;

    public string $complaintNumber;

    public int $consumptionId;

    public string $consumptionNumber;

    public string $agreementNumber;

    public string $date;

    public array $differencesAsArray;
    public string $differencesAsString;
    public Seller $seller;
    public Contractor $client;
    public Employee $creator;
    public Employee $expert;

    /**
     * @param array $data
     * @return $this
     */
    public function fill(array $data): DTOOperation
    {
        try {
            $this->notificationType = (int)$data['notificationType'];
            $this->clientId = (int)$data['clientId'];
            $this->creatorId = (int)$data['creatorId'];
            $this->expertId = (int)$data['expertId'];
            $this->complaintId = (int)$data['complaintId'];
            $this->complaintNumber = (string)$data['complaintNumber'];
            $this->consumptionId = (int)$data['consumptionId'];
            $this->consumptionNumber = (string)$data['consumptionNumber'];
            $this->agreementNumber = (string)$data['agreementNumber'];
            $this->date = (string)$data['date'];
            $this->differencesAsArray = (array)$data['differences'];
        } catch (Exception $exception) {
            throw new BadDataException();
        }

        return $this;
    }

    public function setSellerFromId(): DTOOperation
    {
        $seller = Seller::getById($this->resellerId);

        if(!$seller){
            throw new NotFoundRowException('Seller not found!', 400);
        }

        $this->seller = $seller;

        return $this;
    }

    public function getSeller(): Seller
    {
        return $this->seller;
    }

    public function setClientFromId(): DTOOperation
    {
        $client = Contractor::getById($this->clientId);

        if (!$client || $client->type !== Contractor::TYPE_CUSTOMER || $client->seller->id !== $this->resellerId) {
            throw new NotFoundRowException('Client not found!', 400);
        }

        $this->client = $client;

        return $this;
    }

    public function getClient(): Contractor
    {
        return $this->client;
    }

    public function setDifferencesAsArray(array $differencesAsArray): DTOOperation
    {
        $this->differencesAsArray = $differencesAsArray;

        return $this;
    }

    public function getDifferencesAsArray(): array
    {
        return $this->differencesAsArray;
    }

    public function setDifferencesAsString(string $differencesAsString): DTOOperation
    {
        $this->differencesAsString = $differencesAsString;

        return $this;
    }

    public function getDifferencesAsString(): string
    {
        return $this->differencesAsString;
    }

    public function setCreatorFromId(): DTOOperation
    {
        $creator = Employee::getById($this->creatorId);

        if(!$creator){
            throw new NotFoundRowException('Creator not found!', 400);
        }

        $this->creator = $creator;

        return $this;
    }

    public function getCreator(): Employee
    {
        return $this->creator;
    }

    public function setExpertFromId(): DTOOperation
    {
        $expert = Employee::getById($this->expertId);

        if(!$expert){
            throw new NotFoundRowException('Expert not found!', 400);
        }

        $this->expert = $expert;

        return $this;
    }

    public function getExpert(): Employee
    {
        return $this->expert;
    }

}