<?php

class Driver{
    private $id;
    private $name;
    private $surname;
    private $phone;
    private $email;
    private $license_expiry;
    private $medical_exam_expiry;
    private $driver_status;
    private $photo;


    public function __construct(
        int $id = null,
        string $name,
        string $surname,
        string $phone,
        string $email,
        string $license_expiry,
        string $medical_exam_expiry,
        string $driver_status,
        ?string $photo = 'avatar.jpg'
        ) {
        $this->id = $id;
        $this->setName($name);
        $this->setSurname($surname);
        $this->phone = $phone;
        $this->setEmail($email);
        $this->license_expiry = $license_expiry;
        $this->medical_exam_expiry = $medical_exam_expiry;
        $this->setDriverStatus($driver_status);
        $this->photo = $photo;
    }

    public function getId(): ?int{
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty");
        }
        $this->name = ucfirst(strtolower(trim($name)));
    }

    public function getSurname(): string{
        return $this->surname;
    } 

    public function setSurname(string $surname)
    {
        if (empty($surname)) {
            throw new InvalidArgumentException("Surname cannot be empty");
        }
        $this->surname = ucfirst(strtolower(trim($surname)));
    }

    public function getPhone(): string{
        return $this->phone;
    } 

    public function setPhone(string $phone){
        $this->phone = $phone;
    } 

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format.");
        }
        $this->email = $email;
    }

    public function getLicenseExpiry(): string {
        return $this->license_expiry;
    }

    public function setLicenseExpiry(string $license_expiry): void {
        $this->license_expiry = $license_expiry;
    }

    public function getMedicalExamExpiry(): string {
        return $this->medical_exam_expiry;
    }

    public function setMedicalExamExpiry(string $medical_exam_expiry): void {
        $this->medical_exam_expiry = $medical_exam_expiry;
    }

    public function getDriverStatus(): string {
        return $this->driver_status;
    }

    public function setDriverStatus(string $status): void {
        $validStatuses = ['available', 'on_road', 'on_leave'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid driver status.");
        }
        $this->driver_status = $status;
    }

    public function getPhoto(): ?string {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void {
        $this->photo = $photo;
    }
}