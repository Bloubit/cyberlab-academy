<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lab_id' => $this->lab_id,
            'name' => $this->name,
            'image' => $this->image,
            'os' => $this->os,
            'ip_address' => $this->when($request->user()?->isAdmin(), $this->ip_address),
            'cpu_cores' => $this->cpu_cores,
            'ram_mb' => $this->ram_mb,
            'disk_gb' => $this->disk_gb,
            'ports' => $this->ports,
            'services' => $this->services,
        ];
    }
}
