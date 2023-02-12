<?php

namespace A17\TwillFirewall\Services;

use Illuminate\Support\Str;

trait IpUtils
{
    protected string|null $ipAddress = null;

    public function getIpAddress(): string|null
    {
        if ($this->ipAddress !== null) {
            return $this->ipAddress;
        }

        $this->ipAddress = $this->getAkamaiIpAddress() ?? $this->getForwardedIpaddress();

        if ($this->ipAddress !== null && !$this->isIPv6($this->ipAddress)) {
            $this->ipAddress = $this->removePortFromIPv4($this->ipAddress);
        }

        if ($this->ipAddress !== null) {
            return $this->ipAddress;
        }

        $this->ipAddress = request()->ip();

        if ($this->ipAddress !== null) {
            return $this->ipAddress;
        }

        /**
         * TODO: is there a way to ensure we will always have an IP here?
         */
        return $this->ipAddress = request()->getClientIp();
    }

    protected function isIPv6(string $ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    public function removePortFromIPv4(string $ipAddress): string
    {
        return Str::before($ipAddress, ':');
    }

    public function getAkamaiIpAddress(): ?string
    {
        return request()->header('True-Client-IP');
    }

    public function getForwardedIpaddress(): ?string
    {
        return explode(',', (string) request()->header('X-Forwarded-For'))[0] ?? null;
    }
}
