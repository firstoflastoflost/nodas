<?php

namespace NW\WebService\References\Operations\Notification;

use Exception;
use NotificationEvents;

class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW = 1;
    public const TYPE_CHANGE = 2;

    private \DTOOperation $DTOOperation;
    private \ValidatorOperationRequest $validator;
    private \OperationResponse $operationResponse;

    public function __construct()
    {
        $this->DTOOperation = new \DTOOperation();
        $this->validator = new \ValidatorOperationRequest();
        $this->operationResponse = new \OperationResponse();
    }

    /**
     * @throws Exception
     */
    public function doOperation(): array
    {
        $data = (array)$this->getRequest('data');

        # Наверх уйдет RuntimeException
        $this->validator->isAllDataPassed($data);

        # Наверх уйдет BadDataException
        $this->DTOOperation->fill($data);

        # Если на resellerId нужно отдельное сообщение
        if (!array_key_exists('resellerId', $data) || empty($data['resellerId'])) {
            $this->operationResponse->notificationClientBySms['message'] = 'Empty resellerId';
        }

        # Наверх уйдет NotFoundRowException
        $this->DTOOperation
            ->setSellerFromId()
            ->setClientFromId()
            ->setCreatorFromId()
            ->setExpertFromId()
            ->setDifferencesAsString($this->generateDifferencesAtString());

        $templateData = $this->prepareTemplateData();

        $this->sendNotifyToEmployers($templateData);
        $this->sendNotifyToClient($templateData);

        return $this->operationResponse->toArray();
    }

    /**
     * @throws Exception
     */
    private function generateDifferencesAtString(): string
    {
        $differencesAsArray = $this->DTOOperation->getDifferencesAsArray()['differences'];

        if ($this->DTOOperation->notificationType === self::TYPE_NEW) {
            $differences = __('NewPositionAdded', null, $this->DTOOperation->resellerId);
        } elseif ($this->DTOOperation->notificationType === self::TYPE_CHANGE && !empty($differencesAsArray)) {
            $differences = __('PositionStatusHasChanged', [
                'FROM' => Status::getName((int)$differencesAsArray['from']),
                'TO'   => Status::getName((int)$differencesAsArray['to']),
            ], $this->DTOOperation->resellerId);
        } else {
            throw new \RuntimeException("Необработанный тип уведомления");
        }

        return $differences;
    }

    private function prepareTemplateData(): array
    {
        return [
            'COMPLAINT_ID'       => $this->DTOOperation->complaintId,
            'COMPLAINT_NUMBER'   => $this->DTOOperation->complaintNumber,
            'CREATOR_ID'         => $this->DTOOperation->creatorId,
            'CREATOR_NAME'       => $this->DTOOperation->getCreator()->getFullName(),
            'EXPERT_ID'          => $this->DTOOperation->expertId,
            'EXPERT_NAME'        => $this->DTOOperation->getExpert()->getFullName(),
            'CLIENT_ID'          => $this->DTOOperation->clientId,
            'CLIENT_NAME'        => $this->DTOOperation->getClient()->getFullName(),
            'CONSUMPTION_ID'     => $this->DTOOperation->consumptionId,
            'CONSUMPTION_NUMBER' => $this->DTOOperation->consumptionNumber,
            'AGREEMENT_NUMBER'   => $this->DTOOperation->agreementNumber,
            'DATE'               => $this->DTOOperation->date,
            'DIFFERENCES'        => $this->DTOOperation->getDifferencesAsString(),
        ];
    }

    private function sendNotifyToEmployers($templateData): void
    {
        $emailFrom = $this->DTOOperation->getSeller()->getResellerEmailFrom();
        $emails = $this->DTOOperation->getSeller()->getEmailsByPermit('tsGoodsReturn');
        if (!empty($emailFrom) && count($emails) > 0) {
            foreach ($emails as $email) {
                MessagesClient::sendMessage([
                    [
                        'emailFrom' => $emailFrom,
                        'emailTo'   => $email,
                        'subject'   => __(
                            'complaintEmployeeEmailSubject',
                            $templateData,
                            $this->DTOOperation->resellerId
                        ),
                        'message'   => __('complaintEmployeeEmailBody', $templateData, $this->DTOOperation->resellerId),
                    ],
                ], $this->DTOOperation->resellerId, NotificationEvents::CHANGE_RETURN_STATUS);
                $this->operationResponse->notificationEmployeeByEmail = true;

            }
        }
    }

    private function sendNotifyToClient($templateData): void
    {
        $emailFrom = $this->DTOOperation->getSeller()->getResellerEmailFrom();
        $differencesAsArray = $this->DTOOperation->getDifferencesAsArray();
        $client = $this->DTOOperation->getClient();

        if ($this->DTOOperation->notificationType !== self::TYPE_CHANGE || empty($differencesAsArray['to'])) {
            return;
        }

        if (!empty($emailFrom) && !empty($client->email)) {
            MessagesClient::sendMessage([
                [
                    'emailFrom' => $emailFrom,
                    'emailTo'   => $client->email,
                    'subject'   => __(
                        'complaintClientEmailSubject',
                        $templateData,
                        $this->DTOOperation->resellerId
                    ),
                    'message'   => __(
                        'complaintClientEmailBody',
                        $templateData,
                        $this->DTOOperation->resellerId
                    ),
                ],
            ],
                $this->DTOOperation->resellerId,
                $client->id,
                NotificationEvents::CHANGE_RETURN_STATUS,
                $differencesAsArray['to']);

            $this->operationResponse->notificationClientByEmail = true;
        }

        if (!empty($client->mobile)) {
            $resultSendClientMobile = NotificationManager::send(
                $this->DTOOperation->resellerId,
                $client->id,
                NotificationEvents::CHANGE_RETURN_STATUS,
                $differencesAsArray['to'],
                $templateData,
                //$error - имелось ввиду $error = '' и передача по ссылке &$error ?
            );
            if ($resultSendClientMobile) {
                $this->operationResponse->notificationClientBySms['isSent'] = true;
            }
            if (!empty($resultSendClientMobile['error'])) {
                $this->operationResponse->notificationClientBySms['message'] = $resultSendClientMobile['error'];
            }
        }

    }
}