<?php

namespace Nhd\Foundation\Http\Resources\Api\Client;

use Illuminate\Http\Resources\Json\JsonResource;

class ExternalLogin extends JsonResource
{
    public function toArray($request)
    {
        $expiredAt = now()->addSeconds(20)->timestamp;

        return [
            'code' => \Crypt::encrypt([
                'user_id' => $this->id,
                'expired_at' => $expiredAt
            ]),
            'expired_at' => $expiredAt
        ];
    }
}
