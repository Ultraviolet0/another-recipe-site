<?php

/**
 * Verify a Cloudflare Turnstile token.
 *
 * @param string $token - Turnstile response token to verify
 * @param string|null $remote_ip - user's remote IP address
 * 
 * @return bool true if the token is valid
 */
function verify_turnstile_token(string $token, ?string $remote_ip = null): bool
{
  $secret = $_ENV['TURNSTILE_SECRET_KEY'] ?? '';

  if ($secret === '' || $token === '') {
    return false;
  }

  $post_fields = [
    'secret'   => $secret,
    'response' => $token,
  ];

  if (!is_blank($remote_ip)) {
    $post_fields['remoteip'] = $remote_ip;
  }

  $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);

  $raw = curl_exec($ch);

  if ($raw === false) {
    $ch = null;
    return false;
  }

  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $ch = null;

  if ($http_code !== 200) {
    return false;
  }

  $result = json_decode($raw, true);

  return is_array($result) && !empty($result['success']);
}
