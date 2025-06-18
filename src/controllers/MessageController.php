<?php

namespace controllers;
require_once BASE_PATH . '/src/models/MessageManager.php';

use Exception;
use models\Messages;
use models\MessageManager;

/**
 * @property MessageManager $messageManager
 */
class MessageController
{

    public function __construct()
    {
        $this->messageManager = new MessageManager();
    }

// CREATE
    public function createMessage($messageData)
    {
        if (!isset($messageData[Messages::NAME_COLUMN]) || !isset($messageData[Messages::EMAIL_COLUMN]) ||
            !isset($messageData[Messages::EMAIL_COLUMN])) {
            $this->sendResponse(false, null, 'Missing data');
            return;
        }

        try {
            $result = $this->messageManager->createMessage($messageData);

        } catch (Exception $e) {
            $this->sendResponse(true, 'ERROR', 'Failed to create message', $e->getMessage());
        }
        if (isset($result[Messages::ID_COLUMN]) && $result[Messages::ID_COLUMN] > 0) {
            $ok = $this->messageManager->sendConfirmationEmail($result);
            if ($ok === true) {
                $this->sendResponse(true, 'OK', null, $result);
            } else {
                $this->sendResponse(true, 'ERROR', $ok, $result);
            }

        } else {
            $this->sendResponse(true, 'ERROR', "?", $result['error']);
        }


    }

    public function getAllApprovedMessages($rate, $search, $from)
    {

        try {
            $result = $this->messageManager->getAllApprovedMessages($rate, $search, $from);
        } catch (\Exception $e) {
            $this->sendResponse(true, 'ERROR', $e->getMessage());
        }

        $this->sendResponse(true, null, null, $result);
    }

    public function sendResponse($success, $message = null, $fault = null, $data = null)
    {
        $statusCode = $success ? 200 : 400;
        http_response_code($statusCode);
        $message = $message ?? ($success ? 'OK' : 'failed');
        $response = [
            'success' => $success,
            'message' => $message,
            'fault' => $fault,
            'data' => $data
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function confirm($token): array
    {
        try {
            $result = $this->messageManager->confirm($token);
            if ($result['success']) {
                // todo send approval email to admin
                $ok = $this->messageManager->sendApprovalEmail($result['data']);
                if ($ok === true) {
                    $result = [
                        'success' => true,
                        'message' => 'OK',
                        'fault' => null,
                        'data' => null
                    ];
                } else {
                   // todo  mi legyen ilyenkor ? comfirm sikerült, de az Approval email küldés nem
                }
            }
        } catch (\Exception $e) {
           $result = [
               'success' => false,
               'message' => 'ERROR',
               'fault' => $e->getMessage(),
               'data' => null
           ];
        }
        return $result;

    }

    public function approve( $approve,$token): array
    {
        try {
            $result = $this->messageManager->approve($approve,$token);
        }catch (Exception $e){
            $result = [
                'success' => false,
                'message' => 'ERROR',
                'fault' => $e->getMessage(),
                'data' => null
            ];
        }
        return $result;
    }

}