<?php
class JWT {
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    public static function gerar($payload, $secret) {
        $header = self::base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body   = self::base64url_encode(json_encode($payload));
        $sig    = self::base64url_encode(hash_hmac('sha256', "$header.$body", $secret, true));
        return "$header.$body.$sig";
    }

    public static function verificar($token, $secret) {
        $partes = explode('.', $token ?? '');
        if (count($partes) !== 3) return false;

        [$header, $body, $sig] = $partes;
        $sigEsperada = self::base64url_encode(hash_hmac('sha256', "$header.$body", $secret, true));

        if (!hash_equals($sigEsperada, $sig)) return false;

        $dados = json_decode(self::base64url_decode($body), true);

        if (isset($dados['exp']) && $dados['exp'] < time()) return false;

        return $dados;
    }
}
