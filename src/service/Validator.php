<?php

class Validator {

    const MAX_FILE_SIZE = 1024*1024;
    const SUPPORTED_TYPES = ['image/png','image/jpeg','image/pjpeg'];


    public function normalizeString(string $input): string {
        return ucfirst(strtolower(trim($input))); 
    }
    
    public function normalizeRegistration(string $input): ?string {
        $normalized = strtoupper(preg_replace('/\s+/', '', $input)); 
        return preg_match('/^[A-Z]{2,3}[0-9A-Z]{3,5}$/', $normalized) ? $normalized : null;
    }
    public function validateVIN(string $vin): ?string {
        return (strlen($vin) === 17 && ctype_alnum($vin)) ? strtoupper($vin) : null;
    }

    public function validatePhoneNumber(string $phone): ?string {
        
        $digitsOnly = preg_replace('/\D+/', '', $phone);

        if (strlen($digitsOnly) !== 9) {
            return null;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})/', '$1 $2 $3', $digitsOnly);
    }

    public function validateEmail(string $email): ?string {
        $email = trim($email);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return null;
    }


    public function validate(array $file): bool
    {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->messages[] = 'File is too large for destination file system.';
            return false;
        }

        if (!isset($file['type']) || !in_array($file['type'], self::SUPPORTED_TYPES)) {
            $this->messages[] = 'File type is not supported.';
            return false;
        }
        return true;
    }



}
