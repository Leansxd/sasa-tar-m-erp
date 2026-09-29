<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class CaptchaService
{
    public static function generate(): array
    {
        $a = random_int(12, 49);
        $b = random_int(3, 19);
        $isAddition = random_int(0, 1) === 1;

        if ($isAddition) {
            $question = "{$a} + {$b}";
            $answer = (string) ($a + $b);
        } else {
            $question = "{$a} - {$b}";
            $answer = (string) ($a - $b);
        }

        $tokenPayload = [
            'answer' => $answer,
            'expires_at' => time() + 300,
        ];

        $token = Crypt::encryptString(json_encode($tokenPayload));

        $svg = self::renderSvg($question);

        return [
            'token' => $token,
            'svg' => 'data:image/svg+xml;base64,' . base64_encode($svg),
        ];
    }

    public static function verify(?string $userAnswer, ?string $token): bool
    {
        if (empty($userAnswer) || empty($token)) {
            return false;
        }

        try {
            $decrypted = json_decode(Crypt::decryptString($token), true);
            if (!is_array($decrypted) || !isset($decrypted['answer'], $decrypted['expires_at'])) {
                return false;
            }

            if (time() > (int) $decrypted['expires_at']) {
                return false;
            }

            return hash_equals((string) $decrypted['answer'], trim($userAnswer));
        } catch (\Throwable) {
            return false;
        }
    }

    private static function renderSvg(string $text): string
    {
        $w = 120;
        $h = 42;
        $textDisplay = $text . ' = ?';

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'">
            <rect width="100%" height="100%" fill="#090d16" rx="8" stroke="#334155" stroke-width="1"/>
            <line x1="10" y1="20" x2="110" y2="24" stroke="#1e293b" stroke-width="2"/>
            <line x1="20" y1="32" x2="100" y2="12" stroke="#334155" stroke-width="1" stroke-dasharray="3,3"/>
            <text x="50%" y="58%" dominant-baseline="middle" text-anchor="middle" fill="#38bdf8" font-family="monospace" font-size="16" font-weight="900" letter-spacing="2">'.$textDisplay.'</text>
        </svg>';
    }
}
