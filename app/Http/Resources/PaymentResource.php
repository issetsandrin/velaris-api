<?php

namespace App\Http\Resources;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'method' => $this->method,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'installments' => $this->installments,
            'pixPayload' => $this->pix_payload,
            'pixQrCode' => $this->pix_payload ? $this->qrCode($this->pix_payload) : null,
            'pixExpiresAt' => $this->pix_expires_at?->toIso8601String(),
            'cardBrand' => $this->card_brand,
            'cardLast4' => $this->card_last4,
            'failureReason' => $this->failure_reason,
            'paidAt' => $this->paid_at?->toIso8601String(),
        ];
    }

    /**
     * PNG em data URI, sem arquivo salvo. O fundo sai transparente (alfa 127 no
     * GD) para o código assentar no card da loja, sem moldura branca.
     */
    private function qrCode(string $conteudo): string
    {
        $png = (new Builder(
            writer: new PngWriter,
            data: $conteudo,
            size: 640,
            margin: 0,
            foregroundColor: new Color(74, 56, 24),
            backgroundColor: new Color(255, 255, 255, 127),
        ))->build();

        return 'data:image/png;base64,'.base64_encode($png->getString());
    }
}
