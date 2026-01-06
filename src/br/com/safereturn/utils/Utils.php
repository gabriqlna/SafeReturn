<?php

namespace br\com\safereturn\utils;

use pocketmine\utils\TextFormat;

class Utils {

    /**
     * Converte códigos de cor (ex: &a -> §a)
     * Útil se quiseres usar '&' na config.yml
     */
    public static function colorize(string $message): string {
        return TextFormat::colorize($message);
    }

    /**
     * Formata segundos para um formato legível.
     * Exemplo: 125 segundos -> "02m 05s"
     */
    public static function formatTime(int $seconds): string {
        if ($seconds < 0) $seconds = 0;
        
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        
        // Retorna formatado com zero à esquerda (ex: 05m 09s)
        return sprintf("%02dm %02ds", $minutes, $seconds);
    }

    /**
     * Função auxiliar para substituir variáveis na mensagem
     * Exemplo de uso: Utils::replace("Olá {PLAYER}", ["PLAYER" => "Steve"])
     */
    public static function replace(string $message, array $replacements = []): string {
        foreach ($replacements as $key => $value) {
            $message = str_replace("{" . $key . "}", (string)$value, $message);
        }
        return self::colorize($message);
    }
}
