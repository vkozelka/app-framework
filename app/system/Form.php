<?php
namespace App\System;

use App\System\Form\Exception\FieldsNotProvidedException;
use App\System\Form\Exception\ValidatorsNotProvidedException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Form {

    private array $errors = [];

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    private array $data = [];

    public function __construct()
    {
        $this->validator = Validation::createValidator();
    }

    /**
     * @return ValidatorInterface
     */
    private function getValidator(): ValidatorInterface {
        return $this->validator;
    }

    /**
     * @throws FieldsNotProvidedException
     */
    public function getFields(): array {
        throw new FieldsNotProvidedException();
    }

    /**
     * @throws ValidatorsNotProvidedException
     */
    public function getValidators(): array {
        throw new ValidatorsNotProvidedException();
    }

    /**
     * @return bool
     * @throws FieldsNotProvidedException
     * @throws ValidatorsNotProvidedException
     */
    public function isValid(): bool {
        $valid = true;

        foreach ($this->getFields() as $field) {
            $value = App::get()->request->request->get($field);
            if (isset($this->getValidators()[$field])) {
                $this->errors[$field] = $this->getValidator()->validate($value,$this->getValidators()[$field]);
                if (count($this->errors[$field])) {
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    public function addError($field, ConstraintViolation $error): void {
        $this->errors[$field]->add($error);
    }

    public function getErrors($field = null): array {
        if (!$field) {
            return $this->errors;
        }
        return $this->errors[$field];
    }

    public function hasErrors($field = null): bool {
        if ($field) {
            if (isset($this->errors[$field]) && count($this->errors[$field])) {
                return true;
            } else {
                return false;
            }
        } else {
            $count = 0;
            foreach ($this->errors as $field => $errors) {
                $count+= count($errors);
            }
            return $count > 0;
        }
    }

    /**
     * @return array|ParameterBag
     * @throws FieldsNotProvidedException
     */
    public function getValues(): array {
        $result = new ParameterBag();
        foreach ($this->getFields() as $field) {
            $result->set($field, App::get()->request->request->get($field));
        }
        return $result;
    }

    /**
     * @param array $data
     * @throws FieldsNotProvidedException
     */
    public function setData(array $data = []): void {
        foreach ($this->getFields() as $field) {
            if (isset($data[$field])) {
                $this->data[$field] = $data[$field];
            }
        }
    }

    /**
     * @param string $field
     * @return bool
     */
    public function hasData(string $field): bool {
        return isset($this->data[$field]);
    }

    /**
     * @param string $field
     * @return string|null
     */
    public function getData(string $field): ?string {
        if ($this->hasData($field)) {
            return $this->data[$field];
        }
        return null;
    }

}