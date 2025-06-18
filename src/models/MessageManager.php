<?php

namespace models;
require_once BASE_PATH . '/src/models/Messages.php';

use DateTime;
use PDO;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once BASE_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once BASE_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once BASE_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';

define('EmailHost', isset($_ENV['MAIL_HOST']) ? $_ENV['MAIL_HOST'] : '');
define('EmailUsername', isset($_ENV['MAIL_USERNAME']) ? $_ENV['MAIL_USERNAME'] : '');
define('EmailPassword', isset($_ENV['MAIL_PASSWORD']) ? $_ENV['MAIL_PASSWORD'] : '');
define('EmailPort', isset($_ENV['MAIL_PORT']) ? $_ENV['MAIL_PORT'] : '587');
define('MAIL_SMTPSecure', isset($_ENV['MAIL_SMTPSecure']) ? $_ENV['MAIL_SMTPSecure'] : 'tls');
define('CharSet', isset($_ENV['MAIL_CHARSET']) ? $_ENV['MAIL_CHARSET'] : 'UTF-8');
define('Encoding', isset($_ENV['MAIL_ENCODING']) ? $_ENV['MAIL_ENCODING'] : 'base64');
define('is_html', isset($_ENV['MAIL_IS_HTML']) ? $_ENV['MAIL_IS_HTML'] : true);
define('EmailFrom', isset($_ENV['MAIL_FROM_ADDRESS']) ? $_ENV['MAIL_FROM_ADDRESS'] : '');
define('EmailFromName', isset($_ENV['MAIL_FROM_NAME']) ? $_ENV['MAIL_FROM_NAME'] : '');
define('SMTPAuth', isset($_ENV['MAIL_AUTH']) ? $_ENV['MAIL_AUTH'] : true);

define('ModeratorMail', isset($_ENV['MAIL_MODERATOR']) ? explode(',', $_ENV['MAIL_MODERATOR']) : []);


