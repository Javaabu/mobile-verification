---
title: Requesting Access Token via API
sidebar_position: 1.8
---

# Requesting Access Token via API

Request an access token using the API.

```php
$response = $http->post('http://your-app.com/oauth/token', [
    'grant_type'        => 'mobile',
    'client_id'         => $client_id,
    'client_secret'     => $client_secret,
    'number'            => $mobile_number,
    'country_code'      => $country_code,
    'verification_code' => $verification_code,
]);
```
