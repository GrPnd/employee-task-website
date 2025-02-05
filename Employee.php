<?php

class Employee {

    public ?string $id;
    public string $firstName;
    public string $lastName;
    public ?string $picture;

    public function __construct(?string $id, string $firstName, string $lastName, ?string $picture) {
        $this->id = $id;
        $this->firstName= $firstName;
        $this->lastName = $lastName;
        $this->picture = $picture;
    }

    public function __toString(): string {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }
}