class MessageManager
{


    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createMessage(array $data): array
    {
        try {
            $name = $data[Messages::NAME_COLUMN];
            $email = $data[Messages::EMAIL_COLUMN];
            $message = $data[Messages::MESSAGE_COLUMN];
            $rate = $data[Messages::RATE_COLUMN] ?? 5; // alapértelmezett érték 5 :)
        } catch (Exception $e) {
            throw new Exception('Missing  data');
        }

        $confirmationToken = $this->generateUuid() . "_" . (new DateTime())->getTimestamp();

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO `" . Messages::TABLE_NAME .
                "` ( `" . Messages::NAME_COLUMN .
                "` , `" . Messages::EMAIL_COLUMN .
                "` , `" . Messages::MESSAGE_COLUMN .
                "` , `" . Messages::CONFIRMATION_TOKEN_COLUMN .
                "` , `" . Messages::RATE_COLUMN .
                "` ) VALUES (?, ?, ?, ?, ?)");

            if (!$stmt->execute([$name, $email, $message, $confirmationToken, $rate])) {
                throw new Exception('Registration failed');
            }

            $id = $this->db->lastInsertId();
            $this->db->commit();
        } catch
        (Exception $e) {
            $this->db->rollBack();
            return array("error" => $e->getMessage());
        }
        $data[Messages::ID_COLUMN] = $id;
        $data[Messages::CONFIRMATION_TOKEN_COLUMN] = $confirmationToken;
        return $data;
    }


    public function getMessageById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM `" . Messages::TABLE_NAME . "` WHERE `"
            . Messages::ID_COLUMN . "` = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllApprovedMessages($rate, $search, $from): array
    {
        if (empty($rate)) {
            $rate = [1, 2, 3, 4, 5]; // vagy: ne szűrj rate-re, vagy adj vissza minden rekordot
        }
        if (!is_array($rate)) {
            $d = $rate;
            $rate = [$d];
        }
        $placeholders = implode(',', array_fill(0, count($rate), '?'));
        $sql = "SELECT * FROM `" . Messages::TABLE_NAME . "`
            WHERE `" . Messages::APPROVED_COLUMN . "` = 1
              AND (`name` LIKE ? OR `message` LIKE ?)
              AND `rate` IN ($placeholders)
            ORDER BY `" . Messages::CREATED_AT_COLUMN . "` DESC";

        if (!isset($search)) {
            $search = '';
        }
        $params = array_merge(
            ["%$search%", "%$search%"],
            $rate
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($messages === false) {
            throw new Exception('Failed to fetch messages');
        }

        return $messages;
    }

    public function sendConfirmationEmail(array $messageData)
    {

        $mail = new PHPMailer(true);
        // todo ezek .env-ben kellene lennie
        try {
            $mail->isSMTP();
            $mail->Host = EmailHost;
            $mail->SMTPAuth = SMTPAuth;
            $mail->Username = EmailUsername;
            $mail->Password = EmailPassword;
            $mail->Port = EmailPort;
            $mail->SMTPSecure = MAIL_SMTPSecure;
            $mail->CharSet = CharSet;
            $mail->Encoding = Encoding;
            $mail->isHTML(is_html); // ha HTML e-mailt küldesz
            $mail->setFrom(EmailFrom, EmailFromName);
            $mail->addAddress($messageData[Messages::EMAIL_COLUMN], $messageData[Messages::NAME_COLUMN]);
            $mail->Subject = 'Vendégkönyv üzenet megerősítése';
            $params = [
                'action' => 'confirm',
                'token' => $messageData[Messages::CONFIRMATION_TOKEN_COLUMN]
            ];
            $confirmUrl = $this->getBaseUrl('/guestbook/') . '?' . http_build_query($params);

            $mail->Body = 'Kérlek, kattints a következő linkre a megerősítéshez: <br><br>' .
                '<a href="' . $confirmUrl . '">' . $confirmUrl . '</a>';

            $mail->isHTML(true);

            $mail->send();
        } catch (Exception $e) {

            return $e->getMessage();
        }
        return true;
    }

    public function confirm($token): array
    {
        if (!$this->messageExists($token, 0)) {
            return [
                'success' => false,
                'message' => 'ERROR',
                'fault' => 'Invalid confirmation token',
                'data' => null
            ];
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM " . Messages::TABLE_NAME .
                " WHERE " . Messages::CONFIRMATION_TOKEN_COLUMN . " = ?"
            );
            $stmt->execute([$token]);
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $this->db->prepare(
                "UPDATE `" . Messages::TABLE_NAME .
                "` SET `" . Messages::IS_CONFIRMED_COLUMN . "` = 1 " .
                "WHERE `" . Messages::CONFIRMATION_TOKEN_COLUMN . "` = ?"
            );
            if (!$stmt->execute([$token])) {
                throw new Exception('Failed to confirm message');
            }
            $result = [
                'success' => true,
                'message' => 'OK',
                'fault' => null,
                'data' => $message
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'ERROR',
                'fault' => $e->getMessage(),
                'data' => null
            ];
        }
        return $result;
    }

    private function messageExists($confirmationToken, $confirmed = 1): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `" . Messages::TABLE_NAME . "` WHERE `" . Messages::CONFIRMATION_TOKEN_COLUMN .
            "` = ? AND `" . Messages::IS_CONFIRMED_COLUMN . "` = ?");
        $stmt->execute([$confirmationToken, $confirmed]);
        return $stmt->fetchColumn() == 1;
    }


    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    function getBaseUrl($appendPath = ''): string
    {

        $protocol = 'https://';
        if (
            (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        ) {
            $protocol = 'https://';
        }
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . $host . $appendPath;
    }

    public function approve(bool $approved, $token): array
    {
        if (!$this->messageExists($token)) {
            return [
                'success' => false,
                'message' => 'ERROR',
                'fault' => 'Invalid confirmation token',
                'data' => null
            ];
        }

        try {
            $stmt = $this->db->prepare(

                "UPDATE `" . Messages::TABLE_NAME
                . "` SET `" . Messages::APPROVED_COLUMN . "` = ? " .
                " WHERE `" . Messages::CONFIRMATION_TOKEN_COLUMN . "` = ?"

            );
            $approvedInt = $approved ? 1 : 0;
            if (!$stmt->execute([$approvedInt, $token])) {
                throw new Exception('Failed to confirm message');
            }
            $result = [
                'success' => true,
                'message' => 'OK',
                'fault' => null,
                'data' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'ERROR',
                'fault' => $e->getMessage(),
                'data' => null
            ];
        }
        return $result;
    }

    public function sendApprovalEmail($messageData)
    {
        $mail = new PHPMailer(true);
        // todo ezek .env-ben kellene lennie
        try {
            $mail->isSMTP();
            $mail->Host = EmailHost;
            $mail->SMTPAuth = SMTPAuth;
            $mail->Username = EmailUsername;
            $mail->Password = EmailPassword;
            $mail->Port = EmailPort;
            $mail->SMTPSecure = MAIL_SMTPSecure;
            $mail->CharSet = CharSet;
            $mail->Encoding = Encoding;
            $mail->isHTML(is_html); // ha HTML e-mailt küldesz
            $mail->setFrom(EmailFrom, EmailFromName);
            foreach (ModeratorMail as $email) {
                $mail->addAddress(trim($email), 'Moderator');
            }
            $mail->Subject = 'Moderálás';
            $params = [
                'action' => 'approve',
                'token' => $messageData[Messages::CONFIRMATION_TOKEN_COLUMN]
            ];
            $approveUrl = $this->getBaseUrl('/guestbook/') . '?' . http_build_query($params);
            $params = [
                'action' => 'dont_approve',
                'token' => $messageData[Messages::CONFIRMATION_TOKEN_COLUMN]
            ];
            $dontApproveUrl = $this->getBaseUrl('/guestbook/') . '?' . http_build_query($params);
            $mail->Body = 'E-mail: ' . $messageData[Messages::EMAIL_COLUMN] . '<br>';
            $mail->Body .= 'Név: ' . $messageData[Messages::NAME_COLUMN] . '<br>';
            $mail->Body .= 'Dátum: ' . $messageData[Messages::CREATED_AT_COLUMN] . '<br>';
            $mail->Body .= 'Értékelés: ' . $messageData[Messages::RATE_COLUMN] . '<br>';
            $mail->Body .= 'Üzenet: ' . $messageData[Messages::MESSAGE_COLUMN] . '<br>';
            $mail->Body .= '<br> ✔  -> ' . '<a href="' . $approveUrl . '">Jóváhagyás</a>';
            $mail->Body .= '<br> ❌ -> ' . '<a href="' . $dontApproveUrl . '">Elutasítás</a>';

            $mail->isHTML(true);

            $mail->send();
        } catch (Exception $e) {

            return $e->getMessage();
        }
        return true;
    }

}