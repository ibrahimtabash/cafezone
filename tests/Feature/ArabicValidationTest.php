<?php

use Illuminate\Support\Facades\Validator;

it('shows validation messages and field names in Arabic', function () {
    $validator = Validator::make([], [
        'customer_phone' => ['required'],
        'payment_receipt' => ['required'],
    ]);

    expect($validator->errors()->first('customer_phone'))->toBe('حقل رقم الجوال مطلوب.')
        ->and($validator->errors()->first('payment_receipt'))->toBe('حقل إثبات الدفع مطلوب.');
});
