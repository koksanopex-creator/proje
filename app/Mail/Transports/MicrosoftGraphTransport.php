<?php

namespace App\Mail\Transports;

use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

class MicrosoftGraphTransport extends AbstractTransport
{
    protected string $tenantId;
    protected string $clientId;
    protected string $clientSecret;
    protected string $fromAddress;

    public function __construct(string $tenantId, string $clientId, string $clientSecret, string $fromAddress)
    {
        parent::__construct();
        $this->tenantId = $tenantId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->fromAddress = $fromAddress;
    }

    protected function doSend(SentMessage $message): void
    {
        /** @var Email $email */
        $email = $message->getOriginalMessage();

        // 1. ADIM: Azure AD OAuth2 Token Al
        $tokenResponse = Http::withoutVerifying()->asForm()->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => 'https://graph.microsoft.com/.default',
            'grant_type'    => 'client_credentials',
        ]);

        if (!$tokenResponse->successful()) {
            $errDesc = $tokenResponse->json('error_description') ?? $tokenResponse->body();
            throw new Exception("Microsoft Graph Token Hatası: " . $errDesc);
        }

        $token = $tokenResponse->json('access_token');
        if (!$token) {
            throw new Exception("Microsoft Graph Access Token bulunamadı.");
        }

        // 2. ADIM: Alıcıları Hazırla
        $toRecipients = [];
        foreach ($email->getTo() as $address) {
            $toRecipients[] = [
                'emailAddress' => [
                    'address' => $address->getAddress(),
                    'name'    => $address->getName() ?: null,
                ]
            ];
        }

        $ccRecipients = [];
        foreach ($email->getCc() as $address) {
            $ccRecipients[] = [
                'emailAddress' => [
                    'address' => $address->getAddress(),
                    'name'    => $address->getName() ?: null,
                ]
            ];
        }

        $bccRecipients = [];
        foreach ($email->getBcc() as $address) {
            $bccRecipients[] = [
                'emailAddress' => [
                    'address' => $address->getAddress(),
                    'name'    => $address->getName() ?: null,
                ]
            ];
        }

        // 3. ADIM: İçeriği Hazırla
        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        $content = $htmlBody ? (is_resource($htmlBody) ? stream_get_contents($htmlBody) : $htmlBody) : (is_resource($textBody) ? stream_get_contents($textBody) : $textBody);
        $contentType = $htmlBody ? 'HTML' : 'Text';

        $messagePayload = [
            'subject' => $email->getSubject() ?: 'Köksan Bildirimi',
            'body' => [
                'contentType' => $contentType,
                'content' => $content ?: '',
            ],
            'toRecipients' => $toRecipients,
        ];

        if (!empty($ccRecipients)) {
            $messagePayload['ccRecipients'] = $ccRecipients;
        }

        if (!empty($bccRecipients)) {
            $messagePayload['bccRecipients'] = $bccRecipients;
        }

        // Ekler (varsa)
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => $attachment->getFilename() ?? 'ek',
                'contentType' => $attachment->getContentType() ?? 'application/octet-stream',
                'contentBytes' => base64_encode($attachment->getBody()),
            ];
        }
        if (!empty($attachments)) {
            $messagePayload['attachments'] = $attachments;
        }

        // 4. ADIM: Microsoft Graph API sendMail Çağrısı
        $sendResponse = Http::withoutVerifying()
            ->withToken($token)
            ->post("https://graph.microsoft.com/v1.0/users/{$this->fromAddress}/sendMail", [
                'message' => $messagePayload,
                'saveToSentItems' => 'false',
            ]);

        if (!$sendResponse->successful()) {
            $err = $sendResponse->json('error.message') ?? $sendResponse->body();
            throw new Exception("Microsoft Graph Gönderim Hatası ({$sendResponse->status()}): " . $err);
        }
    }

    public function __toString(): string
    {
        return 'microsoft-graph';
    }
}
