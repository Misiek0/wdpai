<?php

class Vehicle{
    private $brand;
    private $model;
    private $reg_number;
    private $mileage;
    private $vehicle_inspection_expiry;
    private $oc_ac_expiry;
    private $vin;
    private $photo;
    private $avg_fuel_consumption;
    private $status;
    private $current_latitude;
    private $current_longitude;
    private $id;


    public function __construct(string $brand,string $model,string $reg_number,int $mileage,string $vehicle_inspection_expiry,string $oc_ac_expiry,string $vin,?string $photo = null, int $id = null, string $status = 'available' , ?float $avg_fuel_consumption = null,  ?float $current_latitude = null, ?float $current_longitude = null) {
        $this->setBrand($brand);
        $this->setModel($model);
        $this->reg_number = $reg_number;
        $this->mileage = $mileage;
        $this->vehicle_inspection_expiry = $vehicle_inspection_expiry;
        $this->oc_ac_expiry = $oc_ac_expiry;
        $this->vin = $vin;
        $this->photo = $photo;
        $this->id = $id;
        $this->status = $status;
        $this->avg_fuel_consumption = $avg_fuel_consumption;
        $this->current_latitude = $current_latitude;
        $this->current_longitude = $current_longitude;
    }

public function getId(): ?int {
    return $this->id;
}

public function getBrand(): string
{
    return $this->brand;
}

public function setBrand(string $brand)
{
    if (empty($brand)) {
        throw new InvalidArgumentException("Brand cannot be empty");
    }
    $this->brand = ucfirst(strtolower(trim($brand)));
}

public function getModel(): string
{
    return $this->model;
}

public function setModel(string $model)
{
    if (empty($model)) {
        throw new InvalidArgumentException("Model cannot be empty");
    }
    $this->model = ucfirst(strtolower(trim($model)));
}

public function getRegNr(): string
{
    return $this->reg_number;
}

public function setRegNr(string $reg_number)
{
    $this->reg_number = $reg_number;
}

public function getMileage() : int
{
    return $this->mileage;
}

public function setMileage(int $mileage)
{
    $this->mileage = $mileage;
}

public function getInspectionDate() : string
{
    $date = new DateTime($this->vehicle_inspection_expiry);
    return $date->format('Y-m-d'); 
}

public function setInspectionDate(string $date)
{
    $this->vehicle_inspection_expiry = (new DateTime($date))->format('Y-m-d');
}

public function getOCACDate() : string
{
    $date = new DateTime($this->oc_ac_expiry);
    return $date->format('Y-m-d'); 
}

public function setOCACDate(string $date)
{
    $this->oc_ac_expiry = (new DateTime($date))->format('Y-m-d');
}

public function getVin() : string
{
    return $this->vin;
}

public function setVin(string $vin)
{
    $this->vin = $vin;
}

public function getPhoto() : string
{
    return $this->photo;
}

public function setPhoto(string $photo)
{
    $this->photo = $photo;
}

public function getAvgFuelConsumption(): ?float 
{
    return $this->avg_fuel_consumption;
}

public function setAvgFuelConsumption(float $avg_fuel_consumption)
{
    $this->avg_fuel_consumption = $avg_fuel_consumption;
}

public function getStatus(): string {
    return $this->status;
}

public function setStatus(string $status): void {
    $this->status = $status;
}

public function getCurrentLatitude(): ?float {
    return $this->current_latitude;
}

public function setCurrentLatitude(float $current_latitude): void {
    $this->current_latitude = $current_latitude;
}

public function getCurrentLongitude(): ?float {
    return $this->current_longitude;
}

public function setCurrentLongitude(float $current_longitude): void {
    $this->current_longitude = $current_longitude;
}
}