<?php

class ValidatorOperationRequest
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

    public array $requiredParams = [
        'notificationType',
        # 'resellerId', В текущем контексте необязательный, так как нужно отдавать сообщение, а не исключение
        'clientId',
        'creatorId',
        'expertId',
        'complaintId',
        'complaintNumber',
        'consumptionId',
        'consumptionNumber',
        'agreementNumber',
        'date'
    ];

    public function isAllDataPassed($data): bool
    {
        foreach ($this->requiredParams as $param) {
            if(!array_key_exists($data, $param)){
                throw new RuntimeException( "Template Data ({$param}) is empty");
            }
        }

        return true;
    }
}