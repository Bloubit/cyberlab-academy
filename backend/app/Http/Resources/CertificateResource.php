<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'certificate_type' => $this->certificate_type,
            'title' => $this->title,
            'description' => $this->description,
            'issued_at' => $this->issued_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'pdf_url' => $this->pdf_url,
            'qr_code' => $this->qr_code,
            'verification_url' => $this->verification_url,
            'revoked' => $this->revoked,
            'user' => new UserResource($this->whenLoaded('user')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'lab' => new LabResource($this->whenLoaded('lab')),
        ];
    }
}
