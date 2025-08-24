<?php

namespace App\Services;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class SmsService
{
    protected static function client()
    {
        return new SnsClient([
            'region' => config('services.sns.region', env('AWS_DEFAULT_REGION')),
            'version' => '2010-03-31',
            'credentials' => [
                'key'    => config('services.sns.key', env('AWS_ACCESS_KEY_ID')),
                'secret' => config('services.sns.secret', env('AWS_SECRET_ACCESS_KEY')),
            ],
        ]);
    }

    public static function send($phoneNumber, $message)
    {
        try {
            $result = self::client()->publish([
                'Message' => $message,
                'PhoneNumber' => $phoneNumber,
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SMSType'  => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional'
                    ],
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => env('AWS_SNS_SENDER_ID', 'HostelSys')
                    ],
                ]
            ]);

            return [
                'success' => true,
                'message_id' => $result['MessageId'] ?? null
            ];
        } catch (AwsException $e) {
            return [
                'success' => false,
                'error' => $e->getAwsErrorMessage()
            ];
        }
    }
}
