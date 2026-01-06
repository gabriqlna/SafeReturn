<?php

namespace br\com\safereturn;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use br\com\safereturn\commands\BackDeathCommand;

class Main extends PluginBase {

    private static $instance;
    private $graveManager;
    public $playerData;

    public function onEnable(): void {
        self::$instance = $this;
        $this->saveDefaultConfig();

        // Garante que a subpasta data/ exista antes de criar o Config
        $dataFolder = $this->getDataFolder();
        $playersDir = $dataFolder . "data/";
        if (!is_dir($playersDir)) {
            // Usa @ para suprimir avisos em caso de condições de corrida/permissão
            @mkdir($playersDir, 0777, true);
        }

        // Dados de jogadores (last death, cooldowns)
        $this->playerData = new Config($playersDir . "players.yml", Config::YAML);

        // Inicializa o Gerenciador de Túmulos
        $this->graveManager = new GraveManager($this);

        // Registra Eventos e Comandos
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("backdeath", new BackDeathCommand($this));

        $this->getLogger()->info("SafeReturn ativado com sucesso!");
    }

    public function onDisable(): void {
        $this->graveManager->saveGraves();
        $this->playerData->save();
    }

    public static function getInstance(): Main {
        return self::$instance;
    }

    public function getGraveManager(): GraveManager {
        return $this->graveManager;
    }
}
