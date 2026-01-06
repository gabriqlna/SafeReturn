<?php

namespace br\com\safereturn\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use br\com\safereturn\Main;

class BackDeathCommand extends Command {

    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("backdeath", "Volta ao local da morte", "/backdeath", ["voltar"]);
        $this->setPermission("safereturn.back");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$sender instanceof Player) return false;

        if(!$this->testPermission($sender)) return false;

        if(!$this->plugin->getConfig()->get("back_death.enable")){
            $sender->sendMessage("§cEste comando está desativado.");
            return true;
        }

        $lastDeath = $this->plugin->playerData->getNested($sender->getName() . ".last_death");
        if(!$lastDeath){
            $sender->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $this->plugin->getConfig()->get("messages.back_no_death"));
            return true;
        }

        // Sistema de Cooldown
        $cooldownTime = $this->plugin->getConfig()->get("back_death.cooldown", 300);
        $lastUse = $this->plugin->playerData->getNested($sender->getName() . ".last_back_use", 0);
        
        if(time() - $lastUse < $cooldownTime && !$sender->hasPermission("safereturn.admin")){
            $remaining = $cooldownTime - (time() - $lastUse);
            $msg = str_replace("{TIME}", $remaining, $this->plugin->getConfig()->get("messages.back_cooldown"));
            $sender->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $msg);
            return true;
        }

        // Custo de XP (Lógica simples)
        $costXp = $this->plugin->getConfig()->get("back_death.cost_xp", 0);
        if($costXp > 0 && $sender->getXpManager()->getXpLevel() < $costXp){
            $sender->sendMessage("§cVocê precisa de §e{$costXp} níveis §cde XP para voltar.");
            return true;
        }

        // Teleporte
        $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($lastDeath['world']);
        if($world){
            $pos = new Position($lastDeath['x'], $lastDeath['y'], $lastDeath['z'], $world);
            $sender->teleport($pos);
            
            // Aplica custos e cooldown
            if($costXp > 0) $sender->getXpManager()->subtractXpLevels($costXp);
            $this->plugin->playerData->setNested($sender->getName() . ".last_back_use", time());
            
            $sender->sendMessage($this->plugin->getConfig()->get("messages.prefix") . $this->plugin->getConfig()->get("messages.back_success"));
        } else {
            $sender->sendMessage("§cO mundo onde você morreu não está carregado.");
        }

        return true;
    }
}
