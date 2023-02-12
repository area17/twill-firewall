<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Str;

trait IpUtils
{
    protected $ipAddress = null;

    public function getIpAddress()
    {
        if ($this->ipAddress !== null) {
            return $this->ipAddress;
        }

        $this->ipAddress = $this->getAkamaiIpAddress() ?? $this->getForwardedIpaddress();

        if (!$this->isIPv6($this->ipAddress)) {
            $this->ipAddress = $this->removePortFromIPv4($this->ipAddress);
        }

        if (filled($this->ipAddress)) {
            return $this->ipAddress;
        }

        if (filled($this->ipAddress = request()->ip())) {
            return $this->ipAddress;
        }

        return $this->ipAddress = request()->getClientIp();
    }

    protected function isIPv6($ipAddress)
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    public function removePortFromIPv4(array|string|null $ipAddress): string
    {
        return Str::before($ipAddress, ':');
    }

    public function getAkamaiIpAddress(): ?string
    {
        return request()->header('True-Client-IP');
    }

    public function getForwardedIpaddress(): ?string
    {
        return explode(',', request()->header('X-Forwarded-For'))[0] ?? null;
    }
}
