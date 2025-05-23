<?php

namespace MyVal;

class Validator
{
    private array $validators;
    private array $errors = [];
    private array $fields;
    private array $rules;
    private array $messages;

    public function __construct(
        array $fields,
        array $rules,
        array $messages = [],
        array $validators = []
    ) {
        $this->validators = $validators;
        $this->fields = $fields;
        $this->rules = $rules;
        $this->messages = $messages;
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $fieldName => $fieldValidators) {
            $this->validateField($fieldName, $fieldValidators);
        }
    }

    private function validateField(string $fieldName, array $fieldValidators): void
    {
        foreach ($fieldValidators as $validatorName) {
            $tmp = explode(':', $validatorName);
            [$validatorName, $args] = count($tmp) > 1 ? $tmp : [$validatorName, null];
            $args = $args ? explode(',', $args) : [];

            if (!isset($this->validators[$validatorName])) {
                continue;
            }

            $validatorClass = $this->validators[$validatorName];

            if (!class_exists($validatorClass)) {
                continue;
            }

            $validator = new $validatorClass(
                $fieldName,
                $this->fields[$fieldName],
                $args,
                $this->messages[$validatorName] ?? null
            );

            if (!$validator->rule()) {
                $this->errors[$fieldName][] = $validator->validate();
            }
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }
}