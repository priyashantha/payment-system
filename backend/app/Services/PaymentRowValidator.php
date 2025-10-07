<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentRowValidator
{
    public static function validate(array $row): array
    {
        $row = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        $validator = Validator::make($row, [
            'customer_id'    => 'required|string',
            'customer_name'  => 'required|string',
            'customer_email' => 'required|email',
            'amount'         => 'required|numeric',
            'currency'       => 'required|string|size:3',
            'reference_no'   => 'required|string',
            'date_time'      => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();

        if (empty($data['date_time']) || $data['date_time'] === '0000-00-00 00:00:00') {
            Log::warning("Payment row missing date_time, using now()", ['reference' => $data['reference_no'] ?? null]);
            $data['date_time'] = now();
        }
        return $data;
    }
}
