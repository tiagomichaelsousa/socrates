<?php

namespace Reducktion\Socrates\Core\Mexico;

use Reducktion\Socrates\Contracts\IdValidator;
use Reducktion\Socrates\Exceptions\InvalidLengthException;

/**
 * Class MexicoIdValidatorMexicoIdValidator
 *
 * Algorithm adapted from: https://sayari.com/blog/breaking-down-mexican-national-id/.
 *
 * @package Reducktion\Socrates\Core\Mexico
 */
class MexicoIdValidator implements IdValidator
{
    private $vowels = ['A', 'E', 'I', 'O', 'U'];
    private $genders = ['H', 'M'];
    private $statesAbreviations = ['AS', 'BS', 'CL', 'CS', 'DF', 'GT', 'HG', 'MC', 'MS', 'NL', 'PL', 'QR', 'SL', 'TC', 'TL', 'YN', 'NE', 'BC', 'CC', 'CM', 'CH', 'DG', 'GR', 'JC', 'MN', 'NT', 'OC', 'QT', 'SP', 'SR', 'TS', 'VZ', 'ZS'];

    public function validate(string $id): bool
    {
        $id = $this->sanitize($id);

        if (!$this->validateNames(substr($id, 0, 3))) {
            return false;
        }

        if (!$this->validateBirthdate(substr($id, 4, 5))) {
            return false;
        }

        if (!$this->validateGender($id[10])) {
            return false;
        }

        if (!$this->validateState(substr($id, 11,  2))) {
            return false;
        }

        if (!$this->validateConsonants(substr($id, 13,  3))) {
            return false;
        }

        return $this->validateChecksum($id);
    }

    private function sanitize(string $id): string
    {
        $idLength = strlen($id);

        if ($idLength !== 18) {
            throw new InvalidLengthException('Mexico CURP', 18, $idLength);
        }

        return strtoupper($id);
    }

    private function validateNames(string $names): bool
    {
        for ($i = 0; $i < 3; $i++) {
            if (!ctype_alpha($names[$i])) {
                return false;
            }

            if (!in_array($names[1], $this->vowels)) {
                return false;
            }
        }

        return true;
    }

    private function validateBirthdate(string $birthdate): bool
    {
        for ($i = 0; $i < strlen($birthdate); $i++) {
            if (!is_numeric($birthdate[$i])) {
                return false;
            }
        }
        return true;
    }

    private function validateGender(string $gender): bool
    {
        if (!ctype_alpha($gender) || !in_array($gender, $this->genders)) {
            return false;
        }

        return true;
    }

    private function validateState(string $state): bool
    {
        if (
            !ctype_alpha($state) ||
            !in_array($state, $this->statesAbreviations)
        ) {
            return false;
        }

        return true;
    }

    private function validateConsonants(string $names): bool
    {
        if (!ctype_alpha($names)) {
            return false;
        }

        for ($i = 0; $i < strlen($names); $i++) {
            if (in_array($names[$i], $this->vowels)) {
                return false;
            }
        }

        return true;
    }

    private function validateChecksum(string $id): bool
    {
        if (!is_numeric($id[17])) {
            return false;
        }

        $alphabet = "0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ";
        $code = substr($id, 0, 17);
        $check = 0;

        for ($i = 0; $i < strlen($code); $i++) {
            $check += array_search($code[$i], str_split($alphabet)) * (18 - $i);
        }
        return (10 - $check % 10) % 10 === intval($id[17]);
    }
}
