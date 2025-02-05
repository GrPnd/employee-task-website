<?php

class Task {

    public ?string $id;
    public string $description;
    public int $estimate;
    public int $employeeId;
    public string $status;

    public function __construct(?string $id, string $description, int $estimate, int $employeeId, string $status) {
        $this->id = $id;
        $this->description= $description;
        $this->estimate = $estimate;
        $this->employeeId = $employeeId;
        $this->status = $status;
    }

    public function __toString(): string {
        return sprintf('%s', $this->description);
    }

}