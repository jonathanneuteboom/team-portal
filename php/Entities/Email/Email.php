<?php

namespace TeamPortal\Entities;

use TeamPortal\Common\Utilities;

abstract class Email
{
    public ?Persoon $sender;
    public Persoon $receiver;
    public string $titel;
    public string $body;
    private string $signature;

    function GetSignature()
    {
        return $this->signature;
    }

    function SetSender(Persoon $persoon)
    {
        $this->sender = $persoon;
    }

    function IsValid(): bool
    {
        $isInvalid =
            Utilities::IsNullOrEmpty($this->receiver->naam) ||
            Utilities::IsNullOrEmpty($this->receiver->email) ||
            Utilities::IsNullOrEmpty($this->titel) ||
            Utilities::IsNullOrEmpty($this->body);
        return !$isInvalid;
    }

    function Build()
    {
        $this->sender = $this->sender ?? new Persoon(-1, "SKC Studentenvolleybal", "info@skcvolleybal.nl");
        $this->CalculateSignature();
    }

    private function CalculateSignature(): void
    {
        $concatenatedEmail = $this->sender->email . $this->receiver->email . $this->titel . $this->body;
        $this->signature = hash("sha1", $concatenatedEmail);
    }
}
