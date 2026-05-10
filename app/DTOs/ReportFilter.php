<?php

namespace App\DTOs;

class ReportFilter
{
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
        public ?int $doctorId = null,
        public ?int $departmentId = null,
        public ?string $invoiceType = null,
        public ?int $wardId = null,
        public ?string $paymentMethod = null,
    ) {
        $this->from = $this->from ?: now()->startOfMonth()->toDateString();
        $this->to = $this->to ?: now()->toDateString();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            doctorId: isset($data['doctor_id']) ? (int) $data['doctor_id'] : null,
            departmentId: isset($data['department_id']) ? (int) $data['department_id'] : null,
            invoiceType: $data['invoice_type'] ?? null,
            wardId: isset($data['ward_id']) ? (int) $data['ward_id'] : null,
            paymentMethod: $data['payment_method'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'doctor_id' => $this->doctorId,
            'department_id' => $this->departmentId,
            'invoice_type' => $this->invoiceType,
            'ward_id' => $this->wardId,
            'payment_method' => $this->paymentMethod,
        ];
    }
}
