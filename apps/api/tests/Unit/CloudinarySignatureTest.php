<?php

use App\Services\Cloudinary;

it('signs requests exactly like the example in Cloudinary\'s documentation', function () {
    // From Cloudinary's "Generating authentication signatures" guide: these parameters
    // with the API secret "abcd" must give this signature.
    $cloudinary = new Cloudinary('demo', '1234', 'abcd', 'courtku');

    expect($cloudinary->sign([
        'timestamp' => 1315060510,
        'public_id' => 'sample_image',
        'eager' => 'w_400,h_300,c_pad|w_260,h_200,c_crop',
    ]))->toBe('bfd09f95f331f558cbd1320e67aa8d488770583e');
});

it('builds photo addresses that resize and compress on delivery', function () {
    $cloudinary = new Cloudinary('courtku', '1234', 'abcd', 'courtku');

    expect($cloudinary->url('courtku/venues/1/abc', 'c_fill,w_400,h_300'))
        ->toBe('https://res.cloudinary.com/courtku/image/upload/c_fill,w_400,h_300,f_auto,q_auto/courtku/venues/1/abc');
});